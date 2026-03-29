@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">
        <div class="shadow p-4 bg-white rounded">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0 text-primary">Liste des Achats</h2>

                <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouvel Achat
                </a>
            </div>

            <form method="GET" action="{{ route('purchases.index') }}" class="row g-2 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                        placeholder="Rechercher par code ou fournisseur..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Rechercher
                    </button>


                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="reçu" {{ request('status') == 'reçu' ? 'selected' : '' }}>Reçu</option>
                        <option value="en attente" {{ request('status') == 'en attente' ? 'selected' : '' }}>En attente
                        </option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">


                    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code Achat</th>
                            <th>Fournisseur</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td>{{ $purchase->id }}</td>
                                <td>{{ $purchase->purchase_code }}</td>
                                <td>{{ $purchase->supplier->name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                                <td>{{ number_format($purchase->total, 2) }} MAD</td>
                                <td>
                                    @if ($purchase->status == 'reçu')
                                        <span class="badge bg-success">Reçu</span>
                                    @else
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('purchases.show', $purchase->id) }}"
                                        class="btn btn-sm btn-primary text-white">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucun achat trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $purchases->appends(request()->query())->links('pagination::bootstrap-5') }}

            </div>

        </div>
    </div>
@endsection
