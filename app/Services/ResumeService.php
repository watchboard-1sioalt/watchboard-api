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
        } else {
            $context = "Titre : {$ressource->nom_original}\nURL : {$ressource->url}";
            $prompt = "Tu es un assistant de veille informationnelle. "
                . "Génère un résumé concis (3 à 5 phrases) de cette vidéo YouTube en français.\n\n"
                . $context;
        }

        $result = Gemini::generativeModel('models/gemini-2.5-flash-lite')->generateContent($prompt);

        return $result->text();
    }
}
