<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    <title> Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Image de fond */
        .bg-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .bg-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .bg-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(19, 18, 18, 0.5);
            z-index: 1;
        }

        /* Conteneur principal */
        .container {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Carte de connexion */
        .login-card {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.35);
            width: 100%;
            max-width: 520px;
            padding: 45px 50px;
            backdrop-filter: blur(4px);
            animation: fadeInScale 0.6s ease-out;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(30px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Logo et titre */
        .logo-area {
            text-align: center;
            margin-bottom: 28px;
            animation: slideDown 0.6s ease-out 0.1s both;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #2563eb, #1a4fc4);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 32px;
            color: white;
            animation: pulseGlow 2s ease-in-out infinite;
        }

        @keyframes pulseGlow {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4);
            }

            50% {
                box-shadow: 0 0 0 20px rgba(37, 99, 235, 0);
            }
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 6px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
        }

        /* Messages d'erreur/succès */
        .session-status {
            background: #d4edda;
            color: #155724;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 22px;
            font-size: 13px;
            text-align: center;
            animation: slideRight 0.6s ease-out 0.15s both;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Groupes de formulaires */
        .form-group {
            margin-bottom: 20px;
            animation: fadeUp 0.5s ease-out both;
        }

        .form-group:nth-child(1) {
            animation-delay: 0.20s;
        }

        .form-group:nth-child(2) {
            animation-delay: 0.35s;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 13px;
        }

        label i {
            color: #2563eb;
            margin-right: 8px;
        }

        .input-wrapper {
            position: relative;
            transition: transform 0.3s ease;
        }

        .input-wrapper:hover {
            transform: translateX(3px);
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #2563eb;
            font-size: 16px;
            transition: all 0.3s ease;
            z-index: 1;
        }

        input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #faf9f6;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            transform: scale(1.01);
        }

        input:focus+i {
            color: #1a4fc4;
        }

        .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
            animation: shake 0.4s ease-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .error i {
            font-size: 12px;
        }

        /* Options */
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
            animation: fadeUp 0.5s ease-out 0.40s both;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #555;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .checkbox-label:hover {
            color: #1a1a1a;
        }

        .checkbox-label input {
            width: auto;
            padding: 0;
            margin: 0;
            accent-color: #2563eb;
            cursor: pointer;
        }

        .forgot-link {
            color: #2563eb;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .forgot-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #2563eb;
            transition: width 0.3s ease;
        }

        .forgot-link:hover::after {
            width: 100%;
        }

        .forgot-link:hover {
            color: #1a4fc4;
        }

        /* Bouton de connexion */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #1a4fc4);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            font-family: inherit;
            position: relative;
            overflow: hidden;
            animation: fadeUp 0.5s ease-out 0.55s both;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 60%);
            transform: scale(0);
            transition: transform 0.6s ease;
        }

        .btn-login:hover::after {
            transform: scale(1);
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 35px rgba(37, 99, 235, 0.45);
        }

        .btn-login:active {
            transform: scale(0.97);
        }

        .btn-login i {
            margin-right: 8px;
        }

        /* Lien d'inscription */
        .register-link {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
            color: #666;
            animation: fadeUp 0.5s ease-out 0.65s both;
        }

        .register-link a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .register-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #2563eb;
            transition: width 0.3s ease;
        }

        .register-link a:hover::after {
            width: 100%;
        }

        .register-link a:hover {
            color: #1a4fc4;
        }

        /* Footer */
        .card-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #eee;
            font-size: 13px;
            color: #888;
            animation: fadeUp 0.5s ease-out 0.75s both;
        }

        .card-footer i {
            color: #1a4fc4;
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f0ede8;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #2563eb;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #1a4fc4;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 580px) {
            .login-card {
                padding: 32px 24px;
                max-width: 100%;
            }

            h1 {
                font-size: 24px;
            }

            .logo-icon {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }

            .form-group:nth-child(1) {
                animation-delay: 0.15s;
            }

            .form-group:nth-child(2) {
                animation-delay: 0.25s;
            }

            .options {
                animation-delay: 0.35s;
            }

            .btn-login {
                animation-delay: 0.45s;
            }

            .register-link {
                animation-delay: 0.55s;
            }

            .card-footer {
                animation-delay: 0.65s;
            }
        }

        @media (max-width: 400px) {
            .login-card {
                padding: 24px 16px;
            }

            h1 {
                font-size: 20px;
            }

            .logo-icon {
                width: 50px;
                height: 50px;
                font-size: 24px;
            }

            input {
                padding: 10px 12px 10px 38px;
                font-size: 13px;
            }

            .input-wrapper i {
                font-size: 14px;
                left: 12px;
            }

            .btn-login {
                padding: 12px;
                font-size: 14px;
            }

            .options {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .forgot-link {
                font-size: 13px;
            }

            .register-link {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <!-- Image de fond -->
    <div class="bg-image">
        <img src="{{ asset('images/login.jpg') }}" alt="Chantier Bâtiment">
    </div>
    <div class="overlay"></div>

    <div class="container">
        <div class="login-card">
            <div class="logo-area">
                <div class="logo-icon">
                    <i class="fas fa-hard-hat"></i>
                </div>
                <h1>Bienvenue</h1>
                <p class="subtitle">Espace de gestion Bâtiment & Travaux Publics</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="session-status">
                    <i class="fas fa-check-circle"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" value="{{ old('email') }}"
                            placeholder="exemple@entreprise-btp.fr" required autofocus>
                    </div>
                    @error('email')
                        <div class="error">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Votre mot de passe" required>
                    </div>
                    @error('password')
                        <div class="error">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Options -->
                <div class="options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Se souvenir de moi</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            <i class="fas fa-key"></i> Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <!-- Bouton -->
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>



                <div class="card-footer">
                    <!-- Lien d'inscription -->
                    @if (Route::has('register'))
                        <div class="register-link">
                            Pas encore de compte ? <a href="{{ route('register') }}">S'inscrire</a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</body>

</html>
