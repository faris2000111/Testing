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
     * Quick Auto-Test: Create project (if needed), generate test cases via AI, and run immediately.
     */
    public function quickTest(Request $request): JsonResponse
    {
        @set_time_limit(300);
        try {
            $validated = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'prompt' => ['nullable', 'string', 'max:2000'],
            'accounts' => ['nullable', 'array'],
            'accounts.*.role' => ['nullable', 'string', 'max:100'],
            'accounts.*.username' => ['nullable', 'string', 'max:255'],
            'accounts.*.password' => ['nullable', 'string', 'max:255'],
        ]);

        $url = rtrim($validated['url'], '/');
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? 'Target Website';
        $projectName = 'Quick Test - ' . $host;

        // Handle multi-account array preparation
        $accounts = $validated['accounts'] ?? [];
        if (empty($accounts) && ! empty($validated['username']) && ! empty($validated['password'])) {
            $accounts[] = [
                'role' => 'Pengguna',
                'username' => $validated['username'],
                'password' => $validated['password'],
            ];
        }

        // Auto-crawl / scan target page & logged-in dashboard HTML to discover ALL real endpoints & submenus
        $discoveredEndpoints = [];
        $loginInputNames = [];

        try {
            // 1. Fetch public home page HTML (fast 3s timeout)
            $crawlResponse = Http::timeout(3)->withoutVerifying()->get($url);
            if ($crawlResponse->successful()) {
                $html = $crawlResponse->body();
                if (preg_match_all('/(?:href|action)=["\'](\/[^"\'#\?]*)/i', $html, $matches)) {
                    $discoveredEndpoints = array_merge($discoveredEndpoints, $matches[1]);
                }
            }

            // 2. Fetch /login page HTML to inspect login form input names & CSRF (fast 3s timeout)
            $loginUrl = rtrim($url, '/') . '/login';
            $loginHtml = '';
            $loginResp = Http::timeout(3)->withoutVerifying()->get($loginUrl);
            if ($loginResp->successful()) {
                $loginHtml = $loginResp->body();
                if (preg_match_all('/<input[^>]+name=["\']([^"\']+)["\']/i', $loginHtml, $inputMatches)) {
                    $loginInputNames = array_values(array_filter(array_unique($inputMatches[1]), function ($n) {
                        return ! in_array(strtolower($n), ['_token', '_method', 'remember', 'remember_me', 'submit']);
                    }));
                }
            }

            // 3. LOG IN IN BACKGROUND TO SCAN INTERNAL LOGGED-IN DASHBOARD SUBMENUS
            if (! empty($accounts)) {
                foreach ($accounts as $acc) {
                    if (! empty($acc['username']) && ! empty($acc['password'])) {
                        $tempJar = new \GuzzleHttp\Cookie\CookieJar();

                        // Extract CSRF token
                        $csrfToken = null;
                        if (! empty($loginHtml)) {
                            if (preg_match('/<meta\s+name=["\']csrf-token["\']\s+content=["\']([^"\']+)["\']/i', $loginHtml, $m)) {
                                $csrfToken = $m[1];
                            } elseif (preg_match('/<input[^>]+name=["\']_token["\'][^>]+value=["\']([^"\']+)["\']/i', $loginHtml, $m)) {
                                $csrfToken = $m[1];
                            }
                        }

                        // Determine form field names
                        $userField = 'email';
                        if (! empty($loginInputNames)) {
                            foreach ($loginInputNames as $f) {
                                if (in_array(strtolower($f), ['username', 'email', 'user', 'identity', 'login'])) {
                                    $userField = $f;
                                    break;
                                }
                            }
                        }
                        $passField = 'password';
                        foreach ($loginInputNames as $f) {
                            if (in_array(strtolower($f), ['password', 'pass', 'pwd'])) {
                                $passField = $f;
                                break;
                            }
                        }

                        $loginBody = [
                            $userField => $acc['username'],
                            $passField => $acc['password'],
                        ];
                        if ($csrfToken) {
                            $loginBody['_token'] = $csrfToken;
                        }

                        // Perform login POST request (fast 3s timeout)
                        Http::timeout(3)->withoutVerifying()->withOptions([
                            'cookies' => $tempJar,
                            'allow_redirects' => true,
                        ])->asForm()->post($loginUrl, $loginBody);

                        // Targeted scan based on role (fast 3s timeout)
                        $dashPages = str_contains(strtolower($acc['role'] ?? ''), 'admin')
                            ? ['/admin/dashboard', '/admin']
                            : ['/workspace', '/dashboard'];

                        foreach ($dashPages as $dashPath) {
                            try {
                                $dashResp = Http::timeout(3)->withoutVerifying()->withOptions([
                                    'cookies' => $tempJar,
                                    'allow_redirects' => true,
                                ])->get(rtrim($url, '/') . $dashPath);

                                if ($dashResp->successful()) {
                                    $dashHtml = $dashResp->body();
                                    if (preg_match_all('/(?:href|action)=["\'](\/[^"\'#\?]*)/i', $dashHtml, $dMatches)) {
                                        $discoveredEndpoints = array_merge($discoveredEndpoints, $dMatches[1]);
                                    }
                                }
                            } catch (\Exception $e) {
                                // Ignore individual page crawl errors
                            }
                        }
                    }
                }
            }

            // Clean & filter discovered endpoints
            $discoveredEndpoints = array_values(array_unique($discoveredEndpoints));
            $discoveredEndpoints = array_values(array_filter($discoveredEndpoints, function ($ep) {
                return ! preg_match('/\.(css|js|png|jpg|jpeg|svg|ico|gif|woff|woff2|ttf|eot|pdf|zip)$/i', $ep)
                    && ! str_starts_with($ep, '//')
                    && $ep !== '/logout';
            }));
            $discoveredEndpoints = array_values(array_slice($discoveredEndpoints, 0, 30));

        } catch (\Exception $e) {
            // Silence crawl errors, AI will fall back to standard URL generation
        }

        // Build prompt context
        $promptParts = [];
        if (! empty($validated['prompt'])) {
            $promptParts[] = $validated['prompt'];
        } else {
            $promptParts[] = 'Generasikan blackbox test cases otomatis untuk menguji website ini.';
        }

        if (! empty($discoveredEndpoints)) {
            $promptParts[] = 'Berikut adalah URL/endpoint internal ASLI yang BERHASIL TERDETEKSI LANGSUNG dari HTML sidebar & dashboard website setelah login: ' . implode(', ', $discoveredEndpoints) . '. Uji SELURUH endpoint asli ini secara mendalam!';
        }

        if (! empty($loginInputNames)) {
            $promptParts[] = 'Form login terdeteksi menggunakan nama input field: ' . implode(', ', $loginInputNames) . '. Gunakan nama field ini secara tepat pada body_params POST /login.';
        }

        if (! empty($accounts)) {
            $accountText = [];
            foreach ($accounts as $idx => $acc) {
                if (! empty($acc['username']) && ! empty($acc['password'])) {
                    $roleName = ! empty($acc['role']) ? $acc['role'] : ('Role ' . ($idx + 1));
                    $accountText[] = sprintf(
                        'Akun #%d [Role: %s] -> Username/Email: "%s", Password: "%s"',
                        $idx + 1,
                        $roleName,
                        $acc['username'],
                        $acc['password']
                    );
                }
            }

            if (! empty($accountText)) {
                $promptParts[] = "Website ini memiliki beberapa akun/role login yang akan diuji.\n" .
                    "Daftar Kredensial Akun per Role:\n" . implode("\n", $accountText) . "\n\n" .
                    "Instruksi Pengujian Multirole Maksimal & Komprehensif:\n" .
                    "- Buatkan SEBANYAK-BANYAKNYA test cases yang sangat lengkap dan detail.\n" .
                    "- Untuk Role Admin / Superadmin: Wajib uji SELURUH sub-menu admin yang ada (seperti /admin/dashboard, /admin/users, /admin/users/create, /admin/roles, /admin/roles/create, /admin/menus, /admin/sections, /admin/settings, /admin/profile, /admin/password, /admin/reports, dll).\n" .
                    "- Untuk Role Pengguna: Wajib uji SELURUH halaman dan fitur pengguna (seperti /workspace, /dashboard, /profile, /settings, dll).\n" .
                    "- Untuk setiap role, awali dengan POST /login. Apabila berganti akun dari Admin ke Pengguna, sertakan POST /logout terlebih dahulu.";
            }
        }

        $promptParts[] = 'Penting: Kembalikan respon HANYA berupa JSON valid sesuai format test_cases tanpa teks penjelasan lain.';
        $fullPrompt = implode("\n\n", $promptParts);

        // Find or create project
        $project = TestProject::firstOrCreate(
            ['base_url' => $url],
            [
                'name' => $projectName,
                'description' => 'Quick Auto-Test untuk ' . $url,
                'is_active' => true,
            ]
        );

        $aiGenerator = app(AiTestGeneratorController::class);

        $aiRequest = new Request([
            'prompt' => $fullPrompt,
            'project_name' => $project->name,
            'base_url' => $project->base_url,
        ]);

        $aiResponse = $aiGenerator->generateBlackbox($aiRequest);
        $aiData = json_decode($aiResponse->getContent(), true);

        if (! ($aiData['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $aiData['message'] ?? 'Gagal membuat test cases dengan AI.',
            ], 422);
        }

        $casesData = $aiData['data']['test_cases'] ?? [];
        if (empty($casesData)) {
            return response()->json([
                'success' => false,
                'message' => 'AI tidak mengembalikan test case yang valid.',
            ], 422);
        }

        // Store generated test cases directly without suite
        $currentMaxOrder = $project->testCases()->max('order') ?? 0;
        foreach ($casesData as $index => $c) {
            \App\Models\TestCase::create([
                'test_project_id' => $project->id,
                'test_suite_id' => null,
                'title' => $c['title'] ?? ('Test Case ' . ($index + 1)),
                'description' => $c['description'] ?? null,
                'method' => strtoupper($c['method'] ?? 'GET'),
                'endpoint' => $c['endpoint'] ?? '/',
                'headers' => $c['headers'] ?? null,
                'body_params' => $c['body_params'] ?? null,
                'expected_status' => (int) ($c['expected_status'] ?? 200),
                'expected_contains' => $c['expected_contains'] ?? null,
                'expected_not_contains' => $c['expected_not_contains'] ?? null,
                'order' => $currentMaxOrder + $index + 1,
                'is_active' => true,
            ]);
        }

        // Execute tests immediately
        $runResponse = $this->run(new Request(), $project);
        $runData = json_decode($runResponse->getContent(), true);

        if ($runData['success'] ?? false) {
            $runData['redirect_url'] = route('admin.blackbox.projects.runs.show', [$project, $runData['run_id']]);
            $runData['project_url'] = route('admin.blackbox.projects.show', $project);
        }

            return response()->json($runData);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run all active test cases for a project (optionally filtered by suite).
     */
    public function run(Request $request, TestProject $project): JsonResponse
    {
        @set_time_limit(300);
        $suiteId = $request->input('suite_id');

        if ($suiteId) {
            $suite = $project->testSuites()->findOrFail($suiteId);
            $cases = $suite->activeCases()->get();
        } else {
            $cases = $project->activeCases()->get();
        }

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
            'test_suite_id' => $suiteId ?? null,
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

        $label = isset($suite) ? $suite->name : $project->name;
        ActivityLog::record('created', $testRun, "Menjalankan blackbox test: {$label} ({$passed}/{$cases->count()} passed)");

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
     * Delete a test run.
     */
    public function destroy(TestProject $project, TestRun $run): \Illuminate\Http\RedirectResponse
    {
        $runId = $run->id;
        $run->delete();

        ActivityLog::record('deleted', null, "Menghapus test run #{$runId}");

        return redirect()->route('admin.blackbox.projects.show', $project)
            ->with('success', "Test run #{$runId} berhasil dihapus.");
    }

    /**
     * Fetch CSRF token from the target website.
     */
    private function fetchCsrfToken(TestProject $project): void
    {
        $pagesToTry = ['/login', '/', '/register'];

        foreach ($pagesToTry as $page) {
            try {
                $url = $project->getFullUrl($page);
                $response = Http::timeout(15)
                    ->withoutVerifying()
                    ->withOptions([
                        'cookies' => $this->cookieJar,
                        'allow_redirects' => true,
                    ])
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

        // Try reversed attribute order
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
                ->withOptions([
                    'cookies' => $this->cookieJar,
                    'allow_redirects' => true,
                ])
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

            $request = Http::timeout(60)
                ->withoutVerifying()
                ->withOptions([
                    'cookies' => $this->cookieJar,
                    // IMPORTANT: Don't follow redirects so we can capture the actual status code
                    'allow_redirects' => false,
                ]);

            // Add custom headers
            if (! empty($case->headers)) {
                $request = $request->withHeaders($case->headers);
            }

            // Prepare body params with CSRF token for non-GET requests
            $bodyParams = $case->body_params ?? [];
            if (in_array($case->method, ['POST', 'PUT', 'PATCH', 'DELETE']) && $this->csrfToken) {
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
            $redirectUrl = $response->header('Location');

            // If it's a redirect and we need to check the final page content,
            // follow the redirect manually to get the response body
            $finalBody = $responseBody;
            if (in_array($actualStatus, [301, 302, 303, 307, 308]) && $redirectUrl) {
                $finalBody = $this->followRedirectForBody($redirectUrl, $project);
            }

            // Determine pass/fail
            $status = 'passed';
            $errorMessage = null;

            // Check status code
            if ($actualStatus !== $case->expected_status) {
                $status = 'failed';
                $errorMessage = "Expected status {$case->expected_status}, got {$actualStatus}.";

                // Add redirect info for clarity
                if ($redirectUrl) {
                    $errorMessage .= " (Redirect to: {$redirectUrl})";
                }
            }

            // Check expected content (use final body after redirect if available)
            $bodyToCheck = ! empty($finalBody) ? $finalBody : $responseBody;

            if ($status === 'passed' && ! empty($case->expected_contains)) {
                if (stripos($bodyToCheck, $case->expected_contains) === false) {
                    $status = 'failed';
                    $errorMessage = "Response tidak mengandung teks: \"{$case->expected_contains}\".";
                }
            }

            // Check content that should NOT be present
            if ($status === 'passed' && ! empty($case->expected_not_contains)) {
                if (stripos($bodyToCheck, $case->expected_not_contains) !== false) {
                    $status = 'failed';
                    $errorMessage = "Response mengandung teks yang tidak diharapkan: \"{$case->expected_not_contains}\".";
                }
            }

            // Store result (truncate body to avoid huge DB entries)
            $storedBody = mb_substr($bodyToCheck, 0, 5000);

            TestResult::create([
                'test_run_id' => $testRun->id,
                'test_case_id' => $case->id,
                'snapshot_title' => $case->title,
                'snapshot_method' => $case->method,
                'snapshot_endpoint' => $case->endpoint,
                'snapshot_expected_status' => $case->expected_status,
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
                'snapshot_title' => $case->title,
                'snapshot_method' => $case->method,
                'snapshot_endpoint' => $case->endpoint,
                'snapshot_expected_status' => $case->expected_status,
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

    /**
     * Follow a redirect URL to get the final page body (for content checking).
     */
    private function followRedirectForBody(string $redirectUrl, TestProject $project): string
    {
        try {
            // If redirect URL is relative, make it absolute
            if (str_starts_with($redirectUrl, '/')) {
                $redirectUrl = rtrim($project->base_url, '/') . $redirectUrl;
            }

            $response = Http::timeout(15)
                ->withoutVerifying()
                ->withOptions([
                    'cookies' => $this->cookieJar,
                    'allow_redirects' => true,
                ])
                ->get($redirectUrl);

            $body = $response->body();

            // Update CSRF token from the redirected page
            $token = $this->extractCsrfToken($body);
            if ($token) {
                $this->csrfToken = $token;
            }

            return $body;
        } catch (\Exception) {
            return '';
        }
    }
}
