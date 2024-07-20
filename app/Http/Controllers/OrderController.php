<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date_commande' => 'required|date',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantite' => 'required|integer|min:1',
            'products.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $order = Order::create([
                'client_id' => $request->client_id,
                'date_commande' => $request->date_commande,
                'montant_total' => collect($request->products)->sum(function ($product) {
                    return $product['quantite'] * $product['prix_unitaire'];
                }),
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
