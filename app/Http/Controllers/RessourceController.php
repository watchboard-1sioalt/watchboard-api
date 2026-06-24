<?php

namespace App\Http\Controllers;

use App\Models\Ressources;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RessourceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $ressources = Ressources::all();
        return response()->json($ressources);
    }

    public function create(): JsonResponse
    {
        return response()->json(['message' => 'formulaire création']);
    }

    public function store(Request $request): JsonResponse
    {
        // TODO: valider et créer la ressource
        return response()->json(['message' => 'ressource créée'], 201);
    }

    public function storeFromRss(Request $request): JsonResponse
    {
        // TODO: sauvegarder un article RSS
        return response()->json(['message' => 'article RSS sauvegardé'], 201);
    }

    public function show(int $id): JsonResponse
    {
        $ressource = Ressources::findOrFail($id);
        return response()->json($ressource);
    }

    public function edit(int $id): JsonResponse
    {
        $ressource = Ressources::findOrFail($id);
        return response()->json($ressource);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $ressource = Ressources::findOrFail($id);
        // TODO: valider et mettre à jour
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
}
