@extends('layout')
@section('content')

    <!-- Main Content -->
    <div class="main-content  main-content-expanded" id="mainContent">
        <h2 class="page-title">Liste des Produits</h2>

        <div class="table-container">
            <div class="table-header">
                <div>
                    <button type="button" class="btn btn-add btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#addProductModal">
                        <i class="fa-solid fa-plus"></i> Ajouter un Produit
                    </button>
                    <button class="btn btn-export text-white btn-sm">
                        <i class="fas fa-file-export"></i> Exporter
                    </button>
                </div>
                <form action="{{ url('/product') }}" method="GET" class="search-box">
                    <div class="input-group mt-2">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search by designation, reference or code..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary btn-sm" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <!--message-->
            @if (session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

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
                    @foreach ($products as $product)
                        @if ($product->Quantite > 0)
                            <!-- Affiche uniquement si la quantité est supérieure à 0 -->
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->Referonce }}</td>
                                <td>{{ $product->code }}</td>
                                <td>{{ $product->category->Category ?? 'Non défini' }}</td>
                                <td>{{ $product->Designation }}</td>
                                <td>{{ $product->prace_bay }}DH</td>
                                <td>{{ $product->prace_sell }}DH</td>
                                <td class="product-quantity text-center">{{ $product->Quantite }}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm action-btn">Modifier</button>
                                    <button class="btn btn-danger btn-sm action-btn">Supprimer</button>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>

            </table>
            <div class="mt-3">
                {{ $products->appends(['search' => request('search')])->links() }}
            </div>


            <!-- CSS personnalisé -->
            <style>
                .low-quantity-cell {
                    background-color: #ffcccc !important;
                    /* Rouge clair pour l'arrière-plan */
                    color: #ff0000 !important;
                    /* Rouge vif pour le texte */
                    font-weight: bold;
                }
            </style>




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

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Modal for Add Product Form -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-green">
                    <h5 class="modal-title" id="addProductModalLabel">Ajouter un Produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="productForm" method="POST" action="/product" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <div class="mb-3">
                            <label for="productCategory" class="form-label">Catégorie</label>
                            <select class="form-select" id="productCategory" name="Category_ID" required>
                                <option value="">-- Choisissez une Catégorie --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->Category }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="mb-3">
                            <label for="productReferonce" class="form-label">Référence</label>
                            <input type="text" class="form-control" id="productReferonce" name="Referonce" required>
                        </div>
                        <div class="mb-3">
                            <label for="productName" class="form-label">Désignation</label>
                            <input type="text" class="form-control" id="productName" name="Designation" required>
                        </div>
                        <div class="mb-3">
                            <label for="productCode" class="form-label">Code</label>
                            <input type="text" class="form-control" id="productCode" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="productQuantity" class="form-label">Quantité</label>
                            <input type="number" class="form-control" id="productQuantity" name="Quantite" required>
                        </div>
                        <div class="mb-3">
                            <label for="productPriceBay" class="form-label">Prix d'achat</label>
                            <input type="number" step="0.01" class="form-control" id="productPriceBay"
                                name="prace_bay" required>
                        </div>
                        <div class="mb-3">
                            <label for="productPriceSell" class="form-label">Prix de vente</label>
                            <input type="number" step="0.01" class="form-control" id="productPriceSell"
                                name="prace_sell" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Bootstrap JS and dependencies -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    @endsection
