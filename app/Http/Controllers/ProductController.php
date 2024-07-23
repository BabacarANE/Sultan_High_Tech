<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function show($id)
    {
        return Product::findOrFail($id);
    }

    public function store(Request $request)
    {
        // Valider les données de la requête
        $request->validate([
            'nom' => 'required|string',
            'description' => 'nullable|string',
            'prix' => 'required|numeric',
            'quantite_en_stock' => 'required|integer',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Initialiser la variable de chemin de la photo
        $photoPath = null;

        // Vérifier si une photo a été téléchargée
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            // Générer un nom unique pour la photo
            $photoName = uniqid() . '_' . $photo->getClientOriginalName();
            // Sauvegarder la photo dans le répertoire 'uploads/photos'
            $photoPath = $photo->storeAs('uploads/photos', $photoName, 'public');
        }

        // Créer le produit en incluant le chemin de la photo s'il y en a une
        $product = Product::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'prix' => $request->prix,
            'quantite_en_stock' => $request->quantite_en_stock,
            'photo' => $photoPath,
            'category_id' => $request->category_id,
        ]);

        return response()->json($product, 201);
    }


    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'nom' => 'required|string',
            'description' => 'nullable|string',
            'prix' => 'required|numeric',
            'quantite_en_stock' => 'required|integer',
            'photo' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product->update($request->all());
        return response()->json($product, 200);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(null, 204);
    }
}
