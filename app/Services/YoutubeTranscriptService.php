<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YoutubeTranscriptService
{
    public function fetch(string $url): ?string
    {
        $videoId = $this->extractVideoId($url);
        if (!$videoId) {
            return null;
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8',
        ])->get("https://www.youtube.com/watch?v={$videoId}");

        if (!$response->successful()) {
            return null;
        }

        $captionTracks = $this->extractCaptionTracks($response->body());
        if (empty($captionTracks)) {
            return null;
        }

        $transcriptUrl = $this->selectBestTrack($captionTracks);
        if (!$transcriptUrl) {
            return null;
        }

        $transcriptResponse = Http::get($transcriptUrl);
        if (!$transcriptResponse->successful()) {
            return null;
        }

        return $this->parseTranscriptXml($transcriptResponse->body());
    }

    private function extractVideoId(string $url): ?string
    {
        if (preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function extractCaptionTracks(string $html): array
    {
        $start = strpos($html, '"captionTracks":');
        if ($start === false) {
            return [];
        }

        $start += strlen('"captionTracks":');
        $arrayStart = strpos($html, '[', $start);
        if ($arrayStart === false) {
            return [];
        }

        // Balance brackets to find the end of the array
        $depth = 0;
        $end = $arrayStart;
        $len = strlen($html);

        for ($i = $arrayStart; $i < $len; $i++) {
            $char = $html[$i];
            if ($char === '[' || $char === '{') {
                $depth++;
            } elseif ($char === ']' || $char === '}') {
                $depth--;
                if ($depth === 0) {
                    $end = $i;
                    break;
                }
            }
        }

        $json = substr($html, $arrayStart, $end - $arrayStart + 1);
        return json_decode($json, true) ?? [];
    }

    private function selectBestTrack(array $tracks): ?string
    {
        $priorities = ['fr', 'en'];

        // Prefer non-auto-generated tracks first
        foreach ($priorities as $lang) {
            foreach ($tracks as $track) {
                $code = $track['languageCode'] ?? '';
                $isAsr = str_starts_with($track['vssId'] ?? '', 'a.');
                if (str_starts_with($code, $lang) && !$isAsr) {
                    return $track['baseUrl'] ?? null;
                }
            }
        }

        // Fall back to auto-generated tracks
        foreach ($priorities as $lang) {
            foreach ($tracks as $track) {
                if (str_starts_with($track['languageCode'] ?? '', $lang)) {
                    return $track['baseUrl'] ?? null;
                }
            }
        }

        // Take whatever is available
        return $tracks[0]['baseUrl'] ?? null;
    }

    private function parseTranscriptXml(string $xml): ?string
    {
        libxml_use_internal_errors(true);
        $parsed = simplexml_load_string($xml);
        libxml_clear_errors();

        if (!$parsed) {
            return null;
        }

        $texts = [];
        foreach ($parsed->text as $text) {
            $decoded = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $cleaned = preg_replace('/\s+/', ' ', trim($decoded));
            if ($cleaned !== '') {
                $texts[] = $cleaned;
            }
        }

        return implode(' ', $texts) ?: null;
    }
}
