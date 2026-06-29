<?php

namespace App\Services;

use App\Models\Ressources;

class TagService
{
    public function __construct(private ?LLMService $llm = null)
    {
        $this->llm = $llm ?? new LLMService();
    }

    public function generateTags(Ressources $ressource): array
    {
        $titre = $ressource->nom_original ?? '';
        $description = $ressource->resume ?? '';

        $context = "Titre : {$titre}";
        if ($description) {
            $context .= "\nDescription : {$description}";
        }

        $prompt = "Tu es un assistant de classification de contenu. "
            . "Génère entre 3 et 8 tags pertinents pour cette ressource.\n\n"
            . $context . "\n\n"
            . "Réponds UNIQUEMENT avec un objet JSON valide, sans texte supplémentaire, au format suivant :\n"
            . '{"tags": ["tag1", "tag2", "tag3"]}' . "\n\n"
            . "Les tags doivent être courts (1 à 3 mots), en minuscules, "
            . "et dans la langue du contenu (français ou anglais).";

        $raw = $this->llm->call($prompt);

        return $this->parseTagsFromResponse($raw);
    }

    private function parseTagsFromResponse(string $raw): array
    {
        // Strip markdown code blocks if present
        $cleaned = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $cleaned = preg_replace('/\s*```$/m', '', $cleaned);
        $cleaned = trim($cleaned);

        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['tags']) || !is_array($data['tags'])) {
            throw new \RuntimeException('La réponse du service de tags est invalide.');
        }

        return array_values(array_filter(
            array_map(fn($t) => strtolower(trim((string) $t)), $data['tags']),
            fn($t) => $t !== ''
        ));
    }
}
