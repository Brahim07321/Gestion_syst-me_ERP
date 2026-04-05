<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Inventory System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.14), transparent 30%),
                radial-gradient(circle at bottom right, rgba(99, 102, 241, 0.12), transparent 30%),
                #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-card {
            width: 100%;
            max-width: 980px;
            background: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.10);
            border: 1px solid #e2e8f0;
        }

        .login-left {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #ffffff;
            padding: 48px 38px;
            height: 100%;
            position: relative;
        }

        .login-left::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.16), transparent 30%);
            pointer-events: none;
        }

        .brand-badge {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin-bottom: 24px;
            backdrop-filter: blur(6px);
        }

        .login-left h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .login-left p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .login-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            color: rgba(255, 255, 255, 0.92);
            font-size: 14px;
        }

        .login-feature i {
            width: 20px;
            text-align: center;
        }

        .login-right {
            padding: 48px 38px;
            background: #ffffff;
        }

        .login-title {
            font-size: 30px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #64748b;
            margin-bottom: 28px;
            font-size: 14px;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-group-text {
            background: #f8fafc;
            border: 1px solid #dbe2ea;
            color: #64748b;
            border-right: 0;
            border-radius: 16px 0 0 16px;
        }

        .form-control {
            border: 1px solid #dbe2ea;
            border-left: 0;
            border-radius: 0 16px 16px 0;
            padding: 12px 14px;
            box-shadow: none !important;
        }

        .form-control.no-icon {
            border-left: 1px solid #dbe2ea;
            border-radius: 16px;
        }

        .form-control:focus {
            border-color: #93c5fd;
        }

        .input-group:focus-within .input-group-text {
            border-color: #93c5fd;
        }

        .login-btn {
            border: 0;
            border-radius: 999px;
            padding: 13px 18px;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #ffffff;
            transition: all 0.2s ease;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.20);
            color: #ffffff;
        }

        .forgot-link {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }

        .forgot-link:hover {
            color: #1d4ed8;
        }

        .remember-label {
            color: #475569;
            font-size: 14px;
        }

        .alert-custom {
            border: 0;
            border-radius: 18px;
            padding: 14px 16px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .footer-note {
            margin-top: 18px;
            color: #94a3b8;
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 991px) {
            .login-left {
                padding: 34px 28px;
            }

            .login-right {
                padding: 34px 28px;
            }

            .login-title {
                font-size: 26px;
            }
        }
    </style>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="row g-0">
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="login-left">
                        <div class="brand-badge">
                            <i class="fas fa-boxes-stacked"></i>
                        </div>

                        <h1>Inventory System</h1>
                        <p>
                            Gérez facilement vos produits, achats, factures, fournisseurs et mouvements de stock
                            depuis une seule interface moderne.
                        </p>

                        <div class="login-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Suivi précis du stock</span>
                        </div>

                        <div class="login-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Gestion des achats et fournisseurs</span>
                        </div>

                        <div class="login-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Facturation et rapports simplifiés</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="login-right">
                        <div class="login-title">Connexion</div>
                        <div class="login-subtitle">
                            Connectez-vous pour accéder à votre espace de gestion.
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success alert-custom">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-custom">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse e-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input id="email"
                                           type="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           required
                                           autofocus
                                           autocomplete="username"
                                           class="form-control"
                                           placeholder="Entrez votre adresse e-mail">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input id="password"
                                           type="password"
                                           name="password"
                                           required
                                           autocomplete="current-password"
                                           class="form-control"
                                           placeholder="Entrez votre mot de passe">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                                    <label class="form-check-label remember-label" for="remember_me">
                                        Se souvenir de moi
                                    </label>
                                </div>

                                @if (Route::has('password.request'))
                                    <a class="forgot-link" href="{{ route('password.request') }}">
                                        Mot de passe oublié ?
                                    </a>
                                @endif
                            </div>

                            <button type="submit" class="btn login-btn w-100">
                                <i class="fas fa-right-to-bracket me-2"></i>Se connecter
                            </button>
                        </form>

                        <div class="footer-note">
                            Accès réservé aux utilisateurs autorisés.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>