<?php

namespace App\Http\Controllers;

use App\Models\Utilisateurs;

use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller {

    public function users()
    {
        $utilisateurs = Utilisateurs::select(
            'id_utilisateur', 'nom', 'prenom', 'email', 'admin', 'validation'
        )->get();

        return response()->json($utilisateurs);
    }

    public function user($id)
    {
        $utilisateur = Utilisateurs::select(
            'id_utilisateur', 'nom', 'prenom', 'email', 'admin', 'validation'
        )->findOrFail($id);

        return response()->json($utilisateur);
    }

    public function validate($id)
    {
        $utilisateur = Utilisateurs::findOrFail($id);
        $utilisateur->validation = true;
        $utilisateur->save();

        return response()->json($utilisateur->only(
            'id_utilisateur', 'nom', 'prenom', 'email', 'admin', 'validation'
        ));
    }
}