<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDelivery;
use App\Mail\OrderProcessedMail;
use App\Models\Client;
use App\Models\DetailOrder;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index()
    {
        return Order::with(['products', 'detailOrder'])->get();
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
    public function generateDeliveryNotePDF($orderId)
    {
        $order = Order::with('client', 'products')->findOrFail($orderId);
        $pdf = PDF::loadView('pdf.delivery_note', compact('order'));

        // Save the PDF to storage
        $filePath = 'delivery_notes/bon_de_livraison_' . $order->id . '.pdf';
        Storage::put($filePath, $pdf->output());

        return $filePath;
    }

    public function generateInvoicePDF($orderId)
    {
        $order = Order::with('client', 'products')->findOrFail($orderId);
        $pdf = PDF::loadView('pdf.invoice', compact('order'));

        // Save the PDF to storage
        $filePath = 'invoices/facture_' . $order->id . '.pdf';
        Storage::put($filePath, $pdf->output());

        return $filePath;
    }
    public function validateOrder($id)
    {
        Log::info('Début de la validation de la commande: ' . $id);

        $order = Order::findOrFail($id);
        Log::info('Commande trouvée: ' . $order->id);

        $order->statut = 'validé';
        $order->save();
        Log::info('Statut de la commande mis à jour');
        // Generate and save PDFs
        $deliveryNotePath = $this->generateDeliveryNotePDF($order->id);
        $invoicePath = $this->generateInvoicePDF($order->id);

        // Save the file paths in the detail_orders table
        DetailOrder::create([
            'order_id' => $order->id,
            'delivery_note_path' => $deliveryNotePath,
            'invoice_path' => $invoicePath,
        ]);

        ///send email
        try {
            Mail::to($order->client->user->email)->send(new OrderProcessedMail($order));
            Log::info('Email envoyé à: ' . $order->client->email);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'envoi de l\'email'], 500);
        }

        ProcessDelivery::dispatch($order);
        Log::info('Job de livraison dispatché');

        return response()->json(['message' => 'Commande validée et notification envoyée.']);
    }
    public function markAsDelivered($id)
    {
        Log::info('Début de la livraison de la commande: ' . $id);

        // Retrieve the order
        $order = Order::with('products')->findOrFail($id);

        // Update the order status to 'delivered'
        $order->statut = 'livré';
        $order->save();
        Log::info('Statut de la commande mis à jour');

        // Update stock quantities
        foreach ($order->products as $product) {
            $productModel = Product::find($product->id);
            if ($productModel) {
                $newStock = $productModel->quantite_en_stock - $product->pivot->quantite;
                $productModel->quantite_en_stock = $newStock;
                $productModel->save();
            }
        }
        Log::info('Quantités de stock mises à jour');

        return response()->json(['message' => 'Commande marquée comme livrée et stock mis à jour.']);
    }
    public function getClientOrders($userId)
    {
        $user=User::findOrFail($userId);
        $client = Client::where('user_id', $userId)->first();
        $clientId=$client->id;

        $orders = Order::where('client_id', $clientId)
            ->with('products')  // Charge les produits associés à chaque commande
            ->orderBy('date_commande', 'desc')
            ->get();

        return response()->json($orders);
    }
}
