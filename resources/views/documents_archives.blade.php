@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <!-- TITRE -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Documents archivés</h2>
        <p class="text-muted mb-0">
            Affichez les bons d’achat et les factures annulés ou supprimés.
        </p>
    </div>

    <!-- CONTENU PRINCIPAL -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <!-- HEADER -->
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Historique des documents annulés / supprimés</h4>
                    <p class="text-muted mb-0">
                        Cette page sert à consulter les documents qui ne doivent plus apparaître dans les mouvements actifs.
                    </p>
                </div>
            </div>

            <!-- FILTRES -->
            <div class="card border-1 bg-light-subtle rounded-4 mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('documents.archives') }}" method="GET" class="row g-3 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Recherche</label>
                            <div class="input-group">
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="N° document / fournisseur / client..."
                                       value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Type document</label>
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous</option>
                                <option value="achat" {{ request('type') === 'achat' ? 'selected' : '' }}>
                                    Bons d’achat
                                </option>
                                <option value="facture" {{ request('type') === 'facture' ? 'selected' : '' }}>
                                    Factures
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Date début</label>
                            <input type="date"
                                   name="date_from"
                                   class="form-control"
                                   value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Date fin</label>
                            <input type="date"
                                   name="date_to"
                                   class="form-control"
                                   value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold d-block invisible">Reset</label>
                            <a href="{{ route('documents.archives') }}" class="btn btn-secondary w-100 rounded-pill">
                                Réinitialiser
                            </a>
                        </div>

                    </form>
                </div>
            </div>

            <!-- BONS D'ACHAT -->
            @if(request('type') !== 'facture')
                <div class="mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-cart-shopping me-2 text-primary"></i>
                        Bons d’achat annulés / supprimés
                    </h5>

                    <div class="table-responsive archive-table">
                        <table class="table align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>N° Bon d’achat</th>
                                    <th>Fournisseur</th>
                                    <th>Date achat</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                    <th>État</th>
                                    <th>Action</th>

                                </tr>
                            </thead>

                            <tbody>
                                @forelse($purchases as $purchase)
                                    <tr>
                                        <td class="fw-semibold">{{ $purchase->id }}</td>

                                        <td class="fw-semibold text-primary">
                                            {{ $purchase->purchase_code ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $purchase->supplier->name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $purchase->purchase_date ?? $purchase->created_at?->format('Y-m-d') }}
                                        </td>

                                        <td>
                                            {{ number_format($purchase->total ?? 0, 2) }} DH
                                        </td>

                                        <td>
                                            <span class="badge rounded-pill bg-warning text-dark">
                                                {{ $purchase->status ?? '-' }}
                                            </span>
                                        </td>

                                        <td>
                                            @if(!empty($purchase->deleted_at))
                                                <span class="badge rounded-pill bg-danger">
                                                    Supprimé
                                                </span>
                                            @else
                                                <span class="badge rounded-pill bg-secondary">
                                                    Annulé
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('purchases.show', $purchase->id) }}?from_archive=1"
                                                   class="btn btn-primary btn-sm rounded-pill px-3">
                                                    <i class="fas fa-eye me-1"></i>Voir
                                                </a>
                                        
                                                <button type="button"
                                                class="btn btn-success btn-sm rounded-pill px-3"
                                                data-bs-toggle="modal"
                                                data-bs-target="#restoreDocumentModal"
                                                onclick="setRestoreDocument(
                                                    @js(route('documents.archives.purchase.restore', $purchase->id)),
                                                    @js('Restaurer bon d’achat'),
                                                    @js('Voulez-vous vraiment restaurer ce bon d’achat et ajouter le stock ?'),
                                                    @js($purchase->purchase_code ?? '-')
                                                )">
                                            <i class="fas fa-rotate-left me-1"></i>Restaurer
                                        </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            Aucun bon d’achat   supprimé.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- FACTURES -->
            @if(request('type') !== 'achat')
                <div class="mt-5">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-file-invoice me-2 text-success"></i>
                        Factures annulées / supprimées
                    </h5>

                    <div class="table-responsive archive-table">
                        <table class="table align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>N° Facture</th>
                                    <th>Client</th>
                                    <th>Date facture</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                    <th>État</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($factures as $facture)
                                    <tr>
                                        <td class="fw-semibold">{{ $facture->id }}</td>

                                        <td class="fw-semibold text-primary">
                                            {{ $facture->code_facture ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $facture->client_name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $facture->date_facture ?? $facture->created_at?->format('Y-m-d') }}
                                        </td>

                                        <td>
                                            {{ number_format($facture->total ?? $facture->grand_total ?? 0, 2) }} DH
                                        </td>

                                        <td>
                                            <span class="badge rounded-pill bg-warning text-dark">
                                                {{ $facture->status ?? '-' }}
                                            </span>
                                        </td>

                                        <td>
                                            @if(!empty($facture->deleted_at))
                                                <span class="badge rounded-pill bg-danger">
                                                    Supprimée
                                                </span>
                                            @else
                                                <span class="badge rounded-pill bg-secondary">
                                                    Annulée
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('factures.show', $facture->id) }}?from_archive=1"
                                                   class="btn btn-primary btn-sm rounded-pill px-3">
                                                    <i class="fas fa-eye me-1"></i>Voir
                                                </a>
                                        
                                                <button type="button"
                                                class="btn btn-success btn-sm rounded-pill px-3"
                                                data-bs-toggle="modal"
                                                data-bs-target="#restoreDocumentModal"
                                                onclick="setRestoreDocument(
                                                    @js(route('documents.archives.facture.restore', $facture->id)),
                                                    @js('Restaurer facture'),
                                                    @js('Voulez-vous vraiment restaurer cette facture ?'),
                                                    @js($facture->code_facture ?? '-')
                                                )">
                                            <i class="fas fa-rotate-left me-1"></i>Restaurer
                                        </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            Aucune facture annulée ou supprimée.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
<!-- MODAL RESTAURATION -->
<div class="modal fade" id="restoreDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 text-center p-4 border-0 shadow">

            <div class="mb-3">
                <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center"
                     style="width:70px;height:70px;background:#ecfdf5;">
                    <i class="fas fa-rotate-left text-success fs-1"></i>
                </div>
            </div>

            <h5 class="fw-bold mb-2" id="restoreDocumentTitle">
                Confirmation
            </h5>

            <p class="text-muted mb-1" id="restoreDocumentMessage">
                Voulez-vous vraiment restaurer ce document ?
            </p>

            <div class="fw-bold text-primary mb-3" id="restoreDocumentCode"></div>

            <div class="alert alert-warning border-0 rounded-4 text-start small mb-4">
                <i class="fas fa-triangle-exclamation me-2"></i>
                Cette action peut modifier le stock selon le type de document.
            </div>

            <div class="d-flex justify-content-center gap-3">
                <button type="button"
                        class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">
                    Non
                </button>

                <form id="restoreDocumentForm" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="fas fa-check me-2"></i>Oui, restaurer
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<style>
    .archive-table {
        overflow: hidden;
        background: #ffffff;
    }

    .archive-table table th,
    .archive-table table td {
        vertical-align: middle;
        white-space: normal;
        word-break: break-word;
    }

    .archive-table tbody tr {
        transition: all 0.2s ease;
    }

    .archive-table tbody tr:hover {
        background: #f8fafc;
    }

    .bg-light-subtle {
        background: #f8fafc;
    }
</style>
<script>
    function setRestoreDocument(actionUrl, title, message, code) {
        document.getElementById('restoreDocumentForm').action = actionUrl;
        document.getElementById('restoreDocumentTitle').innerText = title;
        document.getElementById('restoreDocumentMessage').innerText = message;
        document.getElementById('restoreDocumentCode').innerText = code;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection