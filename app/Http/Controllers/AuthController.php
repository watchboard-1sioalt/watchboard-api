<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($data);

        return $this->respondWithToken(JWTAuth::fromUser($user));
    }

    public function me()
    {
        return response()->json(JWTAuth::user());
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