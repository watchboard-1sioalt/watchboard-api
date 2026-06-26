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
}