@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <!-- TITRE -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Gestion des dépenses</h2>
        <p class="text-muted mb-0">
            Ajoutez, recherchez et suivez facilement toutes les dépenses enregistrées.
        </p>
    </div>

    <!-- ALERTES -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- CONTENEUR PRINCIPAL -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <!-- HEADER -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Liste des dépenses</h4>
                    <p class="text-muted mb-0">Consultez et gérez vos charges quotidiennes.</p>
                </div>

                <button type="button"
                        class="btn btn-success rounded-pill px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#expenseModal">
                    <i class="fas fa-plus me-2"></i>Ajouter une dépense
                </button>
            </div>

            <!-- RECHERCHE -->
            <div class="card border-1 bg-light-subtle rounded-4 mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('expenses.index') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Recherche</label>
                            <div class="input-group">
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="Rechercher une dépense..."
                                       value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold d-block invisible">Reset</label>
                            <a href="{{ route('expenses.index') }}" class="btn btn-secondary w-100 rounded-pill">
                                Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABLEAU -->
            <div class="table-responsive expenses-table">
                <table class="table align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Montant</th>
                            <th>Date</th>
                            <th>Description</th>
                            @if(Auth::user()->role === 'admin')

                            <th>Action</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="fw-semibold">{{ $expense->id }}</td>
                                <td class="fw-semibold text-primary">{{ $expense->name }}</td>
                                <td class="fw-semibold text-danger">
                                    {{ number_format($expense->amount, 2) }} MAD
                                </td>
                                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                                <td>{{ $expense->description ?? '-' }}</td>
                                <td>
                                    @if(Auth::user()->role === 'admin')

                                   

                                    <button class="btn btn-danger btn-sm rounded-pill px-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteCustomerModal"
                                    onclick="setDeleteexpense('{{ $expense->id }}', '{{ $expense->name }}')">
                                    <i class="fas fa-trash me-1"></i>Supprimer
                                </button>
                                @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Aucune dépense trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $expenses->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- MODAL AJOUT DEPENSE -->
<div class="modal fade" id="expenseModal" tabindex="-1" aria-labelledby="expenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">

            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf

                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="expenseModalLabel">Ajouter une dépense</h5>
                        <p class="text-muted small mb-0">Renseignez les informations de la dépense.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom</label>
                        <input type="text" name="name" class="form-control" placeholder="Nom de la dépense" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Montant</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="Montant" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Description optionnelle"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Fermer
                    </button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        Enregistrer
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">

            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-trash text-danger fs-1"></i>
                </div>

                <h5 class="fw-bold mb-2">Confirmation de suppression</h5>

                <p id="deleteCustomerText" class="text-muted mb-0">
                    Voulez-vous vraiment supprimer cette dépense
                                </p>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Annuler
                    </button>

                    <form id="deleteCustomerForm" method="POST">
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
</div>

<style>
    .expenses-table {
        overflow: hidden;
        background: #ffffff;
    }

    .expenses-table table th,
    .expenses-table table td {
        vertical-align: middle;
        white-space: normal;
        word-break: break-word;
    }

    .expenses-table tbody tr {
        transition: all 0.2s ease;
    }

    .expenses-table tbody tr:hover {
        background: #f8fafc;
    }

    .bg-light-subtle {
        background: #f8fafc;
    }
</style>
<script>
       //confirm delet

       function setDeleteexpense(id, name) {
            document.getElementById('deleteCustomerForm').action = '/expenses/' + id;
            document.getElementById('deleteCustomerText').innerText =
                'Voulez-vous vraiment supprimer le client : ' + name + ' ?';
        }
</script>
@endsection