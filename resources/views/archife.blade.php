@extends('layout')
@section('content')

<div class="main-content main-content-expanded" id="mainContent">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-container">
        <div class="table-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title">Archive des Factures</h2>
                <a href="/facture" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouvel facture
                </a>
            </div>

            <form action="{{ route('factures.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                <div class="input-group">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control form-control-sm" 
                        placeholder="Search facture..."
                        value="{{ request('search') }}"
                    >
                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            
                <select name="status" class="form-select form-select-sm" style="width: 180px;" onchange="this.form.submit()">
                    <option value="">Toutes les factures</option>
                    <option value="payée" {{ request('status') == 'payée' ? 'selected' : '' }}>Payée</option>
                    <option value="non payée" {{ request('status') == 'non payée' ? 'selected' : '' }}>Non payée</option>
                </select>
            
                <a href="{{ route('factures.index') }}" class="btn btn-sm btn-secondary">
                    Reset
                </a>
            </form>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code_Facture</th>
                    <th>Nom de client</th>
                    <th>Total</th>
                    <th>La situation</th>
                    <th>Date de facture</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($factures as $facture)
                    <tr>
                        <td>{{ $facture->id }}</td>
                        <td>{{ $facture->code_facture }}</td>
                        <td>{{ $facture->client_name }}</td>
                        <td>{{ number_format($facture->total, 2) }} MAD</td>
                        <td>
                            @if($facture->status === 'payée')
                                <span class="badge bg-success">Payée</span>
                            @else
                                <span class="badge bg-danger">Non payée</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>
                       
                        <td>
                            <a href="{{ route('factures.show', $facture->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Aucune facture trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $factures->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}

        </div>
    </div>
</div>
@endsection