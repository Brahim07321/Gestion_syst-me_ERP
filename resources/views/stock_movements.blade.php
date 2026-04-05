@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <!-- TITRE -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h2 class="fw-bold mb-1">Historique des mouvements de stock</h2>
        <div class="d-flex flex-wrap gap-2">


            <a href="{{ route('stock.movements.export.excel', request()->query()) }}"
               class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-file-excel me-2"></i>Exporter Excel
            </a>
        
            <a href="{{ route('stock.movements.export.pdf', request()->query()) }}"
               class="btn btn-danger rounded-pill px-4">
                <i class="fas fa-file-pdf me-2"></i>Exporter PDF
            </a>
        </div>
        



    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <p class="text-muted mb-0">
            Consultez toutes les entrées et sorties de stock liées aux achats et aux factures.
        </p>
    </div>
    <!-- CONTENEUR PRINCIPAL -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Liste des mouvements</h4>
                    <p class="text-muted mb-0">Suivez chaque opération effectuée sur le stock.</p>
                </div>
            </div>
            <div class="card bg-light-subtle rounded-4 mb-4">
                <div class="card-body p-3">
            
                    <form method="GET" class="row g-3 align-items-end">
            
                        <!-- Search -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Recherche</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Produit ou référence..."
                                value="{{ request('search') }}">
                        </div>
            
                        <!-- Type -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" class="form-select">
                                <option value="">Tous</option>
                                <option value="entree" {{ request('type')=='entree'?'selected':'' }}>Entrée</option>
                                <option value="sortie" {{ request('type')=='sortie'?'selected':'' }}>Sortie</option>
                            </select>
                        </div>
            
                        <!-- Source -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Source</label>
                            <select name="source" class="form-select">
                                <option value="">Toutes</option>
                                <option value="facture" {{ request('source')=='facture'?'selected':'' }}>Facture</option>
                                <option value="achat" {{ request('source')=='achat'?'selected':'' }}>Achat</option>
                                <option value="annulation facture" {{ request('source')=='annulation facture'?'selected':'' }}>Annulation facture</option>
                                <option value="suppression achat" {{ request('source')=='suppression achat'?'selected':'' }}>Suppression achat</option>
                            </select>
                        </div>
            
                        <!-- Date from -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Du</label>
                            <input type="date" name="date_from" class="form-control"
                                value="{{ request('date_from') }}">
                        </div>
            
                        <!-- Date to -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Au</label>
                            <input type="date" name="date_to" class="form-control"
                                value="{{ request('date_to') }}">
                        </div>
            
                        <!-- Buttons -->
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
            
                            <a href="{{ route('stock.movements') }}" class="btn btn-secondary w-100">
                                Reset
                            </a>
                        </div>
            
                    </form>
            
                </div>
            </div>

            <!-- TABLEAU -->
            <div class="table-responsive stock-history-table">
                <table class="table align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Produit</th>
                            <th>Type</th>
                            <th>Quantité</th>
                            <th>Source</th>
                            <th>Référence</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($movements as $movement)
                        <tr class="
                        {{ $movement->facture && $movement->facture->trashed() ? 'table-danger' : '' }}
                        {{ $movement->purchase && $movement->purchase->trashed() ? 'table-danger' : '' }}
                        {{ $movement->facture && $movement->facture->status == 'annulée' ? 'table-secondary' : '' }}
                    ">                          
                      <td class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}
                                </td>

                                <td>
                                    {{ $movement->product->Designation ?? '-' }}
                                </td>

                                <td>
                                    @if ($movement->type == 'entree')
                                        <span class="badge rounded-pill px-3 py-2 bg-success-subtle text-success">
                                            Entrée
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-2 bg-danger-subtle text-danger">
                                            Sortie
                                        </span>
                                    @endif
                                </td>

                                <td class="fw-semibold">
                                    {{ $movement->quantity }}
                                </td>

                                <td>
                                    @if ($movement->source == 'facture')
                                        <span class="badge rounded-pill px-3 py-2 bg-primary-subtle text-primary">
                                            Facture
                                        </span>
                                    @elseif($movement->source == 'achat')
                                        <span class="badge rounded-pill px-3 py-2 bg-success-subtle text-success">
                                            Achat
                                        </span>
                                    @elseif($movement->source == 'suppression achat')
                                        <span class="badge rounded-pill px-3 py-2 bg-warning-subtle text-warning-emphasis">
                                            Suppression achat
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-2 bg-secondary-subtle text-secondary">
                                            {{ $movement->source }}
                                        </span>
                                    @endif
                                </td>

                                

                                <td class="fw-semibold text-primary">
                                    {{ $movement->reference }}
                                </td>

                                <td>
                                    @if($movement->source == 'facture' && $movement->facture)
                                        <a href="{{ route('factures.show', $movement->facture->id) }}"
                                           class="btn btn-sm btn-primary rounded-pill px-3">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                
                                    @elseif($movement->source == 'annulation facture' && $movement->facture)
                                        <a href="{{ route('factures.show', $movement->facture->id) }}"
                                           class="btn btn-sm btn-primary rounded-pill px-3">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                
                                    @elseif($movement->source == 'achat' && $movement->purchase)
                                        <a href="{{ route('purchases.show', $movement->purchase->id) }}"
                                           class="btn btn-sm btn-success rounded-pill px-3">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Aucun mouvement trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $movements->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<style>
    .stock-history-table {
        overflow: hidden;
        background: #ffffff;
    }

    .stock-history-table table th,
    .stock-history-table table td {
        vertical-align: middle;
        white-space: normal;
        word-break: break-word;
    }

    .stock-history-table tbody tr {
        transition: all 0.2s ease;
    }

    .stock-history-table tbody tr:hover {
        background: #f8fafc;
    }

    .bg-success-subtle {
        background: #ecfdf5;
    }

    .bg-danger-subtle {
        background: #fee2e2;
    }

    .bg-warning-subtle {
        background: #fef3c7;
    }

    .bg-primary-subtle {
        background: #dbeafe;
    }

    .bg-secondary-subtle {
        background: #f1f5f9;
    }
</style>
@endsection