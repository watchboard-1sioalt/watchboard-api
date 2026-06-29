<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class YoutubeTranscriptService
{
    public function fetch(string $url): ?string
    {
        $videoId = $this->extractVideoId($url);
        if (!$videoId) {
            return null;
        }

        $tmpDir = sys_get_temp_dir() . '/yt_transcript_' . uniqid();
        mkdir($tmpDir, 0700, true);

        try {
            return $this->fetchViaYtDlp($videoId, $tmpDir);
        } finally {
            $this->cleanup($tmpDir);
        }
    }

    private function fetchViaYtDlp(string $videoId, string $tmpDir): ?string
    {
        $ytUrl = "https://www.youtube.com/watch?v={$videoId}";
        $output = $tmpDir . '/%(id)s';

        $result = Process::timeout(60)->run([
            'yt-dlp',
            '--write-subs',
            '--write-auto-subs',
            '--sub-langs', 'fr,en',
            '--sub-format', 'vtt',
            '--skip-download',
            '--no-warnings',
            '-o', $output,
            $ytUrl,
        ]);

        // Priority: fr (manual) > en (manual) > fr (auto) > en (auto) > any
        $candidates = [
            "{$tmpDir}/{$videoId}.fr.vtt",
            "{$tmpDir}/{$videoId}.en.vtt",
        ];

        foreach ($candidates as $path) {
            if (file_exists($path) && filesize($path) > 0) {
                return $this->parseVtt(file_get_contents($path));
            }
        }

        // Fallback: pick any .vtt file found
        $files = glob("{$tmpDir}/*.vtt") ?: [];
        foreach ($files as $path) {
            if (filesize($path) > 0) {
                return $this->parseVtt(file_get_contents($path));
            }
        }

        return null;
    }

    private function parseVtt(string $vtt): ?string
    {
        // Strip word-level timing tags: <00:00:01.120><c>word</c> → word
        $vtt = preg_replace('/<[^>]+>/', '', $vtt);

        $lines = explode("\n", $vtt);
        $texts = [];
        $seen = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip header, timestamps, and empty lines
            if ($line === '' || $line === 'WEBVTT' || str_contains($line, '-->') ||
                preg_match('/^(Kind|Language):/', $line)) {
                continue;
            }

            // Deduplicate: YouTube VTT repeats previous caption text in each cue
            if (!isset($seen[$line])) {
                $seen[$line] = true;
                $texts[] = $line;
            }
        }

        $result = implode(' ', $texts);
        return $result !== '' ? $result : null;
    }

    private function extractVideoId(string $url): ?string
    {
        if (preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function cleanup(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (glob("{$dir}/*") ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($dir);
    }
}
