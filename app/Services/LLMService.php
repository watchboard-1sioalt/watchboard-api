<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LLMService
{
    public function call(string $prompt): string
    {
        $providers = [
            fn() => $this->callGeminiFlashLite($prompt),
            fn() => $this->callGroq($prompt),
            fn() => $this->callGemini15Flash($prompt),
        ];

        foreach ($providers as $provider) {
            try {
                return $provider();
            } catch (\Exception $e) {
                Log::warning('AI provider failed, trying next', ['error' => $e->getMessage()]);
            }
        }

        throw new \RuntimeException('All AI providers failed');
    }

    private function callGeminiFlashLite(string $prompt): string
    {
        return retry(3, function () use ($prompt) {
            $result = Gemini::generativeModel('models/gemini-2.5-flash-lite')->generateContent($prompt);
            return $result->text();
        }, sleepMilliseconds: 2000, when: fn($e) => !str_contains($e->getMessage(), 'Quota exceeded'));
    }

    private function callGroq(string $prompt): string
    {
        if (strlen($prompt) > 8000) {
            $prompt = substr($prompt, 0, 8000) . '...';
        }

        $response = Http::withToken(config('services.groq.key'))
            ->timeout(30)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'      => 'llama-3.3-70b-versatile',
                'messages'   => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => 512,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Groq API error: ' . $response->status());
        }

        return $response->json('choices.0.message.content')
            ?? throw new \RuntimeException('Groq returned no content');
    }

    private function callGemini15Flash(string $prompt): string
    {
        $result = Gemini::generativeModel('models/gemini-2.0-flash')->generateContent($prompt);
        return $result->text();
    }
}
