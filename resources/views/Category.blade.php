@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">

        <!-- TITRE -->
        <div class="mb-4">
            <h2 class="fw-bold mb-1">Gestion des catégories</h2>
            <p class="text-muted mb-0">
                Ajoutez, recherchez et gérez facilement les catégories de vos produits.
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

        <!-- CONTENU PRINCIPAL -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <!-- HEADER -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">Liste des catégories</h4>
                        <p class="text-muted mb-0">Consultez et organisez les catégories disponibles.</p>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        @if (Auth::user()->role === 'admin')

                        <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal"
                            data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus me-2"></i>Ajouter une catégorie
                        </button>
                        @endif


                    </div>
                </div>

                <!-- RECHERCHE -->
                <div class="card border-1 bg-light-subtle rounded-4 mb-4">
                    <div class="card-body p-3">
                        <form action="{{ url('/Category') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Recherche</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Rechercher une catégorie..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold d-block invisible">Reset</label>
                                <a href="{{ url('/Category') }}" class="btn btn-secondary w-100 rounded-pill">
                                    Réinitialiser
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABLEAU -->
                <div class="table-responsive categories-table">
                    <table class="table align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nom de la catégorie</th>
                                @if (Auth::user()->role === 'admin')
                                <th>Action</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($Categorys as $Category)
                                <tr>
                                    <td class="fw-semibold">{{ $Category->id }}</td>
                                    <td class="fw-semibold text-primary">{{ $Category->Category }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                                            @if (Auth::user()->role === 'admin')
                                                <button class="btn btn-danger btn-sm rounded-pill px-3"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                    onclick="setDeleteId('{{ $Category->id }}','{{ $Category->Category }}')">
                                                    <i class="fas fa-trash"></i>
                                                    Supprimer
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        Aucune catégorie trouvée.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="mt-4 d-flex justify-content-center">
                    {{ $Categorys->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL AJOUT -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">

                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="addCategoryModalLabel">Ajouter une catégorie</h5>
                        <p class="text-muted small mb-0">Saisissez les informations de la nouvelle catégorie.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <form id="addCategoryForm" action="/Category" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label fw-semibold">Nom</label>
                            <input type="text" class="form-control" id="categoryName" name="Category"
                                placeholder="Entrez le nom de la catégorie" required>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            Enregistrer
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- DELETE MODAL -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-body text-center p-4">

                    <div class="mb-3">
                        <i class="fas fa-trash text-danger fs-1"></i>
                    </div>

                    <h5 class="fw-bold mb-2">Confirmation</h5>

                    <p id="deleteText" class="text-muted">
                        Voulez-vous vraiment supprimer ?
                    </p>

                    <div class="d-flex justify-content-center gap-3 mt-4">

                        <button class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Annuler
                        </button>

                        <form id="deleteForm" method="POST">
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
    <script>
        function setDeleteId(id, name) {
            document.getElementById('deleteForm').action = '/Category/' + id;

            document.getElementById('deleteText').innerText =
                "Voulez-vous supprimer la catégorie : " + name + " ?";
        }
    </script>

    <style>
        .categories-table {
            overflow: hidden;
            background: #ffffff;
        }

        .categories-table table th,
        .categories-table table td {
            vertical-align: middle;
            white-space: normal;
            word-break: break-word;
        }

        .categories-table tbody tr {
            transition: all 0.2s ease;
        }

        .categories-table tbody tr:hover {
            background: #f8fafc;
        }

        .bg-light-subtle {
            background: #f8fafc;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
