@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h2 class="fw-bold mb-1">Gestion des utilisateurs</h2>
            <p class="text-muted mb-0">
                Consultez, ajoutez et gérez les accès au système.
            </p>
        </div>

        <a href="{{ route('users.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-user-plus me-2"></i>Ajouter un utilisateur
        </a>
    </div>

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

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <div class="table-responsive users-table">
                <table class="table align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Date création</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="fw-semibold">{{ $user->id }}</td>

                                <td class="fw-semibold text-primary">
                                    {{ $user->name }}
                                </td>

                                <td>{{ $user->email }}</td>

                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge rounded-pill px-3 py-2 bg-danger-subtle text-danger">
                                            Administrateur
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-2 bg-primary-subtle text-primary">
                                            Utilisateur
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="{{ route('users.edit', $user->id) }}"
                                           class="btn btn-warning btn-sm rounded-pill px-3 text-white">
                                            <i class="fas fa-edit me-1"></i>Modifier
                                        </a>
                                    
                                        <button type="button"
                                                class="btn btn-danger btn-sm rounded-pill px-3"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal"
                                                onclick="setDeleteUser('{{ $user->id }}', '{{ $user->name }}')">
                                            <i class="fas fa-trash me-1"></i>Supprimer
                                        </button>
                                    </div>                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">

            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-trash text-danger fs-1"></i>
                </div>

                <h5 class="fw-bold mb-2">Confirmation de suppression</h5>

                <p id="deleteUserText" class="text-muted mb-0">
                    Voulez-vous vraiment supprimer cet utilisateur ?
                </p>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button"
                            class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">
                        Annuler
                    </button>

                    <form id="deleteUserForm" method="POST">
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
    .users-table {
        overflow: hidden;
        background: #ffffff;
    }

    .users-table table th,
    .users-table table td {
        vertical-align: middle;
        white-space: normal;
        word-break: break-word;
    }

    .users-table tbody tr {
        transition: all 0.2s ease;
    }

    .users-table tbody tr:hover {
        background: #f8fafc;
    }

    .bg-danger-subtle {
        background: #fee2e2;
    }

    .bg-primary-subtle {
        background: #dbeafe;
    }
</style>

<script>
    function setDeleteUser(id, name) {
        document.getElementById('deleteUserForm').action = '/users/' + id;
        document.getElementById('deleteUserText').innerText =
            'Voulez-vous vraiment supprimer l\'utilisateur : ' + name + ' ?';
    }
</script>
@endsection