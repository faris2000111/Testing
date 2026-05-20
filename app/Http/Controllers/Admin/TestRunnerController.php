<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TestProject;
use App\Models\TestResult;
use App\Models\TestRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class TestRunnerController extends Controller
{
    /**
     * Shared cookie jar for maintaining session across requests.
     */
    private \GuzzleHttp\Cookie\CookieJar $cookieJar;

    /**
     * Cached CSRF token for the current test run.
     */
    private ?string $csrfToken = null;

    /**
     * Run all active test cases for a project.
     */
    public function run(TestProject $project): JsonResponse
    {
        $cases = $project->activeCases()->get();

        if ($cases->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada test case aktif untuk dijalankan.',
            ], 422);
        }

        $startTime = microtime(true);

        // Initialize cookie jar for session persistence
        $this->cookieJar = new \GuzzleHttp\Cookie\CookieJar();
        $this->csrfToken = null;

        // Fetch initial CSRF token from the target site
        $this->fetchCsrfToken($project);

        // Create a test run record
        $testRun = TestRun::create([
            'test_project_id' => $project->id,
            'user_id' => auth()->id(),
            'status' => 'running',
            'total_cases' => $cases->count(),
        ]);

        $passed = 0;
        $failed = 0;
        $results = [];

        foreach ($cases as $case) {
            $result = $this->executeTestCase($project, $case, $testRun);
            $results[] = $result;

            if ($result['status'] === 'passed') {
                $passed++;
            } else {
                $failed++;
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;

        // Update the test run
        $testRun->update([
            'status' => 'completed',
            'passed' => $passed,
            'failed' => $failed,
            'duration_ms' => round($duration, 2),
        ]);

        ActivityLog::record('created', $testRun, "Menjalankan blackbox test: {$project->name} ({$passed}/{$cases->count()} passed)");

        return response()->json([
            'success' => true,
            'run_id' => $testRun->id,
            'total' => $cases->count(),
            'passed' => $passed,
            'failed' => $failed,
            'duration_ms' => round($duration, 2),
            'results' => $results,
        ]);
    }

    /**
     * Show test run detail.
     */
    public function show(TestProject $project, TestRun $run): View
    {
        $run->load(['results.testCase', 'user']);

        return view('admin.blackbox.runs.show', compact('project', 'run'));
    }

    /**
     * Fetch CSRF token from the target website.
     * Tries common pages: /login, /, or any page that returns a form with _token.
     */
    private function fetchCsrfToken(TestProject $project): void
    {
        $pagesToTry = ['/login', '/', '/register'];

        foreach ($pagesToTry as $page) {
            try {
                $url = $project->getFullUrl($page);
                $response = Http::timeout(15)
                    ->withoutVerifying()
                    ->withOptions(['cookies' => $this->cookieJar])
                    ->get($url);

                if ($response->successful()) {
                    $token = $this->extractCsrfToken($response->body());
                    if ($token) {
                        $this->csrfToken = $token;
                        return;
                    }
                }
            } catch (\Exception) {
                continue;
            }
        }
    }

    /**
     * Extract CSRF token from HTML response.
     * Looks for meta tag or hidden input field.
     */
    private function extractCsrfToken(string $html): ?string
    {
        // Try meta tag: <meta name="csrf-token" content="...">
        if (preg_match('/<meta\s+name=["\']csrf-token["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $matches[1];
        }

        // Try hidden input: <input type="hidden" name="_token" value="...">
        if (preg_match('/<input[^>]+name=["\']_token["\'][^>]+value=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $matches[1];
        }

        // Try reversed attribute order: <input type="hidden" value="..." name="_token">
        if (preg_match('/<input[^>]+value=["\']([^"\']+)["\'][^>]+name=["\']_token["\']/i', $html, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Refresh CSRF token from a specific page before a POST request.
     */
    private function refreshCsrfTokenFromPage(TestProject $project, string $endpoint): void
    {
        try {
            $url = $project->getFullUrl($endpoint);
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->withOptions(['cookies' => $this->cookieJar])
                ->get($url);

            if ($response->successful()) {
                $token = $this->extractCsrfToken($response->body());
                if ($token) {
                    $this->csrfToken = $token;
                }
            }
        } catch (\Exception) {
            // Keep existing token
        }
    }

    /**
     * Execute a single test case and store the result.
     */
    private function executeTestCase(TestProject $project, $case, TestRun $testRun): array
    {
        $url = $project->getFullUrl($case->endpoint);
        $startTime = microtime(true);

        try {
            // For non-GET requests, refresh CSRF token from the target page
            if (in_array($case->method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                $this->refreshCsrfTokenFromPage($project, $case->endpoint);
            }

            $request = Http::timeout(30)
                ->withoutVerifying()
                ->withOptions(['cookies' => $this->cookieJar]);

            // Add custom headers
            if (! empty($case->headers)) {
                $request = $request->withHeaders($case->headers);
            }

            // Prepare body params with CSRF token for non-GET requests
            $bodyParams = $case->body_params ?? [];
            if (in_array($case->method, ['POST', 'PUT', 'PATCH', 'DELETE']) && $this->csrfToken) {
                // Only inject _token if not already provided in body_params
                if (! isset($bodyParams['_token'])) {
                    $bodyParams['_token'] = $this->csrfToken;
                }
            }

            // Execute the request based on method
            $response = match ($case->method) {
                'GET' => $request->get($url),
                'POST' => $request->asForm()->post($url, $bodyParams),
                'PUT' => $request->asForm()->put($url, $bodyParams),
                'PATCH' => $request->asForm()->patch($url, $bodyParams),
                'DELETE' => $request->asForm()->delete($url, $bodyParams),
                default => $request->get($url),
            };

            $responseTime = (microtime(true) - $startTime) * 1000;
            $actualStatus = $response->status();
            $responseBody = $response->body();

            // After a successful POST, try to extract new CSRF token from response
            // (in case the page returns a new form)
            if (in_array($case->method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                $newToken = $this->extractCsrfToken($responseBody);
                if ($newToken) {
                    $this->csrfToken = $newToken;
                }
            }

            // Determine pass/fail
            $status = 'passed';
            $errorMessage = null;

            // Check status code (also accept redirects as success if expected)
            if ($actualStatus !== $case->expected_status) {
                $status = 'failed';
                $errorMessage = "Expected status {$case->expected_status}, got {$actualStatus}.";
            }

            // Check expected content
            if ($status === 'passed' && ! empty($case->expected_contains)) {
                if (stripos($responseBody, $case->expected_contains) === false) {
                    $status = 'failed';
                    $errorMessage = "Response tidak mengandung teks: \"{$case->expected_contains}\".";
                }
            }

            // Check content that should NOT be present
            if ($status === 'passed' && ! empty($case->expected_not_contains)) {
                if (stripos($responseBody, $case->expected_not_contains) !== false) {
                    $status = 'failed';
                    $errorMessage = "Response mengandung teks yang tidak diharapkan: \"{$case->expected_not_contains}\".";
                }
            }

            // Store result (truncate body to avoid huge DB entries)
            $storedBody = mb_substr($responseBody, 0, 5000);

            TestResult::create([
                'test_run_id' => $testRun->id,
                'test_case_id' => $case->id,
                'status' => $status,
                'actual_status' => $actualStatus,
                'response_body' => $storedBody,
                'error_message' => $errorMessage,
                'response_time_ms' => round($responseTime, 2),
            ]);

            return [
                'case_id' => $case->id,
                'title' => $case->title,
                'status' => $status,
                'actual_status' => $actualStatus,
                'expected_status' => $case->expected_status,
                'response_time_ms' => round($responseTime, 2),
                'error_message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            $responseTime = (microtime(true) - $startTime) * 1000;

            TestResult::create([
                'test_run_id' => $testRun->id,
                'test_case_id' => $case->id,
                'status' => 'error',
                'actual_status' => null,
                'response_body' => null,
                'error_message' => $e->getMessage(),
                'response_time_ms' => round($responseTime, 2),
            ]);

            return [
                'case_id' => $case->id,
                'title' => $case->title,
                'status' => 'error',
                'actual_status' => null,
                'expected_status' => $case->expected_status,
                'response_time_ms' => round($responseTime, 2),
                'error_message' => $e->getMessage(),
            ];
        }
    }
}
