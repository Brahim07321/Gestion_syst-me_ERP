@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <div class="mb-4">
        <h2 class="fw-bold mb-1">Ajouter un utilisateur</h2>
        <p class="text-muted mb-0">
            Créez un nouveau compte pour accéder au système.
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
                                 style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;">
                                <i class="fas fa-user-plus"></i>
                            </div>

                            <h4 class="fw-bold mb-2">Nouvel accès</h4>
                            <p class="text-muted mb-4">
                                Ajoutez un nouvel utilisateur pour lui permettre de se connecter au système en toute sécurité.
                            </p>

                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:38px;height:38px;border-radius:12px;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Adresse e-mail</div>
                                    <small class="text-muted">Utilisée pour la connexion</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:38px;height:38px;border-radius:12px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Mot de passe sécurisé</div>
                                    <small class="text-muted">Minimum 8 caractères recommandé</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <div style="width:38px;height:38px;border-radius:12px;background:#f3e8ff;color:#7c3aed;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Rôle utilisateur</div>
                                    <small class="text-muted">Administrateur ou utilisateur standard</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom complet</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   class="form-control rounded-4"
                                   placeholder="Entrez le nom complet"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Adresse e-mail</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="form-control rounded-4"
                                   placeholder="Entrez l'adresse e-mail"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rôle</label>
                            <select name="role" class="form-select rounded-4" required>
                                <option value="">Choisir un rôle</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Utilisateur</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mot de passe</label>
                            <input type="password"
                                   name="password"
                                   class="form-control rounded-4"
                                   placeholder="Entrez le mot de passe"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                            <input type="password"
                                   name="password_confirmation"
                                   class="form-control rounded-4"
                                   placeholder="Confirmez le mot de passe"
                                   required>
                        </div>

                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <a href="{{ url()->previous() }}" class="btn btn-light rounded-pill px-4">
                                Annuler
                            </a>

                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-save me-2"></i>Créer l'utilisateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection