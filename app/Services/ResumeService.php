<?php

namespace App\Services;

use App\Models\Ressources;
use Gemini\Laravel\Facades\Gemini;
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
        } else {
            $context = "Titre : {$ressource->nom_original}\nURL : {$ressource->url}";
            $prompt = "Tu es un assistant de veille informationnelle. "
                . "Génère un résumé concis (3 à 5 phrases) de cette ressource en français.\n\n"
                . $context;
        }

        return retry(3, function () use ($prompt) {
            $result = Gemini::generativeModel('models/gemini-1.5-flash')->generateContent($prompt);
            return $result->text();
        }, sleepMilliseconds: 2000);
    }
}
