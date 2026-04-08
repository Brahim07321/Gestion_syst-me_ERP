@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">

        <!-- TITRE -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

            <h2 class="fw-bold mb-1">Archives des factures</h2>

            <!-- ACTIONS RIGHT -->
            <div class="ms-md-auto">

                <div class="dropdown ">

                    <button class="btn btn-light rounded-pill px-3" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2">

                        <li>
                            <a href="{{ route('factures.export.excel', request()->query()) }}"
                                class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                                <i class="fas fa-file-excel text-success"></i>
                                Exporter Excel
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('factures.export.pdf', request()->query()) }}"
                                class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                                <i class="fas fa-file-pdf text-danger"></i>
                                Exporter PDF
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a href="{{ route('factures.create') }}"
                                class="dropdown-item rounded-3 d-flex align-items-center gap-2">
                                <i class="fas fa-plus text-primary"></i>
                                Nouvelle facture
                            </a>
                        </li>

                    </ul>

                </div>

            </div>

        </div>
        <p class="text-muted mb-4">
            Consultez, filtrez et suivez toutes les factures enregistrées dans le système.
        </p>

        <!-- ALERTES -->
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
            <div class="alert alert-danger shadow-sm rounded-4">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <!-- FILTRES -->
                <div class="card bg-light-subtle rounded-4 mb-4">
                    <div class="card-body p-3">

                        <form action="{{ route('factures.index') }}" method="GET" class="row g-3 align-items-end">

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Recherche</label>
                                <input type="text" name="search" class="form-control" placeholder="Code, client..."
                                    value="{{ request('search') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Statut</label>
                                <select name="status" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="payée" {{ request('status') == 'payée' ? 'selected' : '' }}>Payée
                                    </option>
                                    <option value="non payée" {{ request('status') == 'non payée' ? 'selected' : '' }}>Non
                                        payée
                                    </option>
                                    <option value="partiellement payée"
                                        {{ request('status') == 'partiellement payée' ? 'selected' : '' }}>Partielle
                                    </option>
                                    <option value="annulée" {{ request('status') == 'annulée' ? 'selected' : '' }}>Annulée
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Date début</label>
                                <input type="date" name="date_from" class="form-control"
                                    value="{{ request('date_from') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Date fin</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>

                            <div class="col-md-1 d-flex gap-1">
                                <button class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>

                            <div class="col-md-2">
                                <a href="{{ route('factures.index') }}" class="btn btn-secondary w-100 rounded-pill">
                                    Reset
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- STATS -->
                <div class="row mb-4 g-3">

                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 rounded-4 p-3">
                            <h6 class="text-muted mb-1">Total Factures</h6>
                            <h4 class="fw-bold text-primary">{{ $totalFactures }}</h4>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 rounded-4 p-3">
                            <h6 class="text-muted mb-1">Montant Total</h6>
                            <h4 class="fw-bold">{{ number_format($totalAmount, 2) }} MAD</h4>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 rounded-4 p-3">
                            <h6 class="text-muted mb-1">Total Payé</h6>
                            <h4 class="fw-bold text-success">{{ number_format($totalPaid, 2) }} MAD</h4>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 rounded-4 p-3">
                            <h6 class="text-muted mb-1">Reste</h6>
                            <h4 class="fw-bold text-danger">{{ number_format($totalRemaining, 2) }} MAD</h4>
                        </div>
                    </div>

                </div>

                <!-- TABLE -->
                <div class="table-responsive archive-table">
                    <table class="table align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Client</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payé</th>
                                <th>Reste</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($factures as $facture)
                                <tr class="{{ $facture->status == 'annulée' ? 'table-secondary' : '' }}">
                                    <td>{{ $facture->id }}</td>
                                    <td class="fw-bold text-primary">{{ $facture->code_facture }}</td>
                                    <td>{{ $facture->client_name }}</td>
                                    <td>{{ number_format($facture->total, 2) }} MAD</td>

                                    <td>
                                        @if ($facture->status == 'payée')
                                            <span class="badge bg-success rounded-pill px-3">Payée</span>
                                        @elseif($facture->status == 'partiellement payée')
                                            <span class="badge bg-warning text-dark rounded-pill px-3">Partielle</span>
                                        @elseif($facture->status == 'annulée')
                                            <span class="badge bg-dark rounded-pill px-3">Annulée</span>
                                        @else
                                            <span class="badge bg-danger rounded-pill px-3">Non payée</span>
                                        @endif
                                    </td>

                                    <td class="text-success fw-semibold">{{ number_format($facture->paid_amount, 2) }}</td>
                                    <td class="text-danger fw-semibold">{{ number_format($facture->remaining_amount, 2) }}
                                    </td>

                                    <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>

                                    <td>
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                    
                                            <!-- 👁️ VIEW -->
                                            <a href="{{ route('factures.show', $facture->id) }}"
                                                class="btn btn-sm btn-primary rounded-pill px-3">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                    
                                            @if (Auth::user()->role === 'admin')
                                                <div class="position-relative action-menu-wrapper">
                                    
                                                    <!-- ... BUTTON -->
                                                    <button type="button"
                                                        class="btn btn-sm btn-light rounded-circle action-menu-btn"
                                                        onclick="toggleFactureMenu(event, 'menu-{{ $facture->id }}')">
                                                        <i class="fas fa-ellipsis-h"></i>
                                                    </button>
                                    
                                                    <!-- MENU -->
                                                    <div id="menu-{{ $facture->id }}" class="facture-action-menu shadow-sm">
                                    
                                                        {{-- ✏️ EDIT --}}
                                                        @if ($facture->status != 'annulée' && !$facture->trashed())
                                                        <button type="button"
                                                        class="dropdown-action-btn text-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editFactureModal"
                                                        onclick="setEditFacture('{{ $facture->id }}','{{ $facture->code_facture }}'); closeAllFactureMenus();">
                                                        <i class="fas fa-pen me-2"></i>Modifier
                                                    </button>
                                                        @endif
                                    
                                                        {{-- 🚫 ANNULER --}}
                                                        @if (!in_array($facture->status, ['annulée', 'payée']))
                                                            <button type="button"
                                                                class="dropdown-action-btn text-warning"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#cancelFactureModal"
                                                                onclick="setCancelFacture('{{ $facture->id }}','{{ $facture->code_facture }}'); closeAllFactureMenus();">
                                                                <i class="fas fa-ban me-2"></i>Annuler
                                                            </button>
                                                        @endif
                                                        
                                    
                                                        {{-- 🗑️ DELETE --}}
                                                        <button type="button"
                                                            class="dropdown-action-btn text-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteFactureModal"
                                                            onclick="setDeleteFacture('{{ $facture->id }}','{{ $facture->code_facture }}'); closeAllFactureMenus();">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </button>
                                    
                                                    </div>
                                                </div>
                                            @endif
                                    
                                        </div>
                                    </td>                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-muted py-4">Aucune facture</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="mt-4 d-flex justify-content-center">
                    {{ $factures->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="cancelFactureModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 text-center p-4">

                <h5 class="fw-bold mb-2">Confirmation</h5>
                <p id="cancelFactureText"></p>

                <div class="d-flex justify-content-center gap-3 mt-3">
                    <button class="btn btn-light" data-bs-dismiss="modal">Non</button>

                    <form id="cancelFactureForm" method="POST">
                        @csrf
                        <button class="btn btn-warning">Oui</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteFactureModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 text-center p-4 border-0 shadow">

                <div class="mb-3">
                    <i class="fas fa-trash text-danger fs-1"></i>
                </div>

                <h5 class="fw-bold mb-2">Confirmation de suppression</h5>
                <p id="deleteFactureText" class="text-muted mb-0"></p>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Non
                    </button>

                    <form id="deleteFactureForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger rounded-pill px-4">
                            Oui, supprimer
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="editFactureModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 text-center p-4 border-0 shadow">
    
                <div class="mb-3">
                    <i class="fas fa-pen text-success fs-1"></i>
                </div>
    
                <h5 class="fw-bold mb-2">Confirmation de modification</h5>
                <p id="editFactureText" class="text-muted mb-0"></p>
    
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Non
                    </button>
    
                    <a id="editFactureLink" class="btn btn-success rounded-pill px-4">
                        Oui, modifier
                    </a>
                </div>
    
            </div>
        </div>
    </div>

    <script>

function setEditFacture(id, code) {
    document.getElementById('editFactureText').innerText =
        'Modifier facture : ' + code + ' ?';

    document.getElementById('editFactureLink').href =
        '/factures/' + id + '/edit';
}
        function setCancelFacture(id, code) {
            document.getElementById('cancelFactureForm').action = '/factures/cancel/' + id;
            document.getElementById('cancelFactureText').innerText =
                'Annuler facture : ' + code + ' ?';
        }

        function setCancelFacture(id, code) {
            document.getElementById('cancelFactureForm').action = '/factures/cancel/' + id;
            document.getElementById('cancelFactureText').innerText =
                'Annuler facture : ' + code + ' ?';
        }

        function setDeleteFacture(id, code) {
            document.getElementById('deleteFactureForm').action = '/factures/' + id;
            document.getElementById('deleteFactureText').innerText =
                'Supprimer facture : ' + code + ' ?';
        }

        function toggleFactureMenu(event, menuId) {
            event.stopPropagation();
            closeAllFactureMenus();

            const menu = document.getElementById(menuId);
            menu.classList.toggle('show');
        }

        function closeAllFactureMenus() {
            document.querySelectorAll('.facture-action-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        document.addEventListener('click', function() {
            closeAllFactureMenus();
        });

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                el.style.transition = "0.4s";
                el.style.opacity = "0";
                setTimeout(() => el.remove(), 400);
            });
        }, 3000);
    </script>

    <style>
        .archive-table tbody tr:not(.table-secondary):hover {
            background: #f8fafc;
        }


        .action-menu-btn {
            width: 36px;
            height: 36px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-menu-btn:hover {
            background: #f8fafc;
        }

        .facture-action-menu {
            position: absolute;
            top: 42px;
            right: 0;
            min-width: 170px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 8px;
            display: none;
            z-index: 1000;
        }

        .facture-action-menu.show {
            display: block;
        }

        .dropdown-action-btn {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            padding: 10px 12px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .dropdown-action-btn:hover {
            background: #f8fafc;
        }

        .dropdown {
            background: var(--bs-btn-hover-bg);
        }

        .dropdown-menu {
            min-width: 220px;
            animation: fadeIn 0.2s ease-in-out;
        }

        .dropdown-item:hover {
            background: #f1f5f9;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

@endsection
