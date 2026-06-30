<?php

namespace App\Http\Controllers;

use App\Models\Utilisateurs;

use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller {

    public function users()
    {
        $utilisateurs = Utilisateurs::select(
            'id_utilisateur', 'nom', 'prenom', 'email', 'admin', 'validation', 'created_at'
        )->orderByDesc('created_at')->get();

        return response()->json($utilisateurs);
    }

    public function user($id)
    {
        $utilisateur = Utilisateurs::select(
            'id_utilisateur', 'nom', 'prenom', 'email', 'admin', 'validation', 'created_at'
        )->findOrFail($id);

        return response()->json($utilisateur);
    }

    public function validate($id)
    {
        $utilisateur = Utilisateurs::findOrFail($id);
        $utilisateur->validation = true;
        $utilisateur->save();

        return response()->json($utilisateur->only(
            'id_utilisateur', 'nom', 'prenom', 'email', 'admin', 'validation', 'created_at'
        ));
    }

    public function disable($id)
    {
        $utilisateur = Utilisateurs::findOrFail($id);
        $utilisateur->validation = false;
        $utilisateur->save();

        return response()->json($utilisateur->only(
            'id_utilisateur', 'nom', 'prenom', 'email', 'admin', 'validation', 'created_at'
        ));
    }
}