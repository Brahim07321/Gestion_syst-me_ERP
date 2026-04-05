@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <!-- TITRE -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Création d’un nouvel achat</h2>
        <p class="text-muted mb-0">
            Enregistrez un bon d’achat, ajoutez plusieurs produits et définissez le statut de réception.
        </p>
    </div>

    <!-- ALERTES -->
    @if (session('message'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- FORMULAIRE PRINCIPAL -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Informations de l’achat</h4>
                    <p class="text-muted mb-0">Sélectionnez le fournisseur, la date et les produits à acheter.</p>
                </div>

                <button type="button"
                        class="btn btn-outline-primary rounded-pill px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Nouveau produit
                </button>
            </div>
       {{-- SUCCESS --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ERROR --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
    <i class="fas fa-exclamation-triangle me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>⚠ {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

            <form method="POST" action="{{ route('purchases.store') }}">
                @csrf

                <!-- INFOS ACHAT -->
                <div class="card border-1 bg-light-subtle rounded-4 mb-4">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fournisseur</label>
                                <select name="supplier_id" class="form-select" required>
                                    <option value="">Choisir un fournisseur</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date d’achat</label>
                                <input type="date"
                                       name="purchase_date"
                                       class="form-control"
                                       value="{{ old('purchase_date', date('Y-m-d')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Statut de l’achat</label>
                                <select name="status" class="form-select" required>
                                    <option value="en attente">En attente</option>
                                    <option value="reçu">Reçu</option>
                                    <option value="annulé">Annulé</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE PRODUITS -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive purchase-table">
                            <table class="table align-middle text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>Prix d’achat</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody id="items-list">
                                    <tr>
                                        <td>
                                            <select name="items[0][product_id]" class="form-select" required>
                                                <option value="">Choisir un produit</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->Designation }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input type="number"
                                                   name="items[0][quantity]"
                                                   class="form-control quantity"
                                                   value="1"
                                                   min="1">
                                        </td>

                                        <td>
                                            <input type="number"
                                                   name="items[0][buy_price]"
                                                   class="form-control price"
                                                   step="0.01"
                                                   min="0">
                                        </td>

                                        <td>
                                            <span class="total fw-semibold text-primary">0.00</span>
                                        </td>
                                       

                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 delete-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-end mb-3">
                                <div class="card border-0  " style="min-width: 260px;">
                                    <div class="card-body text-end">
                                        <div class="text-muted small">Total général</div>
                                        <div class="fs-4 fw-bold text-success">
                                            <span id="grand-total">0.00</span> MAD
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <button type="button" id="add-item" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-plus me-2"></i>Ajouter un produit
                    </button>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="fas fa-save me-2"></i>Enregistrer l’achat
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- MODAL AJOUT PRODUIT -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold" id="addProductModalLabel">Ajouter un produit</h5>
                    <p class="text-muted small mb-0">Créez rapidement un nouveau produit depuis cette page.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <form id="productForm" method="POST" action="/product" enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="productCategory" class="form-label fw-semibold">Catégorie</label>
                            <select class="form-select" id="productCategory" name="Category_ID" required>
                                <option value="">-- Choisissez une catégorie --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->Category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="productReferonce" class="form-label fw-semibold">Référence</label>
                            <input type="text" class="form-control" id="productReferonce" name="Referonce" required>
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
                            <input type="number" class="form-control" id="productQuantity" name="Quantite" required>
                        </div>

                        <div class="col-md-4">
                            <label for="productPriceBay" class="form-label fw-semibold">Prix d'achat</label>
                            <input type="number" step="0.01" class="form-control" id="productPriceBay" name="prace_bay" required>
                        </div>

                        <div class="col-md-4">
                            <label for="productPriceSell" class="form-label fw-semibold">Prix de vente</label>
                            <input type="number" step="0.01" class="form-control" id="productPriceSell" name="prace_sell" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Fermer
                    </button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .purchase-table {
        overflow: hidden;
        background: #ffffff;
    }

    .purchase-table table th,
    .purchase-table table td {
        vertical-align: middle;
        white-space: normal;
        word-break: break-word;
    }

    .purchase-table tbody tr {
        transition: all 0.2s ease;
    }

    .purchase-table tbody tr:hover {
        background: #f8fafc;
    }

    .bg-light-subtle {
        background: #f8fafc;
    }
</style>

Ni<script>
    let index = 1;

    function calculateRowTotal(row) {
        const quantityInput = row.querySelector('.quantity');
        const priceInput = row.querySelector('.price');
        const totalSpan = row.querySelector('.total');

        const quantity = parseFloat(quantityInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const total = quantity * price;

        if (totalSpan) {
            totalSpan.textContent = total.toFixed(2);
        }

        return total;
    }

    function calculateGrandTotal() {
        let grandTotal = 0;

        document.querySelectorAll('#items-list tr').forEach(row => {
            grandTotal += calculateRowTotal(row);
        });

        document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    }

    function attachRowEvents(row) {
        const quantityInput = row.querySelector('.quantity');
        const priceInput = row.querySelector('.price');

        if (quantityInput) {
            quantityInput.addEventListener('input', calculateGrandTotal);
        }

        if (priceInput) {
            priceInput.addEventListener('input', calculateGrandTotal);
        }
    }

    document.getElementById('add-item').addEventListener('click', function() {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>
                <select name="items[${index}][product_id]" class="form-select" required>
                    <option value="">Choisir un produit</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->Designation }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${index}][quantity]" class="form-control quantity" value="1" min="1">
            </td>
            <td>
                <input type="number" name="items[${index}][buy_price]" class="form-control price" step="0.01" min="0">
            </td>
            <td>
                <span class="total fw-semibold text-primary">0.00</span>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 delete-item">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        document.getElementById('items-list').appendChild(row);
        attachRowEvents(row);
        calculateGrandTotal();
        index++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-item') || e.target.closest('.delete-item')) {
            const row = e.target.closest('tr');
            if (row) {
                row.remove();
                calculateGrandTotal();
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#items-list tr').forEach(row => {
            attachRowEvents(row);
        });

        calculateGrandTotal();
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection