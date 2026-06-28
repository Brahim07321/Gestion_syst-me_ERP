<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Import Facture AI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        body {
            background-color: #f8f9fa;
        }

        .invoice-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }

        h1 {
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 30px;
            text-align: center;
            letter-spacing: 2px;
        }

        label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <h1>Import Facture par AI</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Importer une image de facture fournisseur. Le système va extraire fournisseur, date, produits, quantités, prix et total.
        </div>

        <form action="{{ route('purchases.import.ai.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="form-label">Image de la facture</label>
                <input type="file" name="invoice_file" class="form-control" accept="image/*" required>
                <small class="text-muted">
                    Formats acceptés: JPG, PNG, WEBP. Max: 5MB.
                </small>
            </div>

            <div class="text-center">
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-lg px-4 me-2">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>

                <button type="submit" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-wand-magic-sparkles"></i> Analyser avec AI
                </button>
            </div>
        </form>
    </div>
</body>
</html>