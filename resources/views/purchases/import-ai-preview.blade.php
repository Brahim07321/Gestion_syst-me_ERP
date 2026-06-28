@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <!-- TITRE -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Vérification de la facture AI</h2>
        <p class="text-muted mb-0">
            Vérifiez les données détectées par AI avant d’enregistrer l’achat.
        </p>
    </div>

    <!-- ALERTES -->
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>⚠ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('success') }}
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
                    <h4 class="fw-bold mb-1">Résultat de lecture AI</h4>
                    <p class="text-muted mb-0">
                        Si le produit existe déjà, il sera utilisé automatiquement. Sinon, choisissez seulement sa catégorie.
                    </p>
                </div>

                <a href="{{ route('purchases.import.ai.create') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            <div class="alert alert-warning border-0 shadow-sm rounded-4">
                <i class="fas fa-triangle-exclamation me-2"></i>
                Vérifie les quantités et les prix avant confirmation. AI peut se tromper.
            </div>

{{-- for add product  --}}

            <datalist id="products-list">
                @foreach($products as $product)
                    <option value="{{ $product->Referonce }} - {{ $product->Designation }}"
                            data-id="{{ $product->id }}"
                            data-reference="{{ $product->Referonce }}"
                            data-designation="{{ $product->Designation }}"
                            data-price="{{ $product->prace_bay }}">
                    </option>
                @endforeach
            </datalist>

            

            <form method="POST" action="{{ route('purchases.import.ai.confirm') }}" id="confirm-form">
                @csrf

                <!-- INFOS FACTURE -->
                <div class="card border-1 bg-light-subtle rounded-4 mb-4">
                    <div class="card-body p-3">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Fournisseur</label>
                                <input type="text"
                                       name="supplier_name"
                                       class="form-control"
                                       value="{{ old('supplier_name', $data['supplier_name'] ?? '') }}"
                                       placeholder="Nom fournisseur">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">N° Facture / Achat</label>
                                <input type="text"
                                       name="invoice_number"
                                       class="form-control"
                                       value="{{ old('invoice_number', $data['invoice_number'] ?? '') }}"
                                       placeholder="ACH-...">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Date facture</label>
                                <input type="date"
                                       name="invoice_date"
                                       class="form-control"
                                       value="{{ old('invoice_date', $data['invoice_date'] ?? date('Y-m-d')) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Date d’échéance</label>
                                <input type="date"
                                       name="due_date"
                                       class="form-control"
                                       value="{{ old('due_date', $data['due_date'] ?? '') }}">
                            </div>

                        </div>
                    </div>
                </div>

                <!-- TABLE PRODUITS -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive purchase-table">
                            <table class="table align-middle text-center mb-0" id="items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 24%;">Statut / Catégorie</th>
                                        <th style="width: 13%;">Référence</th>
                                        <th style="width: 20%;">Désignation</th>
                                        <th style="width: 8%;">Quantité</th>
                                        <th style="width: 10%;">Prix d’achat</th>
                                        <th style="width: 10%;">Total</th>
                                        <th style="width: 7%;">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="items-list">
                                    @forelse($data['items'] as $index => $item)
                                        @php
                                            $isMatched = !empty($item['product_id'])
                                                && (($item['match_status'] ?? '') === 'matched_by_reference');
                                        @endphp

                                        <tr>
                                            <!-- STATUT / CATEGORIE -->
                                            <td class="text-start" style="min-width: 260px;">
                                                @if($isMatched)
                                                    <div class="alert alert-success py-2 mb-0 small">
                                                        <strong>Produit existant trouvé</strong><br>
                                                        Réf: {{ $item['matched_reference'] }}<br>
                                                    </div>

                                                    <input type="hidden" name="items[{{ $index }}][product_action]" value="existing">
                                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item['product_id'] }}">
                                                    <input type="hidden" name="items[{{ $index }}][category_id]" value="">
                                                @else
                                                    <div class="alert alert-warning py-2 mb-2 small">
                                                        <strong>Produit non trouvé</strong><br>
                                                    </div>

                                                    <input type="hidden" name="items[{{ $index }}][product_action]" value="create">
                                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="">

                                                    <label class="form-label fw-semibold small">Catégorie</label>
                                                    <select name="items[{{ $index }}][category_id]" class="form-select" required>
                                                        <option value="">Choisir une catégorie</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}">
                                                                {{ $category->Category
                                                                    ?? $category->name
                                                                    ?? $category->Category_Name
                                                                    ?? $category->category_name
                                                                    ?? ('Catégorie ' . $category->id) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>

                                            <!-- REFERENCE -->
                                            <td style="min-width: 120px;">
                                                <input type="text"
                                                       class="form-control"
                                                       name="items[{{ $index }}][reference]"
                                                       value="{{ old("items.$index.reference", $item['reference'] ?? '') }}"
                                                       required>
                                            </td>

                                            <!-- DESIGNATION -->
                                            <td style="min-width: 140px;">
                                                <input type="text"
                                                       class="form-control"
                                                       name="items[{{ $index }}][designation]"
                                                       value="{{ old("items.$index.designation", $item['designation'] ?? '') }}"
                                                       required>
                                            </td>

                                            <!-- QUANTITE -->
                                            <td style="min-width: 100px;">
                                                <input type="number"
                                                       step="0.01"
                                                       min="0.01"
                                                       class="form-control quantity"
                                                       name="items[{{ $index }}][quantity]"
                                                       value="{{ old("items.$index.quantity", $item['quantity'] ?? 1) }}"
                                                       required>
                                            </td>

                                            <!-- PRIX -->
                                            <td style="min-width: 120px;">
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       class="form-control price unit-price"
                                                       name="items[{{ $index }}][unit_price]"
                                                       value="{{ old("items.$index.unit_price", $item['unit_price'] ?? 0) }}"
                                                       required>
                                            </td>

                                            <!-- TOTAL -->
                                            <td style="min-width: 140px;">
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       class="form-control line-total text-primary fw-semibold"
                                                       name="items[{{ $index }}][line_total]"
                                                       value="{{ old("items.$index.line_total", $item['line_total'] ?? 0) }}">
                                            </td>

                                            <!-- ACTION -->
                                            <td>
                                                <button type="button"
                                                        class="btn btn-danger btn-sm rounded-pill px-3 delete-item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                Aucun produit détecté.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- TOTAL GENERAL -->
                            <div class="d-flex justify-content-end mb-3 mt-3 pe-3">
                                <div class="card border-0" style="min-width: 260px;">
                                    <div class="card-body text-end">
                                        <div class="text-muted small">Total général</div>
                                        <div class="fs-4 fw-bold text-success">
                                            <span id="grand-total">0.00</span> MAD
                                        </div>
                                        <input type="hidden" name="total" id="grand-total-input"
                                               value="{{ $data['total'] ?? 0 }}">
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
                            <i class="fas fa-save me-2"></i>Confirmer et enregistrer
                        </button>
                    </div>
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

<script>
    let index = {{ count($data['items'] ?? []) }};

    const categoriesOptions = `
        <option value="">Choisir une catégorie</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}">
                {{ $category->Category
                    ?? $category->name
                    ?? $category->Category_Name
                    ?? $category->category_name
                    ?? ('Catégorie ' . $category->id) }}
            </option>
        @endforeach
    `;

    function calculateRowTotal(row) {
        const quantityInput = row.querySelector('.quantity');
        const priceInput = row.querySelector('.price');
        const totalInput = row.querySelector('.line-total');

        const quantity = parseFloat(quantityInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const total = quantity * price;

        if (totalInput) {
            totalInput.value = total.toFixed(2);
        }

        return total;
    }

    function calculateGrandTotal() {
        let grandTotal = 0;

        document.querySelectorAll('#items-list tr').forEach(row => {
            grandTotal += calculateRowTotal(row);
        });

        const grandTotalSpan = document.getElementById('grand-total');
        const grandTotalInput = document.getElementById('grand-total-input');

        if (grandTotalSpan) {
            grandTotalSpan.textContent = grandTotal.toFixed(2);
        }

        if (grandTotalInput) {
            grandTotalInput.value = grandTotal.toFixed(2);
        }
    }

    function attachRowEvents(row) {
        const quantityInput = row.querySelector('.quantity');
        const priceInput = row.querySelector('.price');
        const totalInput = row.querySelector('.line-total');

        if (quantityInput) {
            quantityInput.addEventListener('input', calculateGrandTotal);
        }

        if (priceInput) {
            priceInput.addEventListener('input', calculateGrandTotal);
        }

        if (totalInput) {
            totalInput.addEventListener('input', calculateGrandTotal);
        }
    }

    function bindProductSearch(row) {
    const input = row.querySelector('.product-search');

    if (!input) return;

    input.addEventListener('input', function () {
        const value = this.value.trim().toLowerCase();
        const options = document.querySelectorAll('#products-list option');

        let matchedOption = null;

        for (let opt of options) {
            if (opt.value.trim().toLowerCase() === value) {
                matchedOption = opt;
                break;
            }
        }

        const statusBox = row.querySelector('.product-status');
        const productAction = row.querySelector('.product-action');
        const productId = row.querySelector('.product-id');
        const categoryWrapper = row.querySelector('.category-wrapper');
        const categorySelect = row.querySelector('.category-select');

        const referenceInput = row.querySelector('.reference-input');
        const designationInput = row.querySelector('.designation-input');
        const priceInput = row.querySelector('.price');

        if (matchedOption) {
            productAction.value = 'existing';
            productId.value = matchedOption.dataset.id;

            referenceInput.value = matchedOption.dataset.reference || '';
            designationInput.value = matchedOption.dataset.designation || '';

            if (priceInput) {
                priceInput.value = parseFloat(matchedOption.dataset.price || 0).toFixed(2);
            }

            if (categoryWrapper) {
                categoryWrapper.classList.add('d-none');
            }

            if (categorySelect) {
                categorySelect.required = false;
                categorySelect.value = '';
            }

            if (statusBox) {
                statusBox.className = 'alert alert-success py-2 mb-2 small product-status';
                statusBox.innerHTML = `
                    <strong>Produit existant trouvé</strong><br>
                    Réf: ${matchedOption.dataset.reference || '-'}
                `;
            }
        } else {
            productAction.value = 'create';
            productId.value = '';

            if (categoryWrapper) {
                categoryWrapper.classList.remove('d-none');
            }

            if (categorySelect) {
                categorySelect.required = true;
            }

            if (statusBox) {
                statusBox.className = 'alert alert-warning py-2 mb-2 small product-status';
                statusBox.innerHTML = `
                    <strong>Nouveau produit</strong><br>
                    Il sera créé avec la référence saisie.
                `;
            }
        }

        calculateGrandTotal();
    });
}

    document.getElementById('add-item').addEventListener('click', function () {
    const tbody = document.getElementById('items-list');
    const row = document.createElement('tr');

    row.innerHTML = `
        <td class="text-start" style="min-width: 280px;">
            <div class="alert alert-info py-2 mb-2 small product-status">
                <strong>Choisir un produit</strong><br>
                Sélectionnez un produit existant ou saisissez un nouveau.
            </div>

            <input type="hidden" name="items[${index}][product_action]" class="product-action" value="create">
            <input type="hidden" name="items[${index}][product_id]" class="product-id" value="">

            <label class="form-label fw-semibold small">Produit ERP</label>
            <input type="text"
                   class="form-control product-search mb-2"
                   list="products-list"
                   placeholder="Référence ou désignation..."
                   autocomplete="off">

            <div class="category-wrapper">
                <label class="form-label fw-semibold small">Catégorie</label>
                <select name="items[${index}][category_id]" class="form-select category-select" required>
                    ${categoriesOptions}
                </select>
            </div>
        </td>

        <td>
            <input type="text" class="form-control reference-input"
                   name="items[${index}][reference]" required>
        </td>

        <td>
            <input type="text" class="form-control designation-input"
                   name="items[${index}][designation]" required>
        </td>

        <td>
            <input type="number" step="0.01" min="0.01" class="form-control quantity"
                   name="items[${index}][quantity]" value="1" required>
        </td>

        <td>
            <input type="number" step="0.01" min="0" class="form-control price unit-price"
                   name="items[${index}][unit_price]" value="0" required>
        </td>

        <td>
            <input type="number" step="0.01" min="0" class="form-control line-total"
                   name="items[${index}][line_total]" value="0">
        </td>

        <td>
            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 delete-item">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);
    attachRowEvents(row);
    bindProductSearch(row);
    calculateGrandTotal();
    index++;
});

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-item') || e.target.closest('.delete-item')) {
            const row = e.target.closest('tr');
            if (row) {
                row.remove();
                calculateGrandTotal();
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#items-list tr').forEach(row => {
        attachRowEvents(row);
        bindProductSearch(row);
    });

    calculateGrandTotal();
});

    document.getElementById('confirm-form').addEventListener('submit', function () {
        const btn = this.querySelector('button[type="submit"]');

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...';
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection