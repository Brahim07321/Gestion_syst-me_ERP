@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <div class="mb-4">
        <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary rounded-pill px-3 mb-3">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
        <h2 class="fw-bold">Détails du produit</h2>
    </div>

    {{-- INFO PRODUIT --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">
            <h4 class="fw-bold mb-4">{{ $product->Designation }}</h4>

            <div class="row g-3">

                <div class="col-md-4 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="text-muted small mb-2">Référence</div>
                            <h4 class="fw-bold text-primary mb-0">{{ $product->Referonce }}</h4>

                        </div>
                    </div>
                </div>
        
                <div class="col-md-4 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="text-muted small mb-2">Code</div>
                            <h4 class="fw-bold text-success mb-0">{{ $product->code }}</h4>
                        </div>
                    </div>
                </div>
        
                <div class="col-md-4 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="text-muted small mb-2">Créé le</div>
                            <h4 class="fw-bold text-danger mb-0">{{ $product->created_at->format('d/m/Y') }}</h4>
    
                        </div>
                    </div>
                </div>
        
                <div class="col-md-4 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="text-muted small mb-2">Prix d'achat actuel</div>
                            <h4 class="fw-bold text-warning mb-0">{{ number_format($product->prace_bay, 2) }} MAD</h4>
    
                        </div>
                    </div>
                </div>
        
                <div class="col-md-4 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="text-muted small mb-2">Prix de vente actuel</div>
                            <h4 class="fw-bold text-secondary mb-0">{{ number_format($product->prace_sell, 2) }} MAD</h4>
    
                        </div>
                    </div>
                </div>
        
                <div class="col-md-4 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="text-muted small mb-2">Stock actuel</div>
                          

                            <h4 class="fw-bold mb-0 {{ $product->Quantite < 5 ? 'text-danger' : 'text-success' }}">
                                {{ $product->Quantite }}
                        </h4>

                            
                        </div>
                    </div>
                </div>
        
            </div>
        
        </div>
    </div>

    {{-- HISTORIQUE ACHATS --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4">Historique des achats</h5>

            @if($purchases->isEmpty())
                <p class="text-muted">Aucun achat enregistré pour ce produit.</p>
            @else
            <div class="table-responsive stock-table">
                <table id="productTable" class="table align-middle text-center mb-0">
                    <thead class="table-light">
                            <tr>
                                <th>Bon d'achat</th>
                                <th>Fournisseur</th>
                                <th>Date</th>
                                <th>Quantité</th>
                                <th>Prix d'achat</th>
                                <th>Total</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $item)
                            <tr>
                                <td class="fw-semibold text-primary">{{ $item->purchase->purchase_code }}</td>
                                <td>{{ $item->purchase->supplier->name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->purchase->purchase_date)->format('d/m/Y') }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->buy_price, 2) }} MAD</td>
                                <td>{{ number_format($item->line_total, 2) }} MAD</td>
                                <td>
                                    @if($item->purchase->status === 'reçu')
                                        <span class="badge bg-success rounded-pill">Reçu</span>
                                    @elseif($item->purchase->status === 'annulé')
                                        <span class="badge bg-danger rounded-pill">Annulé</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill">En attente</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
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


</div>
@endsection