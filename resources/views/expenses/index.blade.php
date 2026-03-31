@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title">Gestion des Dépenses</h2>

            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#expenseModal">
                <i class="fas fa-plus"></i> Ajouter Dépense
            </button>
        </div>

        <form action="{{ route('expenses.index') }}" method="GET" class="mb-3 d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm"
                placeholder="Rechercher une dépense..." value="{{ request('search') }}">

            <button class="btn btn-outline-secondary btn-sm" type="submit">
                <i class="fas fa-search"></i>
            </button>

            <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">
                Reset
            </a>
        </form>

        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Montant</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($expenses as $expense)
                    <tr>
                        <td>{{ $expense->id }}</td>
                        <td>{{ $expense->name }}</td>
                        <td>{{ number_format($expense->amount, 2) }} MAD</td>
                        <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                        <td>{{ $expense->description ?? '-' }}</td>
                        <td class="text-center">
                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST"
                                onsubmit="return confirm('Voulez-vous vraiment supprimer cette dépense ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Aucune dépense trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $expenses->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- MODAL AJOUT DEPENSE -->
<div class="modal fade" id="expenseModal" tabindex="-1" aria-labelledby="expenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="expenseModalLabel">Ajouter une Dépense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nom</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Montant</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection