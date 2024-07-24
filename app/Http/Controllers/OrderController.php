<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class OrderController extends Controller
{
    public function index()
    {
        return Order::with('products')->get();
    }

    public function show($id)
    {
        return Order::with('products')->findOrFail($id);
    }

    public function store(Request $request)
    {
        // Validation des données de la requête
        $request->validate([
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantite' => 'required|integer|min:1',
            'products.*.prix_unitaire' => 'required|numeric|min:0',
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'numero_telephone' => 'required|string|max:20',
            'adresse' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
        ]);

        // Vérification de l'existence de l'utilisateur
        $user = User::where('email', $request->email)->first();

        // Si l'utilisateur n'existe pas, créer un nouveau client
        if (!$user) {
            $clientController = new ClientController();
            $clientRequest = new Request($request->only(['prenom', 'nom', 'email', 'numero_telephone', 'adresse', 'sexe']));
            $clientRequest->merge(['photo' => null]);

            $clientController->store($clientRequest);
            $user = User::where('email', $request->email)->first();

        }

        // Créer la commande et les produits associés dans une transaction
        DB::transaction(function () use ($request, $user) {
            $order = Order::create([
                'client_id' => $user->client->id,
                'date_commande' => Carbon::now()->toDateTimeString(), // Utilisation de la date système
                'montant_total' => collect($request->products)->sum(function ($product) {
                    return $product['quantite'] * $product['prix_unitaire'];
                })
            ]);

            foreach ($request->products as $product) {
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product['product_id'],
                    'quantite' => $product['quantite'],
                    'prix_unitaire' => $product['prix_unitaire'],
                ]);
            }
        });

        return response()->json(['message' => 'Order created successfully'], 201);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date_commande' => 'required|date',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantite' => 'required|integer|min:1',
            'products.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $id) {
            $order = Order::findOrFail($id);

            $order->update([
                'client_id' => $request->client_id,
                'date_commande' => $request->date_commande,
                'montant_total' => collect($request->products)->sum(function ($product) {
                    return $product['quantite'] * $product['prix_unitaire'];
                }),
            ]);

            OrderProduct::where('order_id', $id)->delete();

            foreach ($request->products as $product) {
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product['product_id'],
                    'quantite' => $product['quantite'],
                    'prix_unitaire' => $product['prix_unitaire'],
                ]);
            }
        });

        return response()->json(['message' => 'Order updated successfully'], 200);
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            OrderProduct::where('order_id', $id)->delete();
            $order = Order::findOrFail($id);
            $order->delete();
        });

        return response()->json(['message' => 'Order deleted successfully'], 204);
    }
}
