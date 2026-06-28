@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <!-- TITRE -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Modification d’un client</h2>
        <p class="text-muted mb-0">
            Modifiez les informations du client sélectionné puis consultez la liste complète des clients.
        </p>
    </div>

    <!-- ALERTES -->
    @if (session('message'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- FORMULAIRE -->
    <div class="card border-1 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">Informations du client</h4>
                <p class="text-muted mb-0">Mettez à jour les données du client ci-dessous.</p>
            </div>

            <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="customerName" class="form-label fw-semibold">Nom complet</label>
                        <input type="text"
                               class="form-control"
                               name="name"
                               id="customerName"
                               value="{{ $customer->name }}"
                               required>
                    </div>

                    <div class="col-md-12">
                        <label for="customerAddress" class="form-label fw-semibold">Adresse</label>
                        <input type="text"
                               class="form-control"
                               id="customerAddress"
                               name="address"
                               value="{{ $customer->address }}"
                               required>
                    </div>

                    <div class="col-md-12">
                        <label for="customerContact" class="form-label fw-semibold">Numéro de contact</label>
                        <input type="tel"
                               class="form-control"
                               id="customerContact"
                               name="contact"
                               value="{{ $customer->contact }}"
                               pattern="[0-9]{10}"
                               required>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2 flex-wrap">
                    <a href="/Customer" class="btn btn-light rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>

                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTE CLIENTS -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Liste des clients</h4>
                    <p class="text-muted mb-0">Consultez rapidement les autres clients enregistrés.</p>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-danger rounded-pill px-4">
                        <i class="fas fa-file-pdf me-2"></i>Exporter en PDF
                    </button>
                    <button class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-file-excel me-2"></i>Exporter en Excel
                    </button>
                </div>
            </div>

            <div class="table-responsive customers-table">
                <table class="table align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom complet</th>
                            <th>Adresse</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($customers as $item)
                            <tr>
                                <td class="fw-semibold">{{ $item->id }}</td>
                                <td class="fw-semibold text-primary">{{ $item->name }}</td>
                                <td>{{ $item->address }}</td>
                                <td>{{ $item->contact }}</td>
                                <td>
                                    <a href="{{ route('customers.edit', $item->id) }}"
                                       class="btn btn-sm btn-primary rounded-pill px-3">
                                        <i class="fas fa-edit me-1"></i>Modifier
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Aucun client trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $customers->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<style>
    .customers-table {
        overflow: hidden;
        background: #ffffff;
    }

    .customers-table table th,
    .customers-table table td {
        vertical-align: middle;
        white-space: normal;
        word-break: break-word;
    }

    .customers-table tbody tr {
        transition: all 0.2s ease;
    }

    .customers-table tbody tr:hover {
        background: #f8fafc;
    }
</style>
@endsection