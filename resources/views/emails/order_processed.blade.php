<!DOCTYPE html>
<html>
<head>
    <title>Votre commande a été traitée</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4CAF50;
            color: #ffffff;
            padding: 10px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .order-details {
            margin-top: 20px;
        }
        .order-details th, .order-details td {
            text-align: left;
            padding: 8px;
        }
        .order-details th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #777777;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Votre commande a été traitée</h1>
    </div>
    <div class="content">
        <p>Cher {{ $order->client->prenom }},</p>
        <p>Nous sommes heureux de vous informer que votre commande a été traitée et sera bientôt livrée.</p>

        <h2>Détails de la commande</h2>
        <table class="order-details">
            <tr>
                <th>ID de la commande</th>
                <td>{{ $order->id }}</td>
            </tr>
            <tr>
                <th>Date de la commande</th>
                <td>{{ $order->date_commande }}</td>
            </tr>
            <tr>
                <th>Statut</th>
                <td>{{ $order->statut }}</td>
            </tr>
            <tr>
                <th>Montant total</th>
                <td>{{ number_format($order->montant_total, 2, ',', ' ') }} FCFA</td>
            </tr>
        </table>

        <h2>Produits commandés</h2>
        <table class="order-details">
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

        <p>Notre service de livraison vous contactera dans les prochaines 48 heures pour la livraison</p>
        <p>Merci pour votre confiance.</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Votre Société. Tous droits réservés.</p>
    </div>
</div>
</body>
</html>

