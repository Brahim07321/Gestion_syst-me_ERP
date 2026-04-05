<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des mouvements de stock</title>
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

    <div class="title">Historique des mouvements de stock</div>
    <div class="subtitle">Document généré depuis le système ERP</div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Produit</th>
                <th>Type</th>
                <th>Quantité</th>
                <th>Source</th>
                <th>Référence</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $movement->product->Designation ?? '-' }}</td>
                    <td>{{ $movement->type == 'entree' ? 'Entrée' : 'Sortie' }}</td>
                    <td>{{ $movement->quantity }}</td>
                    <td>{{ $movement->source }}</td>
                    <td>{{ $movement->reference }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Aucun mouvement trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Export effectué le {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>