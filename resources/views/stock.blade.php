@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">

        <!-- TITRE -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <h2 class="fw-bold mb-1">Gestion du stock</h2>

            <a href="{{ route('stock.export', request()->query()) }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-file-export me-2"></i>Exporter Excel
            </a>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <p class="text-muted mb-0">
                Suivez les quantités disponibles, filtrez les produits et identifiez rapidement les stocks faibles.
            </p>
        </div>
        <!-- CONTENU PRINCIPAL -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <!-- HEADER -->
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-3 mb-4">

                    <div>
                        <h4 class="fw-bold mb-1">Contrôle des quantités</h4>
                        <p class="text-muted mb-0">Visualisez l’état actuel du stock de tous vos produits.</p>
                    </div>


                </div>

                <!-- FILTRES -->
                <div class="card border-1 bg-light-subtle rounded-4 mb-4">
                    <div class="card-body p-3">
                        <form action="{{ route('stock.index') }}" method="GET" class="row g-3 align-items-end">

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Recherche</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Rechercher un produit..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Catégorie</label>
                                <select name="category_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Toutes les catégories</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->Category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">État du stock</label>
                                <select name="low_stock" class="form-select" onchange="this.form.submit()">
                                    <option value="">Tous les niveaux</option>
                                    <option value="1" {{ request('low_stock') == '1' ? 'selected' : '' }}>
                                        Stock faible (&lt; 5)
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold d-block invisible">Reset</label>
                                <a href="{{ route('stock.index') }}" class="btn btn-secondary w-100 rounded-pill">
                                    Réinitialiser
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ALERTE LOW STOCK -->
                @if ($lowStockProducts->count() > 0)
                    <div class="alert alert-warning border-0 shadow-sm rounded-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Produits en stock faible</strong>
                        </div>

                        <ul class="mb-0 ps-3">
                            @foreach ($lowStockProducts as $product)
                                <li>{{ $product->Designation }} — reste {{ $product->Quantite }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- TABLEAU -->
                <div class="table-responsive stock-table">
                    <table id="productTable" class="table align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Référence</th>
                                <th>Code</th>
                                <th>Catégorie</th>
                                <th>Désignation</th>
                                <th>Prix d'achat</th>
                                <th>Prix de vente</th>
                                <th>Quantité</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td class="fw-semibold">{{ $product->id }}</td>
                                    <td class="fw-semibold text-primary">{{ $product->Referonce }}</td>
                                    <td>{{ $product->code }}</td>
                                    <td>{{ $product->category_name ?? '-' }}</td>
                                    <td>{{ $product->Designation }}</td>
                                    <td>{{ number_format($product->prace_bay, 2) }} DH</td>
                                    <td>{{ number_format($product->prace_sell, 2) }} DH</td>
                                    <td class="product-quantity fw-semibold">
                                        {{ $product->Quantite }}
                                    </td>
                                    <td>
                                        <a href="/product" class="btn btn-primary btn-sm rounded-pill px-3">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        Aucun produit trouvé.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

    <style>
        .stock-table {
            overflow: hidden;
            background: #ffffff;
        }

        .stock-table table th,
        .stock-table table td {
            vertical-align: middle;
            white-space: normal;
            word-break: break-word;
        }

        .stock-table tbody tr {
            transition: all 0.2s ease;
        }

        .stock-table tbody tr:hover {
            background: #f8fafc;
        }

        .bg-light-subtle {
            background: #f8fafc;
        }

        .low-quantity-cell {
            background-color: #fee2e2 !important;
            color: #dc2626 !important;
            font-weight: bold;
            border-radius: 8px;
            padding: 6px 10px;
            display: inline-block;
            min-width: 45px;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tableBody = document.querySelector("#productTable tbody");

            if (!tableBody) return;

            const rows = Array.from(tableBody.querySelectorAll("tr"));
            const lowQuantityRows = [];

            rows.forEach(row => {
                const quantityCell = row.querySelector(".product-quantity");
                if (!quantityCell) return;

                const quantity = parseInt(quantityCell.textContent.trim(), 10);

                if (!isNaN(quantity) && quantity < 5) {
                    quantityCell.classList.add("low-quantity-cell");
                    lowQuantityRows.push(row);
                }
            });

            lowQuantityRows.forEach(row => {
                tableBody.prepend(row);
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
