@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">

        <!-- TITRE -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

            <h2 class="fw-bold mb-1">Gestion des clients</h2>
        
            <div class="ms-md-auto">
                <div class="dropdown">
        
                    <button class="btn btn-light rounded-pill px-3 shadow-sm"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
        
                    <ul class="dropdown-menu dropdown-menu-end p-2 border-0 shadow rounded-4">
        
                        <li>
                            <a href="{{ route('customers.export.pdf') }}"
                               class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                                <i class="fas fa-file-pdf text-danger"></i>
                                Exporter en PDF
                            </a>
                        </li>
        
                        <li>
                            <a href="{{ route('customers.export.excel') }}"
                               class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                                <i class="fas fa-file-excel text-success"></i>
                                Exporter en Excel
                            </a>
                        </li>
        
                        <li><hr class="dropdown-divider"></li>
        
                        <li>
                            <button class="dropdown-item rounded-3 d-flex align-items-center gap-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#customerModal"
                                    onclick="openAddModal()">
                                <i class="fas fa-plus text-primary"></i>
                                Ajouter un client
                            </button>
                        </li>
        
                    </ul>
        
                </div>
            </div>
        
        </div>        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <p class="text-muted mb-0">
            Ajoutez, recherchez et consultez facilement la liste de vos clients.
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

        <!-- CONTENEUR PRINCIPAL -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <!-- HEADER -->





                    <!-- RECHERCHE -->
                    <form class="w-100" style="max-width: 360px;" method="GET" action="{{ url('/Customer') }}">
                        <label class="form-label fw-semibold">Recherche</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Rechercher un client..."
                                value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TABLEAU -->
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
                            @forelse ($customers as $customer)
                                <tr>
                                    <td class="fw-semibold">{{ $customer->id }}</td>
                                    <td class="fw-semibold text-primary">{{ $customer->name }}</td>
                                    <td>{{ $customer->address }}</td>
                                    <td>{{ $customer->contact }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                                            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal"
                                                data-bs-target="#customerModal"
                                                onclick="openEditModal(
                                                    '{{ $customer->id }}',
                                                    '{{ $customer->name }}',
                                                    '{{ $customer->address }}',
                                                    '{{ $customer->contact }}'
                                                )">
                                                <i class="fas fa-edit me-1"></i>Modifier
                                            </button>
                                            @if(Auth::user()->role === 'admin')


                                            <button class="btn btn-danger btn-sm rounded-pill px-3" data-bs-toggle="modal"
                                                data-bs-target="#deleteCustomerModal"
                                                onclick="setDeleteCustomer('{{ $customer->id }}', '{{ $customer->name }}')">
                                                <i class="fas fa-trash me-1"></i>Supprimer
                                            </button>
                                            @endif
                                        </div>
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

                <!-- PAGINATION -->
                <div class="mt-4 d-flex justify-content-center">
                    {{ $customers->links('pagination::bootstrap-5') }}
                </div>

                <!-- IMPORT -->
                <div class="mt-5">
                    <h4 class="fw-bold mb-1">Importer des clients</h4>
                    <p class="text-muted mb-3">Importez rapidement plusieurs clients depuis un fichier.</p>

                    <div class="card border-0 bg-light-subtle rounded-4">
                        <div class="card-body p-3">
                            <form>
                                <div class="mb-3">
                                    <label for="importFile" class="form-label fw-semibold">Choisir un fichier</label>
                                    <input type="file" class="form-control" id="importFile">
                                </div>
                                <button type="submit" class="btn btn-secondary rounded-pill px-4">
                                    <i class="fas fa-file-import me-2"></i>Importer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL AJOUT CLIENT -->
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">
                        Ajouter un client
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="customerForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="address" id="address" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="contact" id="contact" class="form-control" required>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">
                            Annuler
                        </button>

                        <button type="submit" class="btn btn-success rounded-pill" id="submitBtn">
                            Ajouter
                        </button>
                    </div>
                </form>

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

        .bg-light-subtle {
            background: #f8fafc;
        }
    </style>
    <div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-trash text-danger fs-1"></i>
                    </div>

                    <h5 class="fw-bold mb-2">Confirmation de suppression</h5>

                    <p id="deleteCustomerText" class="text-muted mb-0">
                        Voulez-vous vraiment supprimer ce client ?
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
        .dropdown-menu {
    z-index: 10000;
    animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
    </style>
    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Ajouter un client';
            document.getElementById('submitBtn').textContent = 'Ajouter';

            const form = document.getElementById('customerForm');
            form.action = "/Customer";

            document.getElementById('formMethod').value = 'POST';

            document.getElementById('name').value = '';
            document.getElementById('address').value = '';
            document.getElementById('contact').value = '';
        }

        function openEditModal(id, name, address, contact) {
            document.getElementById('modalTitle').textContent = 'Modifier le client';
            document.getElementById('submitBtn').textContent = 'Mettre à jour';

            const form = document.getElementById('customerForm');
            form.action = "/Customer/" + id;

            document.getElementById('formMethod').value = 'PUT';

            document.getElementById('name').value = name;
            document.getElementById('address').value = address;
            document.getElementById('contact').value = contact;
        }
        
        //confirm delet

        function setDeleteCustomer(id, name) {
            document.getElementById('deleteCustomerForm').action = '/Customer/' + id;
            document.getElementById('deleteCustomerText').innerText =
                'Voulez-vous vraiment supprimer le client : ' + name + ' ?';
        }
    </script>

@endsection
