<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiTestGeneratorController extends Controller
{
    /**
     * Generate test scenario steps using AI.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'base_url' => ['nullable', 'string', 'max:500'],
            'module' => ['nullable', 'string', 'max:100'],
        ]);

        $aiConfig = $this->getAiConfig();
        if (! $aiConfig['success']) {
            return response()->json($aiConfig, 422);
        }

        $systemPrompt = $this->buildSystemPrompt($validated);
        $userPrompt = $validated['prompt'];

        try {
            $response = $this->callAi($aiConfig, $systemPrompt, $userPrompt);

            if (! $response['success']) {
                return response()->json($response, 422);
            }

            $parsed = $this->parseManualScenarioResponse($response['content']);

            if (empty($parsed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI tidak dapat menghasilkan scenario yang valid. Coba ubah prompt Anda.',
                    'raw' => $response['content'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => $parsed,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate blackbox test cases using AI.
     */
    public function generateBlackbox(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'base_url' => ['nullable', 'string', 'max:500'],
        ]);

        $aiConfig = $this->getAiConfig();
        if (! $aiConfig['success']) {
            return response()->json($aiConfig, 422);
        }

        $systemPrompt = $this->buildBlackboxSystemPrompt($validated);
        $userPrompt = $validated['prompt'];

        try {
            $response = $this->callAi($aiConfig, $systemPrompt, $userPrompt);

            if (! $response['success']) {
                return response()->json($response, 422);
            }

            $parsed = $this->parseBlackboxResponse($response['content']);

            if (empty($parsed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI tidak dapat menghasilkan test cases yang valid. Coba ubah prompt Anda.',
                    'raw' => $response['content'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => $parsed,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get AI configuration from SiteSetting.
     */
    private function getAiConfig(): array
    {
        $setting = SiteSetting::query()->first();

        if (! $setting || ! $setting->ai_provider) {
            return [
                'success' => false,
                'message' => 'AI Provider belum dikonfigurasi. Silakan atur di menu Pengaturan > AI.',
            ];
        }

        $provider = $setting->ai_provider;

        if ($provider === 'gemini') {
            if (empty($setting->gemini_api_key)) {
                return [
                    'success' => false,
                    'message' => 'Gemini API Key belum diisi. Silakan atur di menu Pengaturan.',
                ];
            }

            return [
                'success' => true,
                'provider' => 'gemini',
                'api_key' => $setting->gemini_api_key,
            ];
        }

        if ($provider === 'openrouter') {
            if (empty($setting->openrouter_api_key)) {
                return [
                    'success' => false,
                    'message' => 'OpenRouter API Key belum diisi. Silakan atur di menu Pengaturan.',
                ];
            }

            return [
                'success' => true,
                'provider' => 'openrouter',
                'api_key' => $setting->openrouter_api_key,
            ];
        }

        return [
            'success' => false,
            'message' => "AI Provider \"{$provider}\" tidak didukung. Gunakan 'gemini' atau 'openrouter'.",
        ];
    }

    /**
     * Call AI API based on provider.
     */
    private function callAi(array $config, string $systemPrompt, string $userPrompt): array
    {
        return match ($config['provider']) {
            'gemini' => $this->callGemini($config['api_key'], $systemPrompt, $userPrompt),
            'openrouter' => $this->callOpenRouter($config['api_key'], $systemPrompt, $userPrompt),
            default => ['success' => false, 'message' => 'Provider tidak didukung.'],
        };
    }

    /**
     * Call Google Gemini API.
     */
    private function callGemini(string $apiKey, string $systemPrompt, string $userPrompt): array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

        $response = Http::timeout(60)->post($url, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemPrompt . "\n\n" . $userPrompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 4000,
                'responseMimeType' => 'application/json',
            ],
        ]);

        if ($response->failed()) {
            $error = $response->json('error.message', $response->body());

            return ['success' => false, 'message' => 'Gemini API Error: ' . $error];
        }

        $content = $response->json('candidates.0.content.parts.0.text', '');

        if (empty($content)) {
            return ['success' => false, 'message' => 'Gemini tidak mengembalikan response.'];
        }

        return ['success' => true, 'content' => $content];
    }

    /**
     * Call OpenRouter API (OpenAI-compatible).
     */
    private function callOpenRouter(string $apiKey, string $systemPrompt, string $userPrompt): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url', 'http://localhost'),
            'X-Title' => 'Blackbox Testing Tool',
        ])->timeout(60)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'google/gemini-2.0-flash-001',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 4000,
        ]);

        if ($response->failed()) {
            $error = $response->json('error.message', $response->body());

            return ['success' => false, 'message' => 'OpenRouter API Error: ' . $error];
        }

        $content = $response->json('choices.0.message.content', '');

        if (empty($content)) {
            return ['success' => false, 'message' => 'OpenRouter tidak mengembalikan response.'];
        }

        return ['success' => true, 'content' => $content];
    }

    /**
     * Build the system prompt for manual test scenario generation.
     */
    private function buildSystemPrompt(array $context): string
    {
        $projectInfo = '';
        if (! empty($context['project_name'])) {
            $projectInfo .= "Project: {$context['project_name']}. ";
        }
        if (! empty($context['base_url'])) {
            $projectInfo .= "URL: {$context['base_url']}. ";
        }
        if (! empty($context['module'])) {
            $projectInfo .= "Module: {$context['module']}. ";
        }

        return <<<PROMPT
Kamu adalah QA Engineer yang ahli dalam membuat test scenario untuk blackbox testing website.
{$projectInfo}

Tugas kamu adalah menghasilkan test scenario berdasarkan deskripsi user.

PENTING: Kamu HARUS merespons dalam format JSON yang valid dengan struktur berikut:
{
  "title": "Judul scenario testing",
  "description": "Deskripsi singkat scenario",
  "module": "Nama module (contoh: Login, Checkout, Profile)",
  "priority": "medium",
  "precondition": "Kondisi yang harus terpenuhi sebelum testing (atau null)",
  "steps": [
    {
      "action": "Langkah yang harus dilakukan tester",
      "expected_result": "Hasil yang diharapkan setelah langkah ini",
      "test_data": "Data input yang digunakan (atau null)"
    }
  ]
}

Aturan:
- Gunakan bahasa Indonesia untuk semua field
- Priority harus salah satu dari: low, medium, high, critical
- Steps minimal 3, maksimal 15 langkah
- Setiap step harus jelas dan spesifik
- Expected result harus bisa diverifikasi secara visual
- Test data berisi contoh data yang realistis
- Jangan tambahkan teks apapun di luar JSON
- Pastikan JSON valid dan bisa di-parse
PROMPT;
    }

    /**
     * Build the system prompt for blackbox test case generation.
     */
    private function buildBlackboxSystemPrompt(array $context): string
    {
        $projectInfo = '';
        if (! empty($context['project_name'])) {
            $projectInfo .= "Project: {$context['project_name']}. ";
        }
        if (! empty($context['base_url'])) {
            $projectInfo .= "Base URL: {$context['base_url']}. ";
        }

        return <<<PROMPT
Kamu adalah QA Engineer yang ahli dalam membuat automated blackbox test cases untuk website.
{$projectInfo}

Tugas kamu adalah menghasilkan daftar HTTP test cases berdasarkan deskripsi user.

PENTING: Kamu HARUS merespons dalam format JSON yang valid dengan struktur berikut:
{
  "test_cases": [
    {
      "title": "Judul test case",
      "description": "Deskripsi singkat",
      "method": "GET",
      "endpoint": "/path",
      "headers": null,
      "body_params": null,
      "expected_status": 200,
      "expected_contains": "teks yang harus ada di response (atau null)",
      "expected_not_contains": "teks yang tidak boleh ada (atau null)"
    }
  ]
}

Aturan:
- Gunakan bahasa Indonesia untuk title dan description
- Method harus salah satu dari: GET, POST, PUT, PATCH, DELETE
- Endpoint harus relative path (dimulai dengan /)
- headers dan body_params dalam format object JSON atau null
- expected_status harus HTTP status code yang valid (200, 201, 301, 302, 401, 403, 404, 422, 500, dll)
- Generate 3-10 test cases yang relevan
- Sertakan positive test (happy path) dan negative test (error handling)
- Jangan tambahkan teks apapun di luar JSON
- Pastikan JSON valid dan bisa di-parse
PROMPT;
    }

    /**
     * Parse AI response for manual test scenario.
     */
    private function parseManualScenarioResponse(string $content): ?array
    {
        $decoded = $this->extractJson($content);

        if (! is_array($decoded) || empty($decoded['steps'])) {
            return null;
        }

        $result = [
            'title' => $decoded['title'] ?? 'Untitled Scenario',
            'description' => $decoded['description'] ?? null,
            'module' => $decoded['module'] ?? null,
            'priority' => in_array($decoded['priority'] ?? '', ['low', 'medium', 'high', 'critical'])
                ? $decoded['priority']
                : 'medium',
            'precondition' => $decoded['precondition'] ?? null,
            'steps' => [],
        ];

        foreach ($decoded['steps'] as $step) {
            if (empty($step['action']) || empty($step['expected_result'])) {
                continue;
            }

            $result['steps'][] = [
                'action' => $step['action'],
                'expected_result' => $step['expected_result'],
                'test_data' => $step['test_data'] ?? null,
            ];
        }

        return empty($result['steps']) ? null : $result;
    }

    /**
     * Parse AI response for blackbox test cases.
     */
    private function parseBlackboxResponse(string $content): ?array
    {
        $decoded = $this->extractJson($content);

        if (! is_array($decoded) || empty($decoded['test_cases'])) {
            return null;
        }

        $cases = [];
        foreach ($decoded['test_cases'] as $case) {
            if (empty($case['endpoint']) || empty($case['method'])) {
                continue;
            }

            $cases[] = [
                'title' => $case['title'] ?? 'Untitled',
                'description' => $case['description'] ?? null,
                'method' => in_array($case['method'] ?? '', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])
                    ? $case['method']
                    : 'GET',
                'endpoint' => $case['endpoint'],
                'headers' => $case['headers'] ?? null,
                'body_params' => $case['body_params'] ?? null,
                'expected_status' => (int) ($case['expected_status'] ?? 200),
                'expected_contains' => $case['expected_contains'] ?? null,
                'expected_not_contains' => $case['expected_not_contains'] ?? null,
            ];
        }

        return empty($cases) ? null : ['test_cases' => $cases];
    }

    /**
     * Extract JSON from AI response (handles markdown code blocks).
     */
    private function extractJson(string $content): ?array
    {
        $content = trim($content);

        // Remove markdown code blocks if present
        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try to find JSON in the response
            if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
                $decoded = json_decode($matches[0], true);
            }
        }

        return $decoded;
    }
}
