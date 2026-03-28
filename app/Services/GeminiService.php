<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $baseApiUrl;
    private array $models = [
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
        'gemini-1.5-flash',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->baseApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    }

    /**
     * Generate course titles for a given category using Gemini AI.
     *
     * @return array<string>
     */
    public function generateCourseTitles(string $category): array
    {
        $prompt = "Generate exactly 15 realistic Arabic YouTube course/playlist titles for the category: \"{$category}\". "
            . "The titles should be educational course names that someone would actually search for on YouTube. "
            . "Return ONLY a JSON array of strings, no other text. Example format: [\"title1\", \"title2\"]";

        // Try each model with retry
        foreach ($this->models as $model) {
            $titles = $this->tryGenerateWithModel($model, $prompt);
            if (!empty($titles)) {
                return $titles;
            }
        }

        // Fallback: generate titles locally without AI
        Log::warning('All Gemini models failed, using local fallback for category: ' . $category);
        return $this->generateLocalFallbackTitles($category);
    }

    private function tryGenerateWithModel(string $model, string $prompt, int $maxRetries = 2): array
    {
        $url = "{$this->baseApiUrl}{$model}:generateContent?key={$this->apiKey}";

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::timeout(30)->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.8,
                        'maxOutputTokens' => 1024,
                    ],
                ]);

                if ($response->successful()) {
                    $titles = $this->parseResponse($response);
                    if (!empty($titles)) {
                        return $titles;
                    }
                }

                // If rate limited (429), wait and retry
                if ($response->status() === 429 && $attempt < $maxRetries) {
                    $retryDelay = $this->extractRetryDelay($response->body());
                    Log::info("Gemini rate limited on {$model}, retrying in {$retryDelay}s (attempt " . ($attempt + 1) . ")");
                    sleep($retryDelay);
                    continue;
                }

                Log::error("Gemini API failed with model {$model}", [
                    'status' => $response->status(),
                    'attempt' => $attempt,
                ]);
                break; // Non-429 error, try next model

            } catch (\Exception $e) {
                Log::error("Gemini service error with model {$model}", ['message' => $e->getMessage()]);
                break;
            }
        }

        return [];
    }

    private function parseResponse($response): array
    {
        $text = $response->json('candidates.0.content.parts.0.text', '');

        preg_match('/\[.*\]/s', $text, $matches);

        if (empty($matches)) {
            Log::warning('Could not parse Gemini response', ['text' => $text]);
            return [];
        }

        $titles = json_decode($matches[0], true);

        return is_array($titles) ? array_values(array_filter($titles, 'is_string')) : [];
    }

    private function extractRetryDelay(string $body): int
    {
        // Extract retry delay from API response (e.g. "retryDelay": "20s")
        if (preg_match('/"retryDelay":\s*"(\d+)/', $body, $matches)) {
            return min((int) $matches[1], 30); // Cap at 30 seconds
        }
        return 15; // Default 15 seconds
    }

    private function generateLocalFallbackTitles(string $category): array
    {
        $templates = [
            'دورة شاملة في %s للمبتدئين',
            'تعلم %s من الصفر حتى الاحتراف',
            'كورس %s الكامل',
            'أساسيات %s - شرح مبسط',
            '%s للمبتدئين - خطوة بخطوة',
            'احتراف %s في 30 يوم',
            'مقدمة في %s',
            'دليلك الشامل لتعلم %s',
            '%s - المستوى المتقدم',
            'تعلم %s بالعربي',
            'ماستر كلاس في %s',
            '%s من البداية للنهاية',
            'أسرار %s - نصائح المحترفين',
            'مشاريع عملية في %s',
            'كل ما تحتاج معرفته عن %s',
        ];

        return array_map(fn($t) => sprintf($t, $category), $templates);
    }
}
