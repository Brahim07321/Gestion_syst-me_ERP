<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Archive des Factures</title>
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

    <div class="title">Archive des factures</div>
    <div class="subtitle">Document généré depuis le système ERP</div>

    <div class="filters">
        <strong>Filtres appliqués :</strong><br>
        Recherche : {{ $search ?: 'Aucune' }} |
        Statut : {{ $status ?: 'Tous' }} |
        Date début : {{ $dateFrom ?: '-' }} |
        Date fin : {{ $dateTo ?: '-' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Code facture</th>
                <th>Client</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Payé</th>
                <th>Reste</th>
                <th>Date facture</th>
            </tr>
        </thead>
        <tbody>
            @forelse($factures as $facture)
                <tr>
                    <td>{{ $facture->id }}</td>
                    <td>{{ $facture->code_facture }}</td>
                    <td>{{ $facture->client_name }}</td>
                    <td>{{ number_format($facture->total, 2) }} MAD</td>
                    <td>{{ $facture->status }}</td>
                    <td>{{ number_format($facture->paid_amount ?? 0, 2) }} MAD</td>
                    <td>{{ number_format($facture->remaining_amount ?? 0, 2) }} MAD</td>
                    <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Aucune facture trouvée.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Export effectué le {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>