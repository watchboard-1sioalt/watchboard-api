<?php

namespace App\Http\Controllers;

use App\Models\Ressources;
use App\Services\ResumeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RessourceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $ressources = Ressources::with('tags')->get();
        return response()->json($ressources);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url'          => 'required|url|max:2048',
            'nom_original' => 'nullable|string|max:150',
        ]);

        $ressource = Ressources::create([
            'type'           => 'url',
            'url'            => $validated['url'],
            'nom_original'   => $validated['nom_original'] ?? null,
            'id_utilisateur' => Auth::id(),
        ]);

        return response()->json($ressource, 201);
    }

    public function storeFromRss(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url'          => 'required|url|max:2048',
            'nom_original' => 'nullable|string|max:150',
            'id_fluxrss'   => 'required|integer|exists:flux_rss,id_fluxrss',
        ]);

        $ressource = Ressources::create([
            'type'           => 'rss',
            'url'            => $validated['url'],
            'nom_original'   => $validated['nom_original'] ?? null,
            'id_utilisateur' => Auth::id(),
            'id_fluxrss'     => $validated['id_fluxrss'],
        ]);

        return response()->json($ressource, 201);
    }

    public function storeFromFile(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,md,pdf|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store('ressources');

        $ressource = Ressources::create([
            'type'          => 'file',
            'url'           => $path,
            'nom_original'  => $file->getClientOriginalName(),
            'id_utilisateur' => Auth::id(),
        ]);

        return response()->json($ressource, 201);
    }

    public function storeFromYoutube(Request $request): JsonResponse
    {
        $request->validate([
            'url'          => ['required', 'url', 'regex:/(youtube\.com|youtu\.be)/'],
            'nom_original' => 'nullable|string|max:150',
        ]);

        $ressource = Ressources::create([
            'type'           => 'youtube',
            'url'            => $request->input('url'),
            'nom_original'   => $request->input('nom_original'),
            'id_utilisateur' => Auth::id(),
        ]);

        return response()->json($ressource, 201);
    }

    public function show(int $id): JsonResponse
    {
        $ressource = Ressources::with('tags')->findOrFail($id);
        return response()->json($ressource);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $ressource = Ressources::findOrFail($id);

        $validated = $request->validate([
            'nom_original' => 'sometimes|nullable|string|max:150',
            'resume'       => 'sometimes|nullable|string',
        ]);

        $ressource->update($validated);

        return response()->json($ressource);
    }

    public function destroy(int $id): JsonResponse
    {
        Ressources::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    public function attachTag(Request $request, int $id): JsonResponse
    {
        $ressource = Ressources::findOrFail($id);
        $ressource->tags()->syncWithoutDetaching([$request->integer('tag_id')]);
        return response()->json(null, 204);
    }

    public function detachTag(int $id, int $tagId): JsonResponse
    {
        Ressources::findOrFail($id)->tags()->detach($tagId);
        return response()->json(null, 204);
    }

    public function generateResume(int $id): JsonResponse
    {
        $ressource = Ressources::findOrFail($id);

        if ($ressource->id_utilisateur !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $resume = (new ResumeService())->generate($ressource);
        } catch (\Throwable) {
            return response()->json(['message' => 'Le service de résumé est temporairement indisponible. Veuillez réessayer dans quelques instants.'], 503);
        }

        $ressource->update(['resume' => $resume]);

        return response()->json(['resume' => $resume]);
    }
}
