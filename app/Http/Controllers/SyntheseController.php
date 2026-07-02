<?php

namespace App\Http\Controllers;

use App\Models\Ressources;
use App\Models\Syntheses;
use App\Services\SyntheseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyntheseController extends Controller
{
    public function index(): JsonResponse
    {
        $syntheses = Syntheses::with('ressources')
            ->where('id_utilisateur', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json($syntheses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'synthese'        => 'nullable|string',
            'ressource_ids'   => 'required|array|min:2',
            'ressource_ids.*' => 'integer|exists:ressources,id_ressource',
        ]);

        $count = Ressources::whereIn('id_ressource', $validated['ressource_ids'])
            ->where('id_utilisateur', Auth::id())
            ->count();

        if ($count !== count($validated['ressource_ids'])) {
            return response()->json(['message' => 'Certaines ressources sont introuvables ou ne vous appartiennent pas.'], 403);
        }

        $synthese = Syntheses::create([
            'synthese'       => $validated['synthese'] ?? null,
            'date_creation'  => now(),
            'id_utilisateur' => Auth::id(),
        ]);

        $synthese->ressources()->attach($validated['ressource_ids']);

        return response()->json($synthese->load('ressources'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $synthese = Syntheses::with('ressources')
            ->where('id_synthese', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        return response()->json($synthese);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $synthese = Syntheses::where('id_synthese', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'synthese'        => 'sometimes|nullable|string',
            'ressource_ids'   => 'sometimes|array|min:2',
            'ressource_ids.*' => 'integer|exists:ressources,id_ressource',
        ]);
enregistrées
        if (isset($validated['ressource_ids'])) {
            $count = Ressources::whereIn('id_ressource', $validated['ressource_ids'])
                ->where('id_utilisateur', Auth::id())
                ->count();

            if ($count !== count($validated['ressource_ids'])) {
                return response()->json(['message' => 'Certaines ressources sont introuvables ou ne vous appartiennent pas.'], 403);
            }

            $synthese->ressources()->sync($validated['ressource_ids']);
        }

        if (array_key_exists('synthese', $validated)) {
            $synthese->update(['synthese' => $validated['synthese']]);
        }

        return response()->json($synthese->load('ressources'));
    }

    public function destroy(int $id): JsonResponse
    {
        $synthese = Syntheses::where('id_synthese', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $synthese->ressources()->detach();
        $synthese->delete();

        return response()->json(null, 204);
    }

    public function generate(int $id): JsonResponse
    {
        $synthese = Syntheses::with('ressources')
            ->where('id_synthese', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        try {
            $text = (new SyntheseService())->generate($synthese);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            \Log::error('SyntheseService failed', ['error' => $e->getMessage(), 'userId' => Auth::id()]);
            return response()->json(['message' => 'Le service de synthèse est temporairement indisponible. Veuillez réessayer dans quelques instants.'], 503);
        }

        $synthese->update(['synthese' => $text]);

        return response()->json(['synthese' => $text]);
    }

    public function attachRessource(Request $request, int $id): JsonResponse
    {
        $synthese = Syntheses::where('id_synthese', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'ressource_id' => 'required|integer|exists:ressources,id_ressource',
        ]);

        Ressources::where('id_ressource', $validated['ressource_id'])
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $synthese->ressources()->syncWithoutDetaching([$validated['ressource_id']]);

        return response()->json(null, 204);
    }

    public function detachRessource(int $id, int $ressourceId): JsonResponse
    {
        $synthese = Syntheses::where('id_synthese', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $synthese->ressources()->detach($ressourceId);

        return response()->json(null, 204);
    }
}
