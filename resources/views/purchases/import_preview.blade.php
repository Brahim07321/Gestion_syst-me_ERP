@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <!-- TITRE -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Aperçu de l’importation des achats</h2>
        <p class="text-muted mb-0">
            Vérifiez les produits importés, comparez les anciens et nouveaux prix, puis confirmez la création de l’achat.
        </p>
    </div>

    <!-- STATS -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Nombre total de lignes</div>
                    <h4 class="fw-bold text-primary mb-0">{{ count($preview) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Produits avec hausse</div>
                    <h4 class="fw-bold text-danger mb-0">
                        {{ collect($preview)->where('status', 'Prix augmenté')->count() }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Produits avec baisse</div>
                    <h4 class="fw-bold text-success mb-0">
                        {{ collect($preview)->where('status', 'Prix diminué')->count() }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENEUR -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <!-- FILTRE -->
            <div class="card border-0 bg-light-subtle rounded-4 mb-4">
                <div class="card-body p-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rechercher un produit</label>
                            <input type="text"
                                   id="previewSearch"
                                   class="form-control"
                                   placeholder="Tapez une référence ou une désignation...">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Filtrer par variation</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">Tous</option>
                                <option value="Prix augmenté">Prix augmenté</option>
                                <option value="Prix diminué">Prix diminué</option>
                                <option value="Prix identique">Prix identique</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold d-block invisible">Reset</label>
                            <button type="button" class="btn btn-secondary w-100 rounded-pill" onclick="resetPreviewFilter()">
                                Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <form action="{{ route('purchases.import.confirm') }}" method="POST">
                @csrf
            
                <div class="table-responsive preview-import-table">
                    <table class="table align-middle text-center mb-0" id="previewTable">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th>Référence</th>
                                <th>Produit</th>
                                <th>Quantité</th>
                                <th>Ancien prix</th>
                                <th>Nouveau prix</th>
                                <th>Écart</th>
                                <th>Observation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($preview as $index => $item)
                                @php
                                    $oldPrice = (float) ($item['old_price'] ?? 0);
                                    $newPrice = (float) ($item['price'] ?? 0);
                                    $difference = $newPrice - $oldPrice;
                                @endphp
                                <tr data-status="{{ $item['status'] }}">
                                    <td>
                                        <input type="checkbox"
                                        name="selected_items[]"
                                        value="{{ $index }}"
                                        class="row-checkbox"
                                        {{ empty($item['product_id']) ? 'disabled' : 'checked' }}>                                    </td>
            
                                    <td class="fw-semibold text-primary">
                                        {{ $item['reference'] ?: '-' }}
                                    </td>
            
                                    <td class="product-name fw-semibold">
                                        {{ $item['designation'] }}
                                    </td>
            
                                    <td>
                                        <span class="badge rounded-pill bg-secondary-subtle text-dark px-3 py-2">
                                            {{ $item['quantity'] }}
                                        </span>
                                    </td>
            
                                    <td class="fw-semibold text-muted">
                                        {{ number_format($oldPrice, 2) }} MAD
                                    </td>
            
                                    <td class="fw-bold">
                                        {{ number_format($newPrice, 2) }} MAD
                                    </td>
            
                                    <td>
                                        @if($difference > 0)
                                            <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2">
                                                +{{ number_format($difference, 2) }} MAD
                                            </span>
                                        @elseif($difference < 0)
                                            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">
                                                {{ number_format($difference, 2) }} MAD
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-light text-secondary px-3 py-2">
                                                0.00 MAD
                                            </span>
                                        @endif
                                    </td>
            
                                    <td>
                                        @if(empty($item['product_id']))
                                            <span class="badge rounded-pill bg-dark px-3 py-2">
                                                Produit introuvable
                                            </span>
                                        @elseif($item['status'] == 'Prix augmenté')
                                            <span class="badge rounded-pill bg-danger px-3 py-2">
                                                {{ $item['status'] }}
                                            </span>
                                        @elseif($item['status'] == 'Prix diminué')
                                            <span class="badge rounded-pill bg-success px-3 py-2">
                                                {{ $item['status'] }}
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary px-3 py-2">
                                                {{ $item['status'] }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        Aucun produit détecté.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            
                <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary rounded-pill px-4" onclick="checkVisible(true)">
                            Tout cocher
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="checkVisible(false)">
                            Tout décocher
                        </button>
                    </div>
            
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('purchases.index') }}" class="btn btn-light rounded-pill px-4">
                            Retour
                        </a>
            
                        <button class="btn btn-success rounded-pill px-4">
                            <i class="fas fa-check me-2"></i>Confirmer et créer l’achat
                        </button>
                    </div>
                </div>
            </form>

            <!-- ACTIONS -->
           
        </div>
    </div>
</div>

<style>
    .preview-import-table {
        overflow: hidden;
        background: #ffffff;
    }

    .preview-import-table table th,
    .preview-import-table table td {
        vertical-align: middle;
        white-space: normal;
        word-break: break-word;
    }

    .preview-import-table tbody tr {
        transition: all 0.2s ease;
    }

    .preview-import-table tbody tr:hover {
        background: #f8fafc;
    }

    .bg-light-subtle {
        background: #f8fafc;
    }

    .bg-success-subtle {
        background: #ecfdf5;
    }

    .bg-danger-subtle {
        background: #fee2e2;
    }

    .bg-secondary-subtle {
        background: #f1f5f9;
    }
</style>

<script>
    const previewSearch = document.getElementById('previewSearch');
    const statusFilter = document.getElementById('statusFilter');
    const previewTable = document.getElementById('previewTable');

    function filterPreviewTable() {
        const searchValue = (previewSearch.value || '').toLowerCase().trim();
        const statusValue = statusFilter.value;

        const rows = previewTable.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const productText = row.querySelector('.product-name')?.textContent.toLowerCase() || '';
            const referenceText = row.children[0]?.textContent.toLowerCase() || '';
            const rowStatus = row.getAttribute('data-status') || '';

            const matchSearch =
                productText.includes(searchValue) ||
                referenceText.includes(searchValue);

            const matchStatus =
                statusValue === '' || rowStatus === statusValue;

            row.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }

    function resetPreviewFilter() {
        previewSearch.value = '';
        statusFilter.value = '';
        filterPreviewTable();
    }

    previewSearch.addEventListener('input', filterPreviewTable);
    statusFilter.addEventListener('change', filterPreviewTable);

    const checkAll = document.getElementById('checkAll');

if (checkAll) {
    checkAll.addEventListener('change', function () {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') {
                cb.checked = this.checked;
            }
        });
    });
}

function checkVisible(state) {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        if (cb.closest('tr').style.display !== 'none') {
            cb.checked = state;
        }
    });

    if (checkAll) {
        checkAll.checked = state;
    }
}
</script>
@endsection