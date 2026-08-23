<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    <title>Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            background: #f0ede8;
            overflow-x: hidden;
        }

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

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(19, 18, 18, 0.45);
            z-index: 1;
        }

        .container {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.35);
            width: 100%;
            max-width: 980px;
            padding: 40px 45px;
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

        .logo-area {
            text-align: center;
            margin-bottom: 22px;
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
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2563eb, #1a4fc4);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 28px;
            color: white;
            animation: pulseGlow 2s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4);
            }
            50% {
                box-shadow: 0 0 0 15px rgba(37, 99, 235, 0);
            }
        }

        h1 {
            font-size: 26px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .subtitle {
            color: #666;
            font-size: 13px;
        }

        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 12px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.4;
            animation: slideRight 0.6s ease-out 0.2s both;
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

        .info-box i {
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* ===== FORMULAIRE 2 COLONNES ===== */
        .form-wrapper {
            display: flex;
            gap: 30px;
        }

        .form-col-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .form-col-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .form-divider {
            width: 2px;
            background: linear-gradient(180deg, transparent, #ddd8d0, #ddd8d0, transparent);
            flex-shrink: 0;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            animation: fadeUp 0.5s ease-out both;
        }

        /* ANIMATIONS PAR GROUPE */
        .form-col-left .form-group:nth-child(1) { animation-delay: 0.25s; }
        .form-col-left .form-group:nth-child(2) { animation-delay: 0.35s; }
        .form-col-left .form-group:nth-child(3) { animation-delay: 0.45s; }
        .form-col-left .form-group:nth-child(4) { animation-delay: 0.55s; }

        .form-col-right .form-group:nth-child(1) { animation-delay: 0.30s; }
        .form-col-right .form-group:nth-child(2) { animation-delay: 0.40s; }
        .form-col-right .form-group:nth-child(3) { animation-delay: 0.50s; }
        .form-col-right .form-group:nth-child(4) { animation-delay: 0.60s; }

        .password-row .form-group:nth-child(1) { animation-delay: 0.65s; }
        .password-row .form-group:nth-child(2) { animation-delay: 0.70s; }

        .btn-login { animation-delay: 0.80s; }
        .card-footer { animation-delay: 0.90s; }

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
            font-weight: 600;
            color: #333;
            font-size: 12px;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }

        label .required {
            color: #dc2626;
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
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2563eb;
            font-size: 14px;
            z-index: 1;
            transition: all 0.3s ease;
        }

        input,
        select {
            width: 100%;
            padding: 10px 12px 10px 36px;
            border: 2px solid #e5e0d8;
            border-radius: 10px;
            font-size: 13px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #faf9f6;
            height: 44px;
            display: block;
        }

        select {
            padding-left: 12px;
            cursor: pointer;
            appearance: auto;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            transform: scale(1.01);
        }

        input:focus + i,
        select:focus + i {
            color: #1a4fc4;
        }

        .error {
            color: #dc3545;
            font-size: 11px;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
            animation: shake 0.4s ease-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error i {
            font-size: 11px;
        }

        /* ===== MOTS DE PASSE ===== */
        .password-row {
            display: flex;
            gap: 18px;
            margin-top: 16px;
        }

        .password-row .form-group {
            flex: 1;
        }

        /* ===== BOUTON ===== */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #1a4fc4);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            font-family: inherit;
            margin-top: 10px;
            height: 48px;
            position: relative;
            overflow: hidden;
            animation: fadeUp 0.5s ease-out both;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 60%);
            transform: scale(0);
            transition: transform 0.6s ease;
        }

        .btn-login:hover::after {
            transform: scale(1);
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(37, 99, 235, 0.45);
        }

        .btn-login:active {
            transform: scale(0.97);
        }

        .btn-login i {
            margin-right: 8px;
        }

        .card-footer {
            text-align: center;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #eee;
            font-size: 13px;
            color: #888;
            animation: fadeUp 0.5s ease-out both;
        }

        .card-footer a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .card-footer a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #2563eb;
            transition: width 0.3s ease;
        }

        .card-footer a:hover::after {
            width: 100%;
        }

        .card-footer a:hover {
            color: #1a4fc4;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 820px) {
            .login-card {
                padding: 28px 24px;
                max-width: 100%;
            }

            .form-wrapper {
                flex-direction: column;
                gap: 0;
            }

            .form-divider {
                display: none;
            }

            .form-col-left,
            .form-col-right {
                gap: 14px;
            }

            .form-col-right {
                margin-top: 4px;
                padding-top: 16px;
                border-top: 2px solid #e5e0d8;
            }

            .password-row {
                flex-direction: column;
                gap: 14px;
            }

            .form-col-left .form-group:nth-child(1) { animation-delay: 0.25s; }
            .form-col-left .form-group:nth-child(2) { animation-delay: 0.35s; }
            .form-col-left .form-group:nth-child(3) { animation-delay: 0.45s; }
            .form-col-left .form-group:nth-child(4) { animation-delay: 0.55s; }
            .form-col-right .form-group:nth-child(1) { animation-delay: 0.65s; }
            .form-col-right .form-group:nth-child(2) { animation-delay: 0.75s; }
            .form-col-right .form-group:nth-child(3) { animation-delay: 0.85s; }
            .form-col-right .form-group:nth-child(4) { animation-delay: 0.95s; }
            .password-row .form-group:nth-child(1) { animation-delay: 1.05s; }
            .password-row .form-group:nth-child(2) { animation-delay: 1.15s; }
            .btn-login { animation-delay: 1.25s; }
            .card-footer { animation-delay: 1.35s; }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 18px 14px;
            }

            h1 {
                font-size: 20px;
            }

            .logo-icon {
                width: 48px;
                height: 48px;
                font-size: 22px;
            }

            input,
            select {
                height: 40px;
                font-size: 12px;
                padding: 8px 10px 8px 32px;
            }

            .btn-login {
                height: 44px;
                font-size: 14px;
            }

            .form-col-left .form-group:nth-child(1) { animation-delay: 0.20s; }
            .form-col-left .form-group:nth-child(2) { animation-delay: 0.30s; }
            .form-col-left .form-group:nth-child(3) { animation-delay: 0.40s; }
            .form-col-left .form-group:nth-child(4) { animation-delay: 0.50s; }
            .form-col-right .form-group:nth-child(1) { animation-delay: 0.60s; }
            .form-col-right .form-group:nth-child(2) { animation-delay: 0.70s; }
            .form-col-right .form-group:nth-child(3) { animation-delay: 0.80s; }
            .form-col-right .form-group:nth-child(4) { animation-delay: 0.90s; }
            .password-row .form-group:nth-child(1) { animation-delay: 1.00s; }
            .password-row .form-group:nth-child(2) { animation-delay: 1.10s; }
            .btn-login { animation-delay: 1.20s; }
            .card-footer { animation-delay: 1.30s; }

            .info-box {
                font-size: 11px;
                padding: 8px 12px;
            }

            .info-box i {
                font-size: 14px;
                margin-top: 1px;
            }
        }

        /* Animation au survol des champs */
        .form-group:hover .input-wrapper input,
        .form-group:hover .input-wrapper select {
            border-color: #2563eb;
        }

        .form-group:hover .input-wrapper i {
            color: #1a4fc4;
            transform: translateY(-50%) scale(1.1);
        }

        /* Scrollbar personnalisée */
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

        /* Effet de particules sur le fond */
        .bg-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
    </style>
</head>

<body>
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
                <h1>Créer un compte</h1>
                <p class="subtitle">Espace de gestion Bâtiment & Travaux Publics</p>
            </div>

            <div class="info-box">
                <i class="fas fa-circle-info"></i>
                <span>Votre compte sera créé en attente de validation. Un administrateur devra l'activer et vous attribuer un rôle avant que vous puissiez vous connecter.</span>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-wrapper">
                    <!-- COLONNE GAUCHE -->
                    <div class="form-col-left">
                        <div class="form-group">
                            <label>Nom <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="nom" value="{{ old('nom') }}"
                                       placeholder="Votre nom" required autofocus>
                            </div>
                            @error('nom')
                                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Prénom</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="prenom" value="{{ old('prenom') }}"
                                       placeholder="Votre prénom">
                            </div>
                            @error('prenom')
                                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Email <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       placeholder="exemple@entreprise-btp.fr" required>
                            </div>
                            @error('email')
                                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Organisme / Structure <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-building"></i>
                                <select name="structure_id" required>
                                    <option value="">— Sélectionnez —</option>
                                    @foreach($structures as $structure)
                                        <option value="{{ $structure->id }}" @selected(old('structure_id') == $structure->id)>
                                            {{ $structure->nom }} ({{ ucfirst(str_replace('_', ' ', $structure->type)) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('structure_id')
                                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- COLONNE DROITE -->
                    <div class="form-col-right">
                        <div class="form-group">
                            <label>Catégorie de rôle <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-user-tag"></i>
                                <select name="categorie_role" required>
                                    <option value="">— Sélectionnez —</option>
                                    <option value="responsable_organisme" @selected(old('categorie_role') == 'responsable_organisme')>
                                        Responsable d'organisme
                                    </option>
                                    <option value="collaborateur" @selected(old('categorie_role') == 'collaborateur')>
                                        Collaborateur
                                    </option>
                                </select>
                            </div>
                            @error('categorie_role')
                                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Fonction</label>
                            <div class="input-wrapper">
                                <i class="fas fa-briefcase"></i>
                                <input type="text" name="fonction" value="{{ old('fonction') }}"
                                       placeholder="Votre fonction">
                            </div>
                            @error('fonction')
                                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Spécialité</label>
                            <div class="input-wrapper">
                                <i class="fas fa-microscope"></i>
                                <input type="text" name="specialite" value="{{ old('specialite') }}"
                                       placeholder="Votre spécialité">
                            </div>
                            @error('specialite')
                                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Lot</label>
                            <div class="input-wrapper">
                                <i class="fas fa-layer-group"></i>
                                <select name="lot_id">
                                    <option value="">— Aucun lot —</option>
                                    @foreach($lots as $lot)
                                        <option value="{{ $lot->id }}" @selected(old('lot_id') == $lot->id)>
                                            {{ $lot->code }} - {{ $lot->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('lot_id')
                                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="password-row">
                    <div class="form-group">
                        <label>Mot de passe <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Votre mot de passe" required>
                        </div>
                        @error('password')
                            <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Confirmer <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password_confirmation" placeholder="Confirmez" required>
                        </div>
                        @error('password_confirmation')
                            <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-user-plus"></i> Créer mon compte
                </button>

                <div class="card-footer">
                    Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
