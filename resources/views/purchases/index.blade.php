@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">

        <!-- TITRE -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

            <h2 class="fw-bold mb-1">Gestion des achats</h2>
        
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
                            <a href="{{ route('purchases.export.excel', request()->query()) }}"
                               class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                                <i class="fas fa-file-excel text-success"></i>
                                Exporter Excel
                            </a>
                        </li>
        
                        <li>
                            <a href="{{ route('purchases.export.pdf', request()->query()) }}"
                               class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                                <i class="fas fa-file-pdf text-danger"></i>
                                Exporter PDF
                            </a>
                        </li>
        
                        <li><hr class="dropdown-divider"></li>
        
                        <li>
                            <a href="{{ route('purchases.create') }}"
                               class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                                <i class="fas fa-plus text-primary"></i>
                                Nouvel achat
                            </a>
                        </li>
        
                        <li>
                            <button type="button"
                                    class="dropdown-item rounded-3 d-flex align-items-center gap-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#importPurchaseModal">
                                <i class="fas fa-file-import text-dark"></i>
                                Importer Excel
                            </button>
                        </li>
        
                    </ul>
        
                </div>
            </div>
        
        </div>


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

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-4">
                {{ session('error') }}
            </div>
        @endif


        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <p class="text-muted mb-0">


                Consultez, filtrez et gérez l’ensemble des bons d’achat enregistrés.
            </p>
        </div>


        <!-- CONTENEUR PRINCIPAL -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <!-- HEADER -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">Liste des achats</h4>
                        <p class="text-muted mb-0">Suivez les statuts, les fournisseurs et les montants de vos achats.</p>
                    </div>

                </div>

                <!-- FILTRES -->
                <div class="card border-1 bg-light-subtle rounded-4 mb-4">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('purchases.index') }}" class="row g-3 align-items-end">

                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Recherche</label>
                                <input type="text" name="search" class="form-control"
                                    placeholder="Rechercher par code ou fournisseur..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold d-block invisible">Rechercher</label>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                    <i class="fas fa-search me-1"></i>Rechercher
                                </button>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Statut</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">Tous les statuts</option>
                                    <option value="reçu" {{ request('status') == 'reçu' ? 'selected' : '' }}>Reçu</option>
                                    <option value="en attente" {{ request('status') == 'en attente' ? 'selected' : '' }}>En
                                        attente</option>
                                    <option value="annulé" {{ request('status') == 'annulé' ? 'selected' : '' }}>Annulé
                                    </option>
                                </select>
                            </div>



                            <div class="col-md-2">
                                <label class="form-label fw-semibold d-block invisible">Reset</label>
                                <a href="{{ route('purchases.index') }}" class="btn btn-secondary w-100 rounded-pill">
                                    Réinitialiser
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- TABLEAU -->

                <div class="table-responsive purchases-table ">
                    <table class="table align-middle border-1 text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Code d'achat</th>
                                <th>Fournisseur</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($purchases as $purchase)
                                <tr>
                                    <td class="fw-semibold">{{ $purchase->id }}</td>
                                    <td class="fw-semibold text-primary">{{ $purchase->purchase_code }}</td>
                                    <td>{{ $purchase->supplier->name ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                                    <td class="fw-semibold">{{ number_format($purchase->total, 2) }} MAD</td>

                                    <td>
                                        @if ($purchase->status == 'reçu')
                                            <span class="badge rounded-pill px-3 py-2 bg-success-subtle text-success">
                                                Reçu
                                            </span>
                                        @elseif($purchase->status == 'annulé')
                                            <span class="badge rounded-pill px-3 py-2 bg-danger-subtle text-danger">
                                                Annulé
                                            </span>
                                        @else
                                            <div
                                                class="d-flex flex-column flex-md-row gap-2 justify-content-center align-items-center">
                                                <button
                                                    onclick="openActionModal('receive', '{{ $purchase->id }}', '{{ $purchase->purchase_code }}')"
                                                    class="btn btn-warning btn-sm rounded-pill px-3">
                                                    Marquer reçu
                                                </button>

                                                <button
                                                    onclick="openActionModal('cancel', '{{ $purchase->id }}', '{{ $purchase->purchase_code }}')"
                                                    class="btn btn-secondary btn-sm rounded-pill px-3">
                                                    Annuler
                                                </button>
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                                            <a href="{{ route('purchases.show', $purchase->id) }}"
                                                class="btn btn-sm btn-primary rounded-pill px-3">
                                                <i class="fas fa-eye me-1"></i>Voir
                                            </a>






                                            @auth
                                                @if (Auth::user()->role === 'admin')
                                                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3"
                                                        onclick="openActionModal('delete', '{{ $purchase->id }}', '{{ $purchase->purchase_code }}')">
                                                        <i class="fas fa-trash me-1"></i>Supprimer
                                                    </button>
                                                @endif
                                            @endauth
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Aucun achat trouvé.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="mt-4 d-flex justify-content-center">
                    {{ $purchases->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL SUPPRESSION -->
    <div class="modal fade" id="actionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-body text-center p-4">

                    <div class="mb-3">
                        <i id="modalIcon" class="fs-1"></i>
                    </div>

                    <h5 id="modalTitle" class="fw-bold mb-2"></h5>

                    <p id="modalText" class="text-muted mb-0"></p>

                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Annuler
                        </button>

                        <form id="modalForm" method="POST">
                            @csrf
                            <input type="hidden" name="_method" id="modalMethod">

                            <button type="submit" id="modalBtn" class="btn rounded-pill px-4">
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="importPurchaseModal" tabindex="-1" aria-labelledby="importPurchaseModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="importPurchaseModalLabel">Importer un fichier d’achat</h5>
                        <p class="text-muted small mb-0">
                            Téléversez un fichier Excel fournisseur pour analyser automatiquement les lignes d’achat.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <form action="{{ route('purchases.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fournisseur</label>
                                <select name="supplier_id" class="form-select" required>
                                    <option value="">Choisir un fournisseur</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date d’achat</label>
                                <input type="date" name="purchase_date" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut</label>
                                <select name="status" class="form-select" required>
                                    <option value="reçu">Reçu</option>
                                    <option value="en attente">En attente</option>
                                    <option value="annulé">Annulé</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fichier Excel</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                            </div>

                            <div class="col-12">
                                <div class="alert alert-light border rounded-4 mb-0">
                                    <strong>Remarque :</strong>
                                    le système va analyser automatiquement le tableau du fournisseur, détecter les colonnes
                                    utiles, comparer les prix et afficher un aperçu avant validation.
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Fermer
                        </button>

                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-search me-2"></i>Analyser le fichier
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <style>
        .purchases-table {
            overflow: hidden;
            background: #ffffff;
        }

        .purchases-table table th,
        .purchases-table table td {
            vertical-align: middle;
            white-space: normal;
            word-break: break-word;
        }

        .purchases-table tbody tr {
            transition: all 0.2s ease;
        }

        .purchases-table tbody tr:hover {
            background: #f8fafc;
        }

        .bg-light-subtle {
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
        function openActionModal(type, id, code) {

            const form = document.getElementById('modalForm');
            const method = document.getElementById('modalMethod');
            const text = document.getElementById('modalText');
            const title = document.getElementById('modalTitle');
            const btn = document.getElementById('modalBtn');
            const icon = document.getElementById('modalIcon');

            // RESET
            btn.className = 'btn rounded-pill px-4';
            icon.className = 'fs-1';

            if (type === 'delete') {
                form.action = '/purchases/' + id;
                method.value = 'DELETE';

                title.innerText = 'Suppression';
                text.innerText = 'Voulez-vous supprimer l\'achat : ' + code + ' ?';

                btn.innerText = 'Oui, supprimer';
                btn.classList.add('btn-danger');

                icon.classList.add('fas', 'fa-trash', 'text-danger');

            } else if (type === 'cancel') {
                form.action = '/purchases/cancel/' + id;
                method.value = 'POST';

                title.innerText = 'Annulation';
                text.innerText = 'Voulez-vous annuler l\'achat : ' + code + ' ?';

                btn.innerText = 'Oui, annuler';
                btn.classList.add('btn-warning');

                icon.classList.add('fas', 'fa-exclamation-triangle', 'text-warning');

            } else if (type === 'receive') {
                form.action = '/purchases/status/' + id;
                method.value = 'POST';

                title.innerText = 'Confirmation';
                text.innerText = 'Marquer comme reçu : ' + code + ' ?';

                btn.innerText = 'Oui, confirmer';
                btn.classList.add('btn-success');

                icon.classList.add('fas', 'fa-check-circle', 'text-success');
            }

            new bootstrap.Modal(document.getElementById('actionModal')).show();
        }

        function setReceivePurchase(id, code) {
            document.getElementById('receivePurchaseForm').action = '/purchases/status/' + id;
            document.getElementById('receivePurchaseText').innerText =
                'Voulez-vous confirmer la réception de l\'achat : ' + code + ' ?';
        }

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
