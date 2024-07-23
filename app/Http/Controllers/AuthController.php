<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validation des données de la requête
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Rechercher l'utilisateur par email
        $user = User::where('email', $request->email)->first();

        // Vérifier les informations d'identification
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Révoquer les jetons précédents
        $user->tokens()->delete();

        // Récupérer les informations du client
        $client = $user->client ?? null;

        // Créer un nouveau jeton
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        // Retourner une réponse JSON avec le jeton, l'utilisateur et le client
        return response()->json([
            'token' => $token,
            'user' => $user
        ], 200);
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}

