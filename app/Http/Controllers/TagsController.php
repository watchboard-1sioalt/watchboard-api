<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tags;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TagsController extends Controller
{
    private function authUser()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    // Tags publics

    public function public(Request $request)
    {
        return Tags::where('public', true)->get();
    }


    public function listUserTags(Request $request)
    {
        $user = $this->authUser();
        
        $tags = Tags::where('public', true)
                ->orWhere('id_utilisateur', $user->id_utilisateur)
                ->get();

        return $tags;
    }

    public function create(Request $request)
    {
        $user = $this->authUser();

        $request->validate(['tag' => 'required|string|max:255']);

        Tags::create([
            'tag'            => $request->tag,
            'public'         => false,
            'id_utilisateur' => $user->id_utilisateur,
        ]);

        return response()->json(['ok' => true, 'message' => 'Tag créé avec succès!'], 201);
    }

    public function edit(Request $request)
    {
        $user = $this->authUser();

        $request->validate([
            'id_tag' => 'required|integer',
            'tag'    => 'required|string|max:255',
        ]);

        $tag = Tags::where('id_tag', $request->id_tag)
                   ->where('id_utilisateur', $user->id_utilisateur)
                   ->where('public', false)
                   ->first();

        if (!$tag) {
            return response()->json(['ok' => false, 'message' => 'Tag introuvable'], 404);
        }

        $tag->update(['tag' => $request->tag]);

        return response()->json(['ok' => true, 'message' => 'Nom du tag mis à jour avec succès!'], 200);
    }

    public function delete(Request $request)
    {
        $user = $this->authUser();

        $request->validate(['id_tag' => 'required|integer']);

        $tag = Tags::where('id_tag', $request->id_tag)
                   ->where('id_utilisateur', $user->id_utilisateur)
                   ->where('public', false)
                   ->first();

        if (!$tag) {
            return response()->json(['ok' => false, 'message' => 'Tag introuvable'], 404);
        }

        $tag->delete();

        return response()->json(['ok' => true, 'message' => 'Tag supprimé avec succès!'], 200);
    }

    
    // gestion des tags publics par les admin
    public function createPublic(Request $request)
    {
        $user = $this->authUser();

        if (!$user->admin) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $request->validate(['tag' => 'required|string|max:255']);

        Tags::create([
            'tag'    => $request->tag,
            'public' => true,
        ]);

        return response()->json(['ok' => true, 'message' => 'Tag public créé avec succès!'], 201);
    }

    public function editPublic(Request $request)
    {
        $user = $this->authUser();

        if (!$user->admin) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $request->validate([
            'id_tag' => 'required|integer',
            'tag'    => 'required|string|max:255',
        ]);

        $tag = Tags::where('id_tag', $request->id_tag)
                   ->where('public', true)
                   ->first();

        if (!$tag) {
            return response()->json(['ok' => false, 'message' => 'Tag public introuvable'], 404);
        }

        $tag->update(['tag' => $request->tag]);

        return response()->json(['ok' => true, 'message' => 'Tag public mis à jour avec succès!'], 200);
    }

    public function deletePublic(Request $request)
    {
        $user = $this->authUser();

        if (!$user->admin) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $request->validate(['id_tag' => 'required|integer']);

        $tag = Tags::where('id_tag', $request->id_tag)
                   ->where('public', true)
                   ->first();

        if (!$tag) {
            return response()->json(['ok' => false, 'message' => 'Tag public introuvable'], 404);
        }

        $tag->delete();

        return response()->json(['ok' => true, 'message' => 'Tag public supprimé avec succès!'], 200);
    }
}
