<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboardStats()
    {
        // Le nombre de clients dans le système
        $totalClients = Client::count();

        // Le nombre de clients par sexe
        $clientsByGender = Client::select('sexe', DB::raw('count(*) as count'))
            ->groupBy('sexe')
            ->get();

        // Le nombre de produits dans le système
        $totalProducts = Product::count();

        // Le montant total des commandes dont le statut est "livré"
        $totalAmountDeliveredOrders = Order::where('statut', 'livré')->sum('montant_total');

        // Le montant total des produits en stock (prix_unitaire * quantite_en_stock)
        $totalStockValue = Product::sum(DB::raw('prix * quantite_en_stock'));

        // Le nombre de produits par catégorie
        $productsByCategory = Product::select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->get();

        return response()->json([
            'totalClients' => $totalClients,
            'clientsByGender' => $clientsByGender,
            'totalProducts' => $totalProducts,
            'totalAmountDeliveredOrders' => $totalAmountDeliveredOrders,
            'totalStockValue' => $totalStockValue,
            'productsByCategory' => $productsByCategory,
        ]);
    }
}
