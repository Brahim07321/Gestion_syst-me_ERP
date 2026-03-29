@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">
        <div class="shadow p-4 bg-white rounded">
            <h2 class="mb-4">Historique du Stock</h2>

            <table class="table table-bordered">
                <thead>
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
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $movement->product->Designation ?? '-' }}</td>

                            <td>
                                @if ($movement->type == 'entree')
                                    <span class="badge bg-success">Entrée</span>
                                @else
                                    <span class="badge bg-danger">Sortie</span>
                                @endif
                            </td>

                            <td>{{ $movement->quantity }}</td>
                            <td>{{ $movement->source }}</td>
                            <td>{{ $movement->reference }}</td>

                            <td>
                                @if($movement->source == 'facture' && $movement->facture)
                                    <a href="{{ route('factures.show', $movement->facture->id) }}"
                                       class="btn btn-sm btn-primary">
                                        Voir
                                    </a>
                            
                                @elseif($movement->source == 'achat' && $movement->purchase)
                                    <a href="{{ route('purchases.show', $movement->purchase->id) }}"
                                       class="btn btn-sm btn-success">
                                        Voir
                                    </a>
                                @endif
                            </td>                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Aucun mouvement trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $movements->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
