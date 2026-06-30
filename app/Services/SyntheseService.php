<?php

namespace App\Services;

use App\Models\Syntheses;

class SyntheseService
{
    public function __construct(private ?LLMService $llm = null)
    {
        $this->llm = $llm ?? new LLMService();
    }

    public function generate(Syntheses $synthese): string
    {
        $ressources = $synthese->ressources;

        if ($ressources->count() < 2) {
            throw new \RuntimeException('Une synthèse nécessite au moins 2 ressources.');
        }

        $context = $ressources->map(function ($r, $i) {
            $lines = ['Ressource ' . ($i + 1) . ' : ' . ($r->nom_original ?? 'Sans titre')];
            if ($r->resume) {
                $lines[] = 'Résumé : ' . $r->resume;
            }
            return implode("\n", $lines);
        })->implode("\n\n");

        $existingText = $synthese->synthese
            ? "\n\nTexte existant (à compléter ou améliorer) :\n" . $synthese->synthese
            : '';

        $prompt = "Tu es un assistant de synthèse. Rédige une synthèse claire et structurée à partir des ressources suivantes.\n\n"
            . $context
            . $existingText . "\n\n"
            . "La synthèse doit mettre en lumière les points communs, les différences et les idées clés. "
            . "Rédige en français, en prose fluide, sans liste à puces.";

        return $this->llm->call($prompt);
    }
}
