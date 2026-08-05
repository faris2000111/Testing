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
        @set_time_limit(300);
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

        $response = Http::timeout(180)->post($url, [
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
        @set_time_limit(300);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url', 'http://localhost'),
            'X-Title' => 'Blackbox Testing Tool',
        ])->timeout(180)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'openrouter/free',
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
Kamu adalah QA Engineer yang ahli dalam membuat test scenario untuk blackbox testing website berbasis Laravel/PHP.
{$projectInfo}

## KONTEKS PENTING

Website target menggunakan framework Laravel dengan perilaku berikut:
- Form submission (login, register, CRUD) menggunakan POST dengan redirect 302
- Login gagal: redirect back ke halaman login dengan pesan error
- Login berhasil: redirect ke halaman dashboard
- Validasi gagal: redirect back ke form dengan pesan error
- CRUD berhasil: redirect ke halaman index dengan flash message "berhasil"
- Halaman yang butuh login: redirect ke /login
- CSRF token otomatis di-handle oleh tool testing

## TUGAS

Menghasilkan test scenario manual berdasarkan deskripsi user.

## FORMAT RESPONSE

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

## ATURAN
- Gunakan bahasa Indonesia untuk semua field
- Priority harus salah satu dari: low, medium, high, critical
- Steps minimal 3, maksimal 15 langkah
- Setiap step harus jelas dan spesifik
- Expected result harus realistis sesuai perilaku Laravel:
  - Login berhasil → "User diarahkan ke halaman Dashboard"
  - Login gagal → "Muncul pesan error di halaman login"
  - Form validasi gagal → "Muncul pesan error validasi di bawah field"
  - CRUD berhasil → "Muncul notifikasi berhasil dan data tampil di tabel"
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
Kamu adalah QA Engineer yang ahli dalam membuat automated blackbox test cases untuk website berbasis Laravel/PHP.
{$projectInfo}

## KONTEKS PENTING TENTANG TOOL TESTING INI

Tool testing ini bekerja dengan cara:
1. Mengirim HTTP request ke website target
2. TIDAK follow redirect (allow_redirects = false)
3. Menangkap status code ASLI dari response pertama
4. Untuk pengecekan konten, tool akan follow redirect secara terpisah dan cek body halaman tujuan
5. CSRF token (_token) otomatis di-inject ke setiap POST/PUT/PATCH/DELETE, JANGAN masukkan _token di body_params

## ATURAN STATUS CODE UNTUK WEBSITE LARAVEL

Website Laravel menggunakan redirect (302) untuk hampir semua form submission:

### Form Login (POST /login):
- Login BERHASIL → status 302 (redirect ke dashboard)
- Login GAGAL (credential salah) → status 302 (redirect back ke halaman login)
- Login GAGAL (validasi/field kosong) → status 302 (redirect back ke halaman login)

### Form CRUD (POST/PUT/DELETE):
- Berhasil create/update/delete → status 302 (redirect ke halaman index)
- Validasi gagal → status 302 (redirect back ke form)

### Halaman GET biasa:
- Halaman bisa diakses → status 200
- Halaman butuh login (belum auth) → status 302 (redirect ke /login)
- Halaman tidak ditemukan → status 404
- Halaman forbidden → status 403

### JANGAN PERNAH gunakan status code ini untuk website Laravel:
- 401 (Laravel tidak return 401 untuk form login, gunakan 302)
- 400 (Laravel tidak return 400 untuk validasi form, gunakan 302)
- 201 (Laravel redirect 302 setelah create, bukan 201)

## ATURAN EXPECTED_CONTAINS

Untuk pengecekan konten setelah redirect:
- Login berhasil → expected_contains bisa: "Dashboard" atau "dashboard" (teks di halaman tujuan)
- Login gagal → expected_contains bisa: teks error yang muncul di halaman, misalnya pesan dari withErrors()
- Halaman GET → expected_contains bisa: judul halaman atau teks unik di halaman tersebut
- Jika tidak yakin teks apa yang muncul, SET NULL (jangan tebak-tebak)

## FORMAT RESPONSE

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
      "expected_contains": null,
      "expected_not_contains": null
    }
  ]
}

## ATURAN UMUM
- Gunakan bahasa Indonesia untuk title dan description
- Method harus salah satu dari: GET, POST, PUT, PATCH, DELETE
- Endpoint harus relative path (dimulai dengan /)
- body_params: object JSON untuk POST/PUT/PATCH, JANGAN sertakan _token (otomatis ditambahkan tool)
- headers: biasanya null (tool sudah handle cookies dan session)
- expected_status: HARUS sesuai aturan Laravel di atas (302 untuk form/POST login/POST logout, 200 untuk GET)
- expected_contains: HANYA isi jika kamu YAKIN teks tersebut ada di halaman. Untuk POST /login dan POST /logout HARUS set expected_contains ke null!
- expected_not_contains: HANYA isi jika kamu YAKIN teks tersebut TIDAK boleh ada. Jika ragu, set null
- Untuk Logout: HARUS menggunakan method POST ke /logout (bukan GET), dengan expected_status 302.
- ATURAN PENTING URUTAN LOGOUT: Test case POST /logout HARUS SELALU ditaruh di URUTAN PALING AKHIR dari pengujian role! JANGAN PERNAH menaruh /logout di tengah-tengah atau sebelum pengujian halaman dashboard/internal!
- Generate SEBANYAK-BANYAKNYA test cases komprehensif dan sangat lengkap untuk menguji SELURUH halaman, menu admin, sub-menu, CRUD form (create, edit, delete), validasi, dan fitur pengguna.
- Sertakan positive test (happy path) dan negative test (error handling)
- URUTAN TEST CASE SANGAT PENTING! SELALU gunakan urutan ini:
  1. POST /login (Login berhasil)
  2. GET halaman index, dashboard, dan seluruh sub-menu internal
  3. CRUD (Create, Edit, Update, Delete)
  4. Negative tests (validasi gagal, password salah)
  5. POST /logout (HARUS TERAKHIR!)
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

        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * Parse AI response for blackbox test cases.
     */
    private function parseBlackboxResponse(string $content): ?array
    {
        $decoded = $this->extractJson($content);

        if (! is_array($decoded)) {
            return null;
        }

        // Support various root keys: test_cases, cases, or direct indexed array
        $rawCases = [];
        if (isset($decoded['test_cases']) && is_array($decoded['test_cases'])) {
            $rawCases = $decoded['test_cases'];
        } elseif (isset($decoded['cases']) && is_array($decoded['cases'])) {
            $rawCases = $decoded['cases'];
        } elseif (array_is_list($decoded)) {
            $rawCases = $decoded;
        }

        if (empty($rawCases)) {
            return null;
        }

        $cases = [];
        foreach ($rawCases as $case) {
            if (! is_array($case) || empty($case['endpoint']) || empty($case['method'])) {
                continue;
            }

            $endpoint = $case['endpoint'];
            if (! str_starts_with($endpoint, '/')) {
                $endpoint = '/' . $endpoint;
            }

            $method = in_array(strtoupper($case['method'] ?? ''), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])
                ? strtoupper($case['method'])
                : 'GET';

            $expectedStatus = (int) ($case['expected_status'] ?? 200);
            $expectedContains = $case['expected_contains'] ?? null;

            // Auto-correct logout method to POST (since Laravel /logout requires POST)
            if (rtrim(strtolower($endpoint), '/') === '/logout' || str_contains(strtolower($endpoint), 'logout')) {
                $method = 'POST';
                $expectedStatus = 302;
                $expectedContains = null;
            }

            $cases[] = [
                'title' => $case['title'] ?? 'Untitled Test Case',
                'description' => $case['description'] ?? null,
                'method' => $method,
                'endpoint' => $endpoint,
                'headers' => $case['headers'] ?? null,
                'body_params' => $case['body_params'] ?? null,
                'expected_status' => $expectedStatus,
                'expected_contains' => $expectedContains,
                'expected_not_contains' => $case['expected_not_contains'] ?? null,
            ];
        }

        if (empty($cases)) {
            return null;
        }

        // Re-order test cases so that POST /logout ALWAYS comes at the very end after authenticated tests
        $nonLogout = [];
        $logouts = [];
        foreach ($cases as $c) {
            $isLogout = rtrim(strtolower($c['endpoint']), '/') === '/logout'
                || str_contains(strtolower($c['title']), 'logout');
            if ($isLogout) {
                $logouts[] = $c;
            } else {
                $nonLogout[] = $c;
            }
        }
        $cases = array_merge($nonLogout, $logouts);

        return ['test_cases' => $cases];
    }

    /**
     * Extract JSON from AI response (robust handling for markdown, think tags, root arrays, and wrapped objects).
     */
    private function extractJson(string $content): ?array
    {
        $content = trim($content);

        // 1. Remove thinking / reasoning blocks (e.g. <think>...</think>)
        $content = preg_replace('/<think>[\s\S]*?<\/think>/i', '', $content);
        $content = trim($content);

        // 2. Extract content from markdown code blocks ```json ... ``` or ``` ... ```
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $content, $matches)) {
            $codeBlockContent = trim($matches[1]);
            $decoded = json_decode($codeBlockContent, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            $content = $codeBlockContent;
        }

        // 3. Direct json_decode attempt
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // 4. Targeted extraction between first '{' and last '}' (object schema)
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $jsonString = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
            $decoded = json_decode($jsonString, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // 5. Targeted extraction between first '[' and last ']' (root-level array schema)
        $firstBracket = strpos($content, '[');
        $lastBracket = strrpos($content, ']');
        if ($firstBracket !== false && $lastBracket !== false && $lastBracket > $firstBracket) {
            $jsonString = substr($content, $firstBracket, $lastBracket - $firstBracket + 1);
            $decoded = json_decode($jsonString, true);
            if (is_array($decoded)) {
                return ['test_cases' => $decoded];
            }
        }

        // 6. Try repairing truncated JSON (if model hit output token limit mid-response)
        $repaired = $this->tryRepairTruncatedJson($content);
        if (is_array($repaired)) {
            return $repaired;
        }

        return null;
    }

    /**
     * Attempt to repair truncated JSON from AI responses that hit token limits.
     */
    private function tryRepairTruncatedJson(string $content): ?array
    {
        // Try repairing truncated root object {...
        $firstBrace = strpos($content, '{');
        if ($firstBrace !== false) {
            $jsonCandidate = substr($content, $firstBrace);
            $lastObjectBrace = strrpos($jsonCandidate, '}');
            if ($lastObjectBrace !== false) {
                $truncated = substr($jsonCandidate, 0, $lastObjectBrace + 1);
                $truncated = rtrim(rtrim($truncated), ',');

                $attempts = [
                    $truncated . ']}',
                    $truncated . ']',
                    $truncated . '}',
                ];

                foreach ($attempts as $attempt) {
                    $decoded = json_decode($attempt, true);
                    if (is_array($decoded) && (! empty($decoded['test_cases']) || ! empty($decoded['cases']) || array_is_list($decoded))) {
                        return $decoded;
                    }
                }
            }
        }

        // Try repairing truncated root array [...
        $firstBracket = strpos($content, '[');
        if ($firstBracket !== false) {
            $jsonCandidate = substr($content, $firstBracket);
            $lastObjectBrace = strrpos($jsonCandidate, '}');
            if ($lastObjectBrace !== false) {
                $truncated = substr($jsonCandidate, 0, $lastObjectBrace + 1);
                $truncated = rtrim(rtrim($truncated), ',');

                $decoded = json_decode($truncated . ']', true);
                if (is_array($decoded) && array_is_list($decoded)) {
                    return ['test_cases' => $decoded];
                }
            }
        }

        return null;
    }
}
