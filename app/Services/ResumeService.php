<?php

namespace App\Services;

use App\Models\Ressources;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ResumeService
{
    public function generate(Ressources $ressource): string
    {
        if ($ressource->type === 'file') {
            $content = Storage::get($ressource->url);
            $prompt = "Tu es un assistant de veille informationnelle. "
                . "Génère un résumé concis (3 à 5 phrases) de ce document en français.\n\n"
                . $content;
        } elseif ($ressource->type === 'youtube') {
            $transcript = (new YoutubeTranscriptService())->fetch($ressource->url);

            if (!$transcript) {
                throw new \RuntimeException('Aucune transcription disponible pour cette vidéo YouTube.');
            }

            $header = $ressource->nom_original
                ? "Titre : {$ressource->nom_original}\n\n"
                : '';
            $prompt = "Tu es un assistant de veille informationnelle. "
                . "Génère un résumé concis (3 à 5 phrases) de cette vidéo YouTube en français, "
                . "en te basant sur sa transcription.\n\n"
                . $header
                . "Transcription :\n"
                . $transcript;
        } elseif ($ressource->type === 'url' || $ressource->type === 'rss') {
            $pageText = $this->fetchPageText($ressource->url);
            $header = "Titre : {$ressource->nom_original}\nURL : {$ressource->url}\n\n";

            if ($pageText) {
                $prompt = "Tu es un assistant de veille informationnelle. "
                    . "Génère un résumé concis (3 à 5 phrases) de cet article ou page web en français.\n\n"
                    . $header
                    . "Contenu :\n"
                    . $pageText;
            } else {
                $prompt = "Tu es un assistant de veille informationnelle. "
                    . "Génère un résumé concis (3 à 5 phrases) de cette ressource en français "
                    . "en te basant uniquement sur son titre et son URL.\n\n"
                    . $header;
            }
        } else {
            $context = "Titre : {$ressource->nom_original}\nURL : {$ressource->url}";
            $prompt = "Tu es un assistant de veille informationnelle. "
                . "Génère un résumé concis (3 à 5 phrases) de cette ressource en français.\n\n"
                . $context;
        }

        return retry(3, function () use ($prompt) {
            $result = Gemini::generativeModel('models/gemini-2.5-flash-lite')->generateContent($prompt);
            return $result->text();
        }, sleepMilliseconds: 2000);
    }

    private function fetchPageText(string $url): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; WatchboardBot/1.0)'])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();

            $dom = new \DOMDocument();
            @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

            foreach (['script', 'style', 'nav', 'footer', 'header', 'aside'] as $tag) {
                foreach (iterator_to_array($dom->getElementsByTagName($tag)) as $node) {
                    $node->parentNode?->removeChild($node);
                }
            }

            $body = $dom->getElementsByTagName('body')->item(0);
            $text = $body ? $body->textContent : $dom->textContent;

            $text = trim(preg_replace('/\s+/', ' ', $text));

            if (strlen($text) > 6000) {
                $text = substr($text, 0, 6000) . '...';
            }

            return $text ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
}
