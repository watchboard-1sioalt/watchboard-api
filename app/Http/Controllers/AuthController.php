<?php

namespace App\Http\Controllers;

use App\Models\Utilisateurs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Identifiant invalide'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:utilisateurs,email',
            'password' => 'required|string|min:8',
        ]);

        $user = Utilisateurs::create($data);

        return $this->respondWithToken(JWTAuth::fromUser($user));
    }

    public function me()
    {
        return response()->json(JWTAuth::user());
    }

    public function updateMe(Request $request)
    {
        $user = JWTAuth::user();

        $data = $request->validate([
            'nom'    => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'email'  => 'sometimes|email|max:255|unique:utilisateurs,email,' . $user->id,
        ]);

        $user->update($data);

        return response()->json($user->fresh());
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = JWTAuth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect'], 422);
        }

        $user->update(['password' => $request->new_password]);

        return response()->json(['message' => 'Mot de passe mis à jour']);
    }

    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    public function logout()
    {
        JWTAuth::invalidate();

        return response()->json(['message' => 'Déconnexion réussie']);
    }

    private function respondWithToken(string $token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ]);
    }
}