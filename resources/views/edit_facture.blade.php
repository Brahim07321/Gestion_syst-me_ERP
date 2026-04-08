@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Modifier la facture</h2>
                        <p class="text-muted mb-0">Mettez à jour les informations de la facture.</p>
                    </div>

                    <a href="{{ route('factures.show', $facture->id) }}" class="btn btn-light rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success rounded-4 shadow-sm border-0">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger rounded-4 shadow-sm border-0">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger rounded-4 shadow-sm border-0">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <datalist id="products-list">
                    @foreach ($products as $product)
                        @if ($product->Quantite > 0)
                            <option value="{{ $product->Referonce }}" data-referonce="{{ $product->Referonce }}"
                                data-designation="{{ $product->Designation }}" data-price="{{ $product->prace_sell }}"
                                data-stock="{{ $product->Quantite }}">
                            </option>
                        @endif
                    @endforeach
                </datalist>
  

                {{-- ========================= --}}
                {{-- FORM FACTURE --}}
                {{-- ========================= --}}
                <form method="POST" action="{{ route('factures.update', $facture->id) }}" id="editInvoiceForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Client</label>
                            <select name="customer_search" class="form-select rounded-4" required>
                                <option value="">Choisir un client</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->name }}"
                                        {{ old('customer_search', $facture->client_name) == $customer->name ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date facture</label>
                            <input type="date" name="invoice_date" class="form-control rounded-4"
                                value="{{ old('invoice_date', \Carbon\Carbon::parse($facture->date_facture)->format('Y-m-d')) }}"
                                required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Montant payé</label>
                            <input type="number" step="0.01" min="0" max="{{ $facture->total }}"
                                name="paid_amount" class="form-control rounded-4"
                                value="{{ old('paid_amount', $facture->paid_amount) }}"
                                {{ ($facture->paid_amount ?? 0) > 0 ? 'readonly' : '' }}>

                            @if (($facture->paid_amount ?? 0) > 0)
                                <small class="text-muted">
                                    Le montant payé se modifie depuis l’historique des paiements.
                                </small>
                            @else
                                <small class="text-muted">
                                    Le montant payé ne doit pas dépasser le total de la facture.
                                </small>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Numéro facture</label>
                        <input type="text" class="form-control rounded-4" value="{{ $facture->code_facture }}" readonly>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="items-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 18%;">Référence</th>
                                    <th style="width: 30%;">Désignation</th>
                                    <th style="width: 15%;">Prix Unitaire</th>
                                    <th style="width: 10%;">Quantité</th>
                                    <th style="width: 15%;">Total</th>
                                    <th style="width: 12%;">Action</th>
                                </tr>
                            </thead>

                            <tbody id="items-list">
                                @foreach ($facture->items as $index => $item)
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control product-search" list="products-list"
                                                value="{{ old("items.$index.referonce", $item->referonce) }}" required>
                                            <input type="hidden" name="items[{{ $index }}][referonce]"
                                                class="product-hidden"
                                                value="{{ old("items.$index.referonce", $item->referonce) }}">
                                        </td>

                                        <td>
                                            <input type="text" name="items[{{ $index }}][designation]"
                                                class="form-control designation readonly-input"
                                                value="{{ old("items.$index.designation", $item->designation) }}" readonly>
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" min="0"
                                                name="items[{{ $index }}][price]" class="form-control price"
                                                value="{{ old("items.$index.price", $item->price) }}" required>
                                        </td>

                                        <td>
                                            <input type="number" min="1"
                                                name="items[{{ $index }}][quantity]" class="form-control quantity"
                                                value="{{ old("items.$index.quantity", $item->quantity) }}" required>
                                        </td>

                                        <td>
                                            <span
                                                class="line-total">{{ number_format($item->line_total, 2, '.', '') }}</span>
                                            MAD
                                        </td>

                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm delete-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-primary rounded-pill px-4 mb-4" id="add-item">
                        <i class="fas fa-plus me-2"></i>Ajouter Article
                    </button>

                    <div class="total-section text-end mt-4">
                        <p>Sous-total : <span id="subtotal">{{ number_format($facture->total, 2, '.', '') }}</span> MAD
                        </p>
                        <p>Taxe (0%) : <span id="tax">0.00</span> MAD</p>
                        <p><strong>Total Général : <span
                                    id="grand-total">{{ number_format($facture->total, 2, '.', '') }}</span> MAD</strong>
                        </p>
                    </div>

                    <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                        <a href="{{ route('factures.show', $facture->id) }}" class="btn btn-light rounded-pill px-4">
                            Annuler
                        </a>

                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="fas fa-save me-2"></i>Mettre à jour
                        </button>
                    </div>
                </form>

                {{-- ========================= --}}
                {{-- HISTORIQUE PAIEMENTS --}}
                {{-- ========================= --}}
                <div class="mt-5">
                    <h4 class="mb-3">Historique des paiements</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($facture->payments as $payment)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                        </td>

                                        <td class="fw-semibold text-success">
                                            {{ number_format($payment->amount, 2) }} MAD
                                        </td>

                                        <td>
                                            {{ $payment->note ?? '-' }}
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                {{-- EDIT --}}
                                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3"
                                                    data-bs-toggle="modal" data-bs-target="#confirmEditPaymentModal"
                                                    onclick='setConfirmEditPayment(
                                                    @json($payment->id),
                                                    @json($payment->amount),
                                                    @json(\Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d')),
                                                    @json($payment->note ?? ''),
                                                    @json(number_format($payment->amount, 2))
                                                )'>
                                                    <i class="fas fa-pen"></i>
                                                </button>

                                                {{-- DELETE --}}
                                                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3"
                                                    data-bs-toggle="modal" data-bs-target="#confirmDeletePaymentModal"
                                                    onclick='setDeletePayment(
                                                    @json($payment->id),
                                                    @json(number_format($payment->amount, 2))
                                                )'>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Aucun paiement enregistré.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- MODAL EDIT PAYMENT --}}
    {{-- ========================= --}}
    <div class="modal fade" id="editPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0 shadow">
                <form id="editPaymentForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Modifier le paiement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Montant</label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="edit_payment_amount"
                                class="form-control rounded-4" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date paiement</label>
                            <input type="date" name="payment_date" id="edit_payment_date"
                                class="form-control rounded-4" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Note</label>
                            <input type="text" name="note" id="edit_payment_note" class="form-control rounded-4"
                                placeholder="Optionnel">
                        </div>
                    </div>
                    

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Fermer
                        </button>

                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="fas fa-save me-2"></i>
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- MODAL CONFIRM EDIT --}}
    {{-- ========================= --}}
    <div class="modal fade" id="confirmEditPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-pen text-success fs-1"></i>
                </div>

                <h5 class="fw-bold mb-2">Confirmation de modification</h5>
                <p id="confirmEditPaymentText" class="text-muted mb-0"></p>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Non
                    </button>

                    <button type="button" class="btn btn-success rounded-pill px-4" id="confirmEditPaymentBtn">
                        Oui, modifier
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- MODAL CONFIRM DELETE --}}
    {{-- ========================= --}}
    <div class="modal fade" id="confirmDeletePaymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-trash text-danger fs-1"></i>
                </div>

                <h5 class="fw-bold mb-2">Confirmation de suppression</h5>
                <p id="deletePaymentText" class="text-muted mb-0"></p>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Non
                    </button>

                    <form id="deletePaymentForm" method="POST">
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

    <style>
        .readonly-input {
            background-color: #f8f9fa !important;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        .modal-content {
            border-radius: 18px !important;
        }
    </style>

    <script>
        let pendingEditPayment = null;

        function setConfirmEditPayment(id, amount, date, note, amountLabel) {
            pendingEditPayment = {
                id,
                amount,
                date,
                note
            };

            document.getElementById('confirmEditPaymentText').innerText =
                'Modifier le paiement de ' + amountLabel + ' MAD ?';
        }

        function setDeletePayment(id, amountLabel) {
            document.getElementById('deletePaymentForm').action = '/payments/' + id;
            document.getElementById('deletePaymentText').innerText =
                'Supprimer le paiement de ' + amountLabel + ' MAD ?';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const confirmEditBtn = document.getElementById('confirmEditPaymentBtn');
            const itemsTable = document.getElementById('items-table');
            const itemsList = document.getElementById('items-list');
            const productsOptions = document.querySelectorAll('#products-list option');
            let itemIndex = {{ $facture->items->count() }};
            const paidAmountInput = document.querySelector('input[name="paid_amount"]');

            if (paidAmountInput) {
                paidAmountInput.addEventListener('input', function() {
                    const total = parseFloat(document.getElementById('grand-total')?.textContent) || 0;
                    let value = parseFloat(this.value) || 0;

                    if (value > total) {
                        alert('⚠️ Le montant payé ne peut pas dépasser le total de la facture.');
                        this.value = total.toFixed(2);
                    }

                    if (value < 0) {
                        this.value = 0;
                    }
                });
            }

            if (confirmEditBtn) {
                confirmEditBtn.addEventListener('click', function() {
                    if (!pendingEditPayment) return;

                    document.getElementById('editPaymentForm').action = '/payments/' + pendingEditPayment
                    .id;
                    document.getElementById('edit_payment_amount').value = pendingEditPayment.amount;
                    document.getElementById('edit_payment_date').value = pendingEditPayment.date;
                    document.getElementById('edit_payment_note').value = pendingEditPayment.note || '';

                    const confirmModalEl = document.getElementById('confirmEditPaymentModal');
                    const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
                    confirmModal.hide();

                    setTimeout(function() {
                        const editModalEl = document.getElementById('editPaymentModal');
                        const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
                        editModal.show();
                    }, 250);
                });
            }

            function getProduct(ref) {
                for (let opt of productsOptions) {
                    if (opt.value === ref) {
                        return {
                            referonce: opt.dataset.referonce,
                            designation: opt.dataset.designation,
                            price: opt.dataset.price,
                            stock: opt.dataset.stock
                        };
                    }
                }
                return null;
            }

            function calculateTotals() {
                let grandTotal = 0;

                document.querySelectorAll('#items-list tr').forEach(row => {
                    const price = parseFloat(row.querySelector('.price')?.value) || 0;
                    const quantity = parseInt(row.querySelector('.quantity')?.value) || 0;
                    const total = price * quantity;

                    const totalCell = row.querySelector('.line-total');
                    if (totalCell) {
                        totalCell.textContent = total.toFixed(2);
                    }

                    grandTotal += total;
                });

                const subtotalEl = document.getElementById('subtotal');
                const taxEl = document.getElementById('tax');
                const grandTotalEl = document.getElementById('grand-total');

                if (subtotalEl) subtotalEl.textContent = grandTotal.toFixed(2);
                if (taxEl) taxEl.textContent = '0.00';
                if (grandTotalEl) grandTotalEl.textContent = grandTotal.toFixed(2);
            }

            itemsTable.addEventListener('input', function(e) {
                const row = e.target.closest('tr');
                if (!row) return;

                if (e.target.classList.contains('product-search')) {
                    const product = getProduct(e.target.value);

                    if (product) {
                        const stock = parseInt(product.stock) || 0;

                        if (stock <= 0) {
                            alert('⚠️ هاد المنتج ما بقاش متوفر فالمخزون');
                            row.querySelector('.product-search').value = '';
                            row.querySelector('.product-hidden').value = '';
                            row.querySelector('.designation').value = '';
                            row.querySelector('.price').value = '';
                            calculateTotals();
                            return;
                        }

                        row.querySelector('.product-hidden').value = product.referonce;
                        row.querySelector('.designation').value = product.designation;
                        row.querySelector('.price').value = parseFloat(product.price).toFixed(2);
                    } else {
                        row.querySelector('.product-hidden').value = '';
                        row.querySelector('.designation').value = '';
                        row.querySelector('.price').value = '';
                    }

                    calculateTotals();
                }

                if (
                    e.target.classList.contains('price') ||
                    e.target.classList.contains('quantity')
                ) {
                    calculateTotals();
                }
            });

            document.getElementById('add-item').addEventListener('click', function() {
                const row = document.createElement('tr');

                row.innerHTML = `
                <td>
                    <input type="text"
                        class="form-control product-search"
                        list="products-list"
                        placeholder="Référence..."
                        required>
                    <input type="hidden"
                        name="items[${itemIndex}][referonce]"
                        class="product-hidden">
                </td>

                <td>
                    <input type="text"
                        name="items[${itemIndex}][designation]"
                        class="form-control designation readonly-input"
                        readonly>
                </td>

                <td>
                    <input type="number"
                        step="0.01"
                        min="0"
                        name="items[${itemIndex}][price]"
                        class="form-control price"
                        required>
                </td>

                <td>
                    <input type="number"
                        min="1"
                        value="1"
                        name="items[${itemIndex}][quantity]"
                        class="form-control quantity"
                        required>
                </td>

                <td>
                    <span class="line-total">0.00</span> MAD
                </td>

                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm delete-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

                itemsList.appendChild(row);
                itemIndex++;
                calculateTotals();
            });

            itemsTable.addEventListener('click', function(e) {
                if (e.target.closest('.delete-item')) {
                    const rows = document.querySelectorAll('#items-list tr');

                    if (rows.length > 1) {
                        e.target.closest('tr').remove();
                        calculateTotals();
                    }
                }
            });

            calculateTotals();
        });
    </script>
@endsection
