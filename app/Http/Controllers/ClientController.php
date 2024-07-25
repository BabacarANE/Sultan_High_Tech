<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index()
    {
        return Client::all();
    }

    public function show($id)
    {
        return Client::findOrFail($id);
    }

    public function store(Request $request)
    {
        // Validation des données de la requête
        $request->validate([
            'photo' => 'nullable|string',
            'password' => 'nullable|string',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'adresse' => 'required|string',
            'numero_telephone' => 'required|string',
            'email' => 'required|email|unique:users',
            'sexe' => 'required|in:M,F',
        ]);

        if($request->password==null){
            $password="passer123";
        }else{
            $password=$request->password;
        }
        // Création de l'utilisateur
        $user = User::create([
            'photo' => $request->photo,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role_id' => 2,
        ]);

        // Création du client
        $client = Client::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'adresse' => $request->adresse,
            'numero_telephone' => $request->numero_telephone,
            'sexe' => $request->sexe,
            'user_id' => $user->id,
        ]);

        // Retourner une réponse JSON avec les deux objets
        return response()->json([
            'user' => $user,
            'client' => $client
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'adresse' => 'required|string',
            'numero_telephone' => 'required|string',
            'sexe' => 'required|in:M,F',
            'user_id' => 'required|exists:users,id',
        ]);

        $client->update($request->all());
        return response()->json($client, 200);
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        return response()->json(null, 204);
    }
}

