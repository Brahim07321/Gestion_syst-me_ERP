@extends('layout')
@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <!-- Main Content -->
    <div class="main-content  main-content-expanded" id="mainContent">
        <h2 class="page-title">Contrôle des Quantités</h2>

        <div class="table-container">
            <div class="table-header">
                <div>
                    
                    <a href="{{ route('stock.export', request()->query()) }}" 
                        class="btn btn-export text-white btn-success  btn-sm">
                         <i class="fas fa-file-export"></i> Exporter
                     </a>
                </div>
                <form action="{{ route('stock.index') }}" method="GET"
                    class="d-flex gap-2 align-items-center mb-3 flex-wrap mt-2">
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search produit..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary btn-sm" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <select name="category_id" class="form-select form-select-sm"
                        style="width: 220px; color: #000; background: #fff;" onchange="this.form.submit()">
                        <option value="">Toutes les catégories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->Category }}
                            </option>
                        @endforeach
                    </select>

                    <select name="low_stock" class="form-select form-select-sm"
                        style="width: 180px; color: #000; background: #fff;" onchange="this.form.submit()">
                        <option value="">Tous les stocks</option>
                        <option value="1" {{ request('low_stock') == '1' ? 'selected' : '' }}>
                            Low stock (&lt; 5)
                        </option>
                    </select>

                    <a href="{{ route('stock.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                </form>
            </div>


            <table id="productTable" class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Référence</th>
                        <th>Code</th>
                        <th>Catégorie</th>
                        <th>Désignation</th>
                        <th>Prix Achat</th>
                        <th>Prix Vente</th>
                        <th class="text-center">Quantité</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->Referonce }}</td>

                            <td>{{ $product->code }}</td>
                            <td>{{ $product->category_name ?? '-' }}</td>
                            <td>{{ $product->Designation }}</td>
                            <td>{{ number_format($product->prace_bay, 2) }}</td>
                            <td>{{ number_format($product->prace_sell, 2) }}</td>
                            <td class="product-quantity">
                                {{ $product->Quantite }}

                                @if ($product->Quantite < 5)
                                @endif
                            </td>
                            <td>
                                <a href="/product" class="btn btn-primary btn-sm">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Aucun produit trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $products->links() }}
            </div>



            <style>
                .low-quantity-cell {
                    background-color: #ffcccc !important;
                    /* Rouge clair pour l'arrière-plan */
                    color: #ff0000 !important;
                    /* Rouge vif pour le texte */
                    font-weight: bold;
                }
            </style>

            <!-- JavaScript corrigé -->
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const tableBody = document.querySelector("#productTable tbody");

                    if (!tableBody) {
                        console.error("Table introuvable !");
                        return;
                    }

                    const rows = Array.from(tableBody.querySelectorAll("tr"));
                    const lowQuantityRows = [];

                    rows.forEach(row => {
                        const quantityCell = row.querySelector(".product-quantity");

                        if (!quantityCell) {
                            return;
                        }

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
            <div class="pagination-info">
                <div>
                    Affichage de 1 à 6 sur 6 entrées
                </div>
                <div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Précédent</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Suivant</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for modifer Product Form -->

    </div>

    </div>
    </div>
    </div>


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
