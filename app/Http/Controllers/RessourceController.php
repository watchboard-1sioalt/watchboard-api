<?php

namespace App\Http\Controllers;

use App\Models\Ressources;
use App\Models\Utilisateurs;
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
            'image'        => 'nullable|url|max:2048',
            'resume'       => 'nullable|string',
        ]);

        $ressource = Ressources::create([
            'type'           => 'url',
            'url'            => $validated['url'],
            'nom_original'   => $validated['nom_original'] ?? null,
            'image'          => $validated['image'] ?? null,
            'resume'         => $validated['resume'] ?? null,
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
            'image'        => 'nullable|url|max:2048',
            'resume'       => 'nullable|string',
        ]);

        $isYoutube = (bool) preg_match('#(youtube\.com|youtu\.be)#i', $validated['url']);

        $ressource = Ressources::create([
            'type'           => $isYoutube ? 'youtube' : 'rss',
            'url'            => $validated['url'],
            'nom_original'   => $validated['nom_original'] ?? null,
            'image'          => $validated['image'] ?? null,
            'resume'         => $validated['resume'] ?? null,
            'id_utilisateur' => Auth::id(),
            'id_fluxrss'     => $validated['id_fluxrss'],
        ]);

        return response()->json($ressource, 201);
    }

    public function storeFromFile(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,pdf,jpg,jpeg,png,mp3,mp4,docx|max:51200',
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
            'image'        => 'nullable|url|max:2048',
        ]);

        $ressource = Ressources::create([
            'type'           => 'youtube',
            'url'            => $request->input('url'),
            'nom_original'   => $request->input('nom_original'),
            'image'          => $request->input('image'),
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

    public function share(Request $request, int $id): JsonResponse
    {
        $ressource = Ressources::findOrFail($id);

        if ($ressource->id_utilisateur !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $destinataire = Utilisateurs::where('email', $validated['email'])->first();

        if (!$destinataire) {
            return response()->json(['message' => 'Aucun compte trouvé pour cet email.'], 404);
        }

        if ($destinataire->id_utilisateur === Auth::id()) {
            return response()->json(['message' => 'Vous ne pouvez pas partager une ressource avec vous-même.'], 422);
        }

        $ressource->partages()->syncWithoutDetaching([$destinataire->id_utilisateur]);

        return response()->json(null, 204);
    }

    public function sharedWithMe(): JsonResponse
    {
        $ressources = Auth::user()->ressourcesPartagees()->with('tags')->get();
        return response()->json($ressources);
    }

    public function ignoreShare(int $id): JsonResponse
    {
        $ressource = Ressources::findOrFail($id);

        $detached = $ressource->partages()->detach(Auth::id());

        if (!$detached) {
            return response()->json(['message' => 'Cette ressource ne vous a pas été partagée.'], 404);
        }

        return response()->json(null, 204);
    }

    public function duplicateShare(int $id): JsonResponse
    {
        $userId = Auth::id();
        $original = Ressources::with('tags')->findOrFail($id);

        if (!$original->partages()->where('id_utilisateur', $userId)->exists()) {
            return response()->json(['message' => 'Cette ressource ne vous a pas été partagée.'], 404);
        }

        $copie = Ressources::create([
            'type'           => $original->type,
            'url'            => $original->url,
            'nom_original'   => $original->nom_original,
            'image'          => $original->image,
            'resume'         => $original->resume,
            'id_utilisateur' => $userId,
            'id_fluxrss'     => $original->id_fluxrss,
        ]);

        if ($original->tags->isNotEmpty()) {
            $copie->tags()->attach($original->tags->pluck('id_tag'));
        }

        $original->partages()->detach($userId);

        return response()->json($copie->load('tags'), 201);
    }

    public function generateResume(int $id): JsonResponse
    {
        $ressource = Ressources::findOrFail($id);

        if ($ressource->id_utilisateur !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $resume = (new ResumeService())->generate($ressource);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            \Log::error('ResumeService failed', ['error' => $e->getMessage(), 'userId' => Auth::id()]);
            return response()->json(['message' => 'Le service de résumé est temporairement indisponible. Veuillez réessayer dans quelques instants.'], 503);
        }

        $ressource->update(['resume' => $resume]);

        return response()->json(['resume' => $resume]);
    }
}
