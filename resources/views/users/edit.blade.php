@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <div class="mb-4">
        <h2 class="fw-bold mb-1">Modifier un utilisateur</h2>
        <p class="text-muted mb-0">
            Mettez à jour les informations et les accès de l’utilisateur.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 rounded-4 h-100" style="background:#f8fafc;">
                        <div class="card-body p-4">
                            <div class="mb-3"
                                 style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;">
                                <i class="fas fa-user-pen"></i>
                            </div>

                            <h4 class="fw-bold mb-2">Modification du compte</h4>
                            <p class="text-muted mb-4">
                                Modifiez le nom, l’adresse e-mail, le rôle ou le mot de passe de cet utilisateur.
                            </p>

                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:38px;height:38px;border-radius:12px;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Informations du compte</div>
                                    <small class="text-muted">Nom et e-mail de connexion</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:38px;height:38px;border-radius:12px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Rôle d’accès</div>
                                    <small class="text-muted">Admin ou utilisateur standard</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <div style="width:38px;height:38px;border-radius:12px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Mot de passe</div>
                                    <small class="text-muted">Laissez vide pour ne pas modifier</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom complet</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   class="form-control rounded-4"
                                   placeholder="Entrez le nom complet"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Adresse e-mail</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   class="form-control rounded-4"
                                   placeholder="Entrez l'adresse e-mail"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rôle</label>
                            <select name="role" class="form-select rounded-4" required>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                    Administrateur
                                </option>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>
                                    Utilisateur
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nouveau mot de passe</label>
                            <input type="password"
                                   name="password"
                                   class="form-control rounded-4"
                                   placeholder="Laissez vide pour garder le mot de passe actuel">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                            <input type="password"
                                   name="password_confirmation"
                                   class="form-control rounded-4"
                                   placeholder="Confirmez le nouveau mot de passe">
                        </div>

                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill px-4">
                                Annuler
                            </a>

                            <button type="submit" class="btn btn-warning rounded-pill px-4 text-white">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection