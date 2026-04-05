<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des achats</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-bottom: 18px;
        }

        .filters {
            margin-bottom: 15px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: center;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="title">Liste des achats</div>
    <div class="subtitle">Document généré depuis le système ERP</div>

    <div class="filters">
        <strong>Filtres appliqués :</strong><br>
        Recherche : {{ $search ?: 'Aucune' }} |
        Statut : {{ $status ?: 'Tous' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Code achat</th>
                <th>Fournisseur</th>
                <th>Date</th>
                <th>Total</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->id }}</td>
                    <td>{{ $purchase->purchase_code }}</td>
                    <td>{{ $purchase->supplier->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                    <td>{{ number_format($purchase->total, 2) }} MAD</td>
                    <td>{{ $purchase->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Aucun achat trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Export effectué le {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>