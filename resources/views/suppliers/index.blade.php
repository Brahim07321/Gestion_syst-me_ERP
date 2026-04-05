@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <!-- TITRE -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <h2 class="fw-bold mb-1">Gestion des fournisseurs</h2>
    
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
                        <a href="{{ route('suppliers.template') }}"
                           class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                            <i class="fas fa-file-download text-dark"></i>
                            Télécharger le modèle
                        </a>
                    </li>
    
                    <li>
                        <a href="{{ route('suppliers.export', request()->query()) }}"
                           class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                            <i class="fas fa-file-excel text-success"></i>
                            Exporter Excel
                        </a>
                    </li>
    
                    <li>
                        <button type="button"
                                class="dropdown-item rounded-3 d-flex align-items-center gap-2"
                                data-bs-toggle="modal"
                                data-bs-target="#importSupplierModal">
                            <i class="fas fa-file-import text-primary"></i>
                            Importer Excel
                        </button>
                    </li>
    
                </ul>
    
            </div>
        </div>
    
    </div>

    <!-- ALERTES -->
    <div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            Ajoutez, importez, exportez et consultez facilement les informations de vos fournisseurs.
        </p>
    </div>

    @if(session('warning'))
    <div class="alert alert-warning border-0 shadow-sm rounded-4">
        {{ session('warning') }}

        @if(session('skipped_suppliers'))
            <hr>
            <strong>Fournisseurs ignorés :</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('skipped_suppliers') as $supplierName)
                    <li>{{ $supplierName }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
    @if(session('message'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session('success'))
    <div class="alert border-0 rounded-4 shadow-sm d-flex align-items-center justify-content-between px-3 py-3 mb-4"
        style="background: #ecfdf5; color: #065f46;">

        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-check-circle fs-5"></i>
            <span class="fw-semibold">{{ session('success') }}</span>
        </div>

        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    </div>
@endif



    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-4">
        {{ session('error') }}

        @if(session('skipped_suppliers'))
            <hr>
            <strong>Fournisseurs ignorés :</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('skipped_suppliers') as $supplierName)
                    <li>{{ $supplierName }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif

    <!-- CONTENEUR PRINCIPAL -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <!-- FORMULAIRE -->
            <div class="mb-4">
                <h4 class="fw-bold mb-1">Ajouter un fournisseur</h4>
                <p class="text-muted mb-0">Renseignez les informations nécessaires pour enregistrer un nouveau fournisseur.</p>
            </div>

        


            <!-- FORM AJOUT -->
            <div class="card border-1 bg-light-subtle rounded-4 mb-4">
                <div class="card-body p-3">
                    <form method="POST" action="{{ route('suppliers.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Nom</label>
                                <input type="text" name="name" class="form-control" placeholder="Nom du fournisseur" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <input type="text" name="phone" class="form-control" placeholder="Téléphone">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Adresse email">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Adresse</label>
                                <input type="text" name="address" class="form-control" placeholder="Adresse du fournisseur">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                <i class="fas fa-plus me-2"></i>Ajouter le fournisseur
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABLEAU -->
            <div>
                <h4 class="fw-bold mb-1">Liste des fournisseurs</h4>
                <p class="text-muted mb-3">Consultez tous les fournisseurs enregistrés dans le système.</p>
            </div>

            <div class="table-responsive suppliers-table">
                <table class="table align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Adresse</th>
                            @if(Auth::user()->role === 'admin')
                            <th>Action</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td class="fw-semibold">{{ $supplier->id }}</td>
                                <td class="fw-semibold text-primary">{{ $supplier->name }}</td>
                                <td>{{ $supplier->phone ?: '-' }}</td>
                                <td>{{ $supplier->email ?: '-' }}</td>
                                <td>{{ $supplier->address ?: '-' }}</td>
                                <td>
                                    @if(Auth::user()->role === 'admin')
 
                                    <button type="button"
                                            class="btn btn-danger btn-sm rounded-pill px-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteSupplierModal"
                                            onclick="setDeleteSupplier('{{ $supplier->id }}', '{{ $supplier->name }}')">
                                        <i class="fas fa-trash me-1"></i>Supprimer
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Aucun fournisseur trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                            <!-- IMPORT -->

            </div>

        </div>
    </div>
</div>

<!-- MODAL SUPPRESSION -->
<div class="modal fade" id="deleteSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">

            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-trash text-danger fs-1"></i>
                </div>

                <h5 class="fw-bold mb-2">Confirmation de suppression</h5>

                <p id="deleteSupplierText" class="text-muted mb-0">
                    Voulez-vous vraiment supprimer ce fournisseur ?
                </p>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Annuler
                    </button>

                    <form id="deleteSupplierForm" method="POST">
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

<div class="modal fade" id="importSupplierModal" tabindex="-1" aria-labelledby="importSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">

            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="importSupplierModalLabel">Importer des fournisseurs</h5>
                    <p class="text-muted small mb-0">
                        Sélectionnez un fichier Excel pour importer les fournisseurs.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <form id="importForm" action="{{ route('suppliers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fichier Excel</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Progression</label>
                        <div class="progress" style="height: 10px;">
                            <div id="progressBar" class="progress-bar" style="width: 0%">0%</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Annuler
                    </button>

                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-file-import me-2"></i>Importer
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<style>
    .suppliers-table {
        overflow: hidden;
        background: #ffffff;
    }

    .suppliers-table table th,
    .suppliers-table table td {
        vertical-align: middle;
        white-space: normal;
        word-break: break-word;
    }

    .suppliers-table tbody tr {
        transition: all 0.2s ease;
    }

    .suppliers-table tbody tr:hover {
        background: #f8fafc;
    }

    .bg-light-subtle {
        background: #f8fafc;
    }

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
    function setDeleteSupplier(id, name) {
        document.getElementById('deleteSupplierForm').action = '/suppliers/' + id;
        document.getElementById('deleteSupplierText').innerText =
            'Voulez-vous vraiment supprimer le fournisseur : ' + name + ' ?';
    }

    document.getElementById('importForm').addEventListener('submit', function() {
        let bar = document.getElementById('progressBar');
        let width = 0;

        let interval = setInterval(() => {
            if (width >= 90) {
                clearInterval(interval);
            } else {
                width += 10;
                bar.style.width = width + "%";
                bar.innerHTML = width + "%";
            }
        }, 100);
    });

document.getElementById('importForm').addEventListener('submit', function () {
    let bar = document.getElementById('progressBar');
    let width = 0;

    let interval = setInterval(() => {
        if (width >= 90) {
            clearInterval(interval);
        } else {
            width += 10;
            bar.style.width = width + "%";
            bar.innerHTML = width + "%";
        }
    }, 120);
});

         //alert 
         setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                el.style.transition = "0.4s";
                el.style.opacity = "0";
                setTimeout(() => el.remove(), 400);
            });
        }, 3000);
</script>

@endsection