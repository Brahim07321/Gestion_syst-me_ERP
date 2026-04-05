@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">

        <!-- TITRE -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

            <h2 class="fw-bold mb-1">Gestion des produits</h2>
        
            <div class="ms-md-auto">
                <div class="dropdown">
        
                    <button class="btn btn-light rounded-pill px-3 shadow-sm" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
        
                    <ul class="dropdown-menu dropdown-menu-end p-2 border-0 shadow rounded-4">
        
                        <li>
                            <a href="{{ route('products.template') }}"
                                class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                                <i class="fas fa-file-download text-dark"></i>
                                Télécharger le modèle Excel
                            </a>
                        </li>
        
                        <li>
                            <a href="{{ route('stock.export', request()->query()) }}"
                                class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                                <i class="fas fa-file-excel text-success"></i>
                                Exporter Excel
                            </a>
                        </li>
        
                        <li>
                            <button type="button"
                                class="dropdown-item rounded-3 d-flex align-items-center gap-2"
                                data-bs-toggle="modal" data-bs-target="#importExcelModal">
                                <i class="fas fa-file-import text-primary"></i>
                                Importer Excel
                            </button>
                        </li>
        
                        <li>
                            <hr class="dropdown-divider">
                        </li>
        
                        <li>
                            <button type="button" class="dropdown-item rounded-3 d-flex align-items-center gap-2"
                                data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddModal()">
                                <i class="fas fa-plus text-primary"></i>
                                Ajouter un produit
                            </button>
                        </li>
        
                    </ul>
        
                </div>
            </div>
        
        </div>






        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <p class="text-muted mb-0">
                Ajoutez, importez, exportez et gérez facilement l’ensemble de vos produits.
            </p>
        </div>

        <!-- ALERTES -->

        @if (session('success'))
            <div class="alert border-0 rounded-4 shadow-sm d-flex align-items-center justify-content-between px-3 py-3 mb-4"
                style="background: #ecfdf5; color: #065f46;">

                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-check-circle fs-5"></i>
                    <span class="fw-semibold">{{ session('success') }}</span>
                </div>

                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            </div>
        @endif


        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center gap-2">
                <i class="fas fa-times-circle"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                <div>{{ session('warning') }}</div>
            </div>
        @endif
        <!-- CONTENEUR PRINCIPAL -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <!-- HEADER -->
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-start gap-4 mb-4">

                    <!-- ACTIONS -->


                </div>

                <!-- RECHERCHE -->
                <div class="search-box w-100" style="max-width: auto;">
                    <form action="{{ url('/product') }}" method="GET">
                        <label class="form-label fw-semibold">Recherche</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                placeholder="Rechercher par désignation, référence ou code..."
                                value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <!-- TABLEAU -->
            <div class="table-responsive products-table">
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
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td class="fw-semibold">{{ $product->id }}</td>
                                <td class="fw-semibold text-primary">{{ $product->Referonce }}</td>
                                <td>{{ $product->code }}</td>
                                <td>{{ $product->category->Category ?? ($product->category->category ?? 'Non définie') }}
                                </td>
                                <td>{{ $product->Designation }}</td>
                                <td>{{ number_format($product->prace_bay, 2) }} DH</td>
                                <td>{{ number_format($product->prace_sell, 2) }} DH</td>
                                <td class="product-quantity text-center">
                                    <span class="fw-semibold">{{ $product->Quantite }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3"
                                            data-bs-toggle="modal" data-bs-target="#productModal"
                                            onclick="openEditModal(
                                                    '{{ $product->id }}',
                                                    '{{ $product->Category_ID }}',
                                                    '{{ $product->Referonce }}',
                                                    '{{ $product->Designation }}',
                                                    '{{ $product->code }}',
                                                    '{{ $product->Quantite }}',
                                                    '{{ $product->prace_bay }}',
                                                    '{{ $product->prace_sell }}'
                                                )">
                                            <i class="fas fa-pen me-1"></i>Modifier
                                        </button>
                                        @if (Auth::user()->role === 'admin')
                                            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3"
                                                data-bs-toggle="modal" data-bs-target="#deleteProductModal"
                                                onclick="setDeleteProduct('{{ $product->id }}', '{{ $product->Designation }}')">
                                                <i class="fas fa-trash me-1"></i>Supprimer
                                            </button>
                                        @endif


                                    </div>
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
                <!-- IMPORT -->
            </div>

            <!-- PAGINATION -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $products->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    </div>

    <!-- imort PRODUIT -->

    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content border-0 rounded-4 shadow">
    
                <div class="modal-header bg-white border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="importExcelModalLabel">Importer des produits</h5>
                        <p class="text-muted small mb-0">Sélectionnez un fichier Excel pour importer vos produits.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
    
                <form id="importForm" action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
    
                    <div class="modal-body pt-3">
                        <div class="mb-3">
                            <label for="importFile" class="form-label fw-semibold">Fichier Excel</label>
                            <input type="file" name="file" id="importFile" class="form-control" accept=".xlsx,.xls" required>
                        </div>
    
                        <div class="progress mb-2" style="height: 10px;">
                            <div id="progressBar" class="progress-bar" style="width: 0%">0%</div>
                        </div>
    
                        <div class="alert alert-light border rounded-4 mb-0">
                            <i class="fas fa-circle-info me-2 text-primary"></i>
                            Veuillez utiliser le modèle Excel pour éviter les erreurs d’importation.
                        </div>
                    </div>
    
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Fermer
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-file-import me-2"></i>Importer
                        </button>
                    </div>
                </form>
    
            </div>
        </div>
    </div>

    <!-- MODAL PRODUIT -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-header bg-white border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="productModalLabel">Ajouter un produit</h5>
                        <p class="text-muted small mb-0">Renseignez les informations du produit.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="productForm" method="POST" action="{{ route('product.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="form_mode" id="formMode" value="add">

                    <div class="modal-body pt-3">
                        @if ($errors->has('Referonce') && old('form_mode') === 'add')
                            <div class="alert alert-danger">
                                {{ $errors->first('Referonce') }}
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="productCategory" class="form-label fw-semibold">Catégorie</label>
                                <select class="form-select" id="productCategory" name="Category_ID" required>
                                    <option value="">-- Choisissez une catégorie --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->Category ?? $category->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="productReferonce" class="form-label fw-semibold">Référence</label>
                                <input type="text" class="form-control @error('Referonce') is-invalid @enderror"
                                    id="productReferonce" name="Referonce" value="{{ old('Referonce') }}" required>
                                @if ($errors->has('Referonce') && old('form_mode') === 'add')
                                    <div class="invalid-feedback d-block">
                                        {{ $errors->first('Referonce') }}
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="productName" class="form-label fw-semibold">Désignation</label>
                                <input type="text" class="form-control" id="productName" name="Designation" required>
                            </div>

                            <div class="col-md-6">
                                <label for="productCode" class="form-label fw-semibold">Code</label>
                                <input type="text" class="form-control" id="productCode" name="code" required>
                            </div>

                            <div class="col-md-4">
                                <label for="productQuantity" class="form-label fw-semibold">Quantité</label>
                                <input type="number" class="form-control" id="productQuantity" name="Quantite"
                                    required>
                            </div>

                            <div class="col-md-4">
                                <label for="productPriceBay" class="form-label fw-semibold">Prix d'achat</label>
                                <input type="number" step="0.01" class="form-control" id="productPriceBay"
                                    name="prace_bay" required>
                            </div>

                            <div class="col-md-4">
                                <label for="productPriceSell" class="form-label fw-semibold">Prix de vente</label>
                                <input type="number" step="0.01" class="form-control" id="productPriceSell"
                                    name="prace_sell" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Fermer
                        </button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" id="submitBtn">
                            Enregistrer
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-trash text-danger fs-1"></i>
                    </div>

                    <h5 class="fw-bold mb-2">Confirmation de suppression</h5>

                    <p id="deleteProductText" class="text-muted mb-0">
                        Voulez-vous vraiment supprimer ce produit ?
                    </p>

                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Annuler
                        </button>

                        <form id="deleteProductForm" method="POST">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger rounded-pill px-4">
                                Oui, supprimer
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .products-table {
            overflow: hidden;
            background: #ffffff;
        }

        .products-table table th,
        .products-table table td {
            vertical-align: middle;
            white-space: normal;
            word-break: break-word;
        }

        .products-table tbody tr {
            transition: all 0.2s ease;
        }

        .products-table tbody tr:hover {
            background: #f8fafc;
        }

        .bg-light-subtle {
            background: #f8fafc;
        }

        .low-quantity-cell {
            background-color: #ffcccc !important;
            color: #ff0000 !important;
            font-weight: bold;
        }



        .dropdown-menu {
            z-index: 10000;
            animation: fadeIn 0.2s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        function openAddModal() {
            document.getElementById('productModalLabel').textContent = 'Ajouter un produit';
            document.getElementById('submitBtn').textContent = 'Enregistrer';
            document.getElementById('formMode').value = 'add';

            const form = document.getElementById('productForm');
            form.action = "{{ route('product.store') }}";

            document.getElementById('formMethod').value = 'POST';
            document.getElementById('productCategory').value = '';
            document.getElementById('productReferonce').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productCode').value = '';
            document.getElementById('productQuantity').value = '';
            document.getElementById('productPriceBay').value = '';
            document.getElementById('productPriceSell').value = '';
        }

        function openEditModal(id, categoryId, referonce, designation, code, quantite, priceBay, priceSell) {
            const errorAlert = document.querySelector('#productModal .alert-danger');
            if (errorAlert) {
                errorAlert.remove();
            }

            const referonceInput = document.getElementById('productReferonce');
            referonceInput.classList.remove('is-invalid');

            const feedback = document.querySelector('#productModal .invalid-feedback');
            if (feedback) {
                feedback.remove();
            }

            document.getElementById('formMode').value = 'edit';
            document.getElementById('productModalLabel').textContent = 'Modifier le produit';
            document.getElementById('submitBtn').textContent = 'Mettre à jour';

            const form = document.getElementById('productForm');
            form.action = `/product/${id}`;

            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('productCategory').value = categoryId;
            document.getElementById('productReferonce').value = referonce;
            document.getElementById('productName').value = designation;
            document.getElementById('productCode').value = code;
            document.getElementById('productQuantity').value = quantite;
            document.getElementById('productPriceBay').value = priceBay;
            document.getElementById('productPriceSell').value = priceSell;
        }

        document.getElementById('importForm').addEventListener('submit', function() {
            let bar = document.getElementById('progressBar');
            let width = 0;

            let interval = setInterval(() => {
                if (width >= 90) {
                    clearInterval(interval);
                } else {
                    width += 10;
                    bar.style.width = width + "%";
                    bar.innerHTML = width + "%";
                }
            }, 100);
        });

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                el.style.transition = "0.5s";
                el.style.opacity = "0";
                setTimeout(() => el.remove(), 500);
            });
        }, 4000);

        // for dellet 
        function setDeleteProduct(id, name) {
            document.getElementById('deleteProductForm').action = '/product/' + id;
            document.getElementById('deleteProductText').innerText =
                'Voulez-vous vraiment supprimer le produit : ' + name + ' ?';
        }
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                el.style.transition = "0.4s";
                el.style.opacity = "0";
                setTimeout(() => el.remove(), 400);
            });
        }, 3000);

        document.getElementById('importForm').addEventListener('submit', function () {
        let bar = document.getElementById('progressBar');
        let width = 0;

        let interval = setInterval(() => {
            if (width >= 90) {
                clearInterval(interval);
            } else {
                width += 10;
                bar.style.width = width + "%";
                bar.innerHTML = width + "%";
            }
        }, 100);
    });

    </script>

@if ($errors->has('file'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const importModal = new bootstrap.Modal(document.getElementById('importExcelModal'));
        importModal.show();
    });
</script>
@endif

    @if ($errors->has('Referonce') && old('form_mode') === 'add')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                openAddModal();
                const productModal = new bootstrap.Modal(document.getElementById('productModal'));
                productModal.show();
            });
        </script>
    @endif
@endsection
