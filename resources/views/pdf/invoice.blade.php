<!DOCTYPE html>
<html>
<head>
    <title>Facture</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 20px;
        }
        .order-details, .product-details {
            width: 100%;
            border-collapse: collapse;
        }
        .order-details th, .order-details td, .product-details th, .product-details td {
            border: 1px solid #dddddd;
            padding: 8px;
        }
        .order-details th, .product-details th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Sultan High Tech</h2>
    </div>
    <div class="header">
        <h2>Facture</h2>
    </div>
    <div class="content">
        <h2>à {{ $order->client->prenom ." ". $order->client->nom }}</h2>

        <h2>Détails de la commande</h2>
        <table class="order-details">

            <tr>
                <th>Date de la commande</th>
                <td>{{ $order->date_commande }}</td>
            </tr>

            <tr>
                <th>Montant total</th>
                <td>{{ number_format($order->montant_total, 2, ',', ' ') }} FCFA</td>
            </tr>
        </table>

        <h2>Produits commandés</h2>
        <table class="product-details">
            <thead>
            <tr>
                <th>Produit</th>
                <th>Description</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Prix total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->products as $product)
                <tr>
                    <td>{{ $product->nom }}</td>
                    <td>{{ $product->description }}</td>
                    <td>{{ $product->pivot->quantite }}</td>
                    <td>{{ number_format($product->pivot->prix_unitaire, 2, ',', ' ') }} FCFA</td>
                    <td>{{ number_format($product->pivot->prix_unitaire * $product->pivot->quantite, 2, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="footer">
        <p>Merci pour votre achat!</p>
    </div>
</div>
</body>
</html>
