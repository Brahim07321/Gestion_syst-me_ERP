<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des clients</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="title">Liste des clients</div>
    <div class="subtitle">Document généré depuis le système ERP</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom complet</th>
                <th>Adresse</th>
                <th>Téléphone</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->address }}</td>
                    <td>{{ $customer->contact }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Aucun client trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Export effectué le {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>