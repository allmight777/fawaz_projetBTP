<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FawazBTP — Gestion de chantiers & Projets</title>
    <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================================
           BASE
        ============================================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: #171717;
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; transition: color .2s ease; }
        img { max-width: 100%; display: block; }
        ul { list-style: none; }

        .container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 28px;
        }

        .section { padding: 96px 0; }
        .section--muted { background: #f7f7f5; }

        .eyebrow {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #2563eb;
            margin-bottom: 14px;
        }

        .section-head {
            max-width: 620px;
            margin-bottom: 56px;
        }

        .section-head--center {
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        .section-head h2 {
            font-size: 34px;
            font-weight: 700;
            letter-spacing: -0.6px;
            color: #111111;
            line-height: 1.25;
        }

        .section-head p {
            margin-top: 14px;
            font-size: 16px;
            color: #5c5c5c;
        }

        /* ============================================================
           BUTTONS
        ============================================================ */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 13px 26px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all .2s ease;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn--primary {
            background: #2563eb;
            color: #ffffff;
        }
        .btn--primary:hover { background: #1d4ed8; }

        .btn--ghost {
            background: #ffffff;
            color: #171717;
            border-color: #e2e2e2;
        }
        .btn--ghost:hover { border-color: #171717; }

        .btn--dark {
            background: #171717;
            color: #ffffff;
        }
        .btn--dark:hover { background: #000000; }

        /* Bouton connexion : texte + icône en blanc forcé */
        .nav .btn--primary,
        .nav .btn--primary i {
            color: #ffffff !important;
        }

        /* ============================================================
           HEADER
        ============================================================ */
        .header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #ececec;
        }

        .header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 72px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .brand__mark {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: #171717;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .brand__name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.4px;
            color: #111111;
        }

        .brand__tag {
            display: block;
            font-size: 11px;
            font-weight: 400;
            color: #8a8a8a;
            margin-top: -2px;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav a {
            font-size: 14px;
            font-weight: 500;
            color: #4a4a4a;
        }
        .nav a:hover { color: #111111; }

        .nav .btn { padding: 9px 18px; font-size: 13px; }

        .menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: #171717;
            cursor: pointer;
        }

        /* ============================================================
           HERO
        ============================================================ */
        .hero {
            padding: 88px 0 96px;
            border-bottom: 1px solid #ececec;
        }

        .hero__grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 64px;
            align-items: center;
        }

        .hero h1 {
            font-size: 46px;
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -1.4px;
            color: #111111;
        }

        .hero h1 em {
            font-style: normal;
            color: #2563eb;
        }

        .hero p {
            margin-top: 22px;
            font-size: 17px;
            color: #5c5c5c;
            max-width: 480px;
        }

        .hero__actions {
            display: flex;
            gap: 12px;
            margin-top: 34px;
        }

        .hero__meta {
            display: flex;
            gap: 44px;
            margin-top: 52px;
            padding-top: 28px;
            border-top: 1px solid #ececec;
        }

        .hero__meta .num {
            font-size: 26px;
            font-weight: 700;
            color: #111111;
            letter-spacing: -0.5px;
        }

        .hero__meta .lbl {
            font-size: 12.5px;
            color: #8a8a8a;
            margin-top: 3px;
        }

        .hero__media {
            position: relative;
        }

        .hero__media img {
            width: 100%;
            height: 460px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #ececec;
        }

        .hero__media .tag {
            position: absolute;
            left: 16px;
            bottom: 16px;
            background: #ffffff;
            border: 1px solid #ececec;
            border-radius: 8px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12.5px;
            font-weight: 500;
            color: #3a3a3a;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
        }

        .hero__media .tag i {
            color: #2563eb;
            font-size: 14px;
        }

        /* ============================================================
           TRUST BAR
        ============================================================ */
        .trust {
            padding: 26px 0;
            border-bottom: 1px solid #ececec;
        }

        .trust .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .trust .label {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #8a8a8a;
        }

        .trust__items {
            display: flex;
            align-items: center;
            gap: 32px;
            flex-wrap: wrap;
        }

        .trust__item {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 13.5px;
            color: #5c5c5c;
            font-weight: 500;
        }

        .trust__item i { color: #b0b0b0; font-size: 16px; }

        /* ============================================================
           METRICS
        ============================================================ */
        .metrics {
            padding: 72px 0;
            border-bottom: 1px solid #ececec;
        }

        .metrics__grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1px;
            background: #ececec;
            border: 1px solid #ececec;
            border-radius: 12px;
            overflow: hidden;
        }

        .metric {
            background: #ffffff;
            padding: 32px 28px;
        }

        .metric__num {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.8px;
            color: #111111;
        }

        .metric__lbl {
            font-size: 13.5px;
            color: #6b6b6b;
            margin-top: 4px;
        }

        /* ============================================================
           ABOUT
        ============================================================ */
        .about__grid {
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            gap: 64px;
            align-items: center;
        }

        .about__img img {
            width: 100%;
            height: 440px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #ececec;
        }

        .about__text h2 {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.6px;
            line-height: 1.3;
            color: #111111;
        }

        .about__text .lead {
            margin-top: 18px;
            font-size: 16.5px;
            color: #3a3a3a;
            font-weight: 500;
        }

        .about__text p {
            margin-top: 14px;
            font-size: 15px;
            color: #5c5c5c;
        }

        .about__list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 24px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid #ececec;
        }

        .about__list li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #3a3a3a;
        }

        .about__list i {
            color: #2563eb;
            font-size: 12px;
        }

        /* ============================================================
           SERVICES
        ============================================================ */
        .services__grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .service {
            padding: 32px 28px;
            border: 1px solid #ececec;
            border-radius: 12px;
            background: #ffffff;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .service:hover {
            border-color: #d4d4d4;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        }

        .service__icon {
            width: 46px;
            height: 46px;
            border-radius: 9px;
            background: #f4f4f2;
            color: #171717;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            margin-bottom: 20px;
        }

        .service h3 {
            font-size: 16.5px;
            font-weight: 600;
            color: #111111;
            margin-bottom: 8px;
            letter-spacing: -0.2px;
        }

        .service p {
            font-size: 14px;
            color: #5c5c5c;
            line-height: 1.7;
        }

        /* ============================================================
           GALLERY
        ============================================================ */
        .gallery {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .gallery figure {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            aspect-ratio: 4 / 3;
            border: 1px solid #ececec;
        }

        .gallery img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .5s ease;
        }

        .gallery figure:hover img { transform: scale(1.04); }

        .gallery figcaption {
            position: absolute;
            inset: auto 0 0 0;
            padding: 20px 18px 16px;
            background: linear-gradient(transparent, rgba(0, 0, 0, .72));
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
        }

        /* ============================================================
           PROCESS
        ============================================================ */
        .process {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border-top: 1px solid #ececec;
        }

        .process__step {
            padding: 32px 24px 32px 0;
            border-right: 1px solid #ececec;
        }

        .process__step:last-child { border-right: none; padding-right: 0; }
        .process__step:not(:first-child) { padding-left: 24px; }

        .process__num {
            font-size: 13px;
            font-weight: 600;
            color: #2563eb;
            letter-spacing: 1px;
            margin-bottom: 14px;
        }

        .process__step h4 {
            font-size: 16px;
            font-weight: 600;
            color: #111111;
            margin-bottom: 8px;
        }

        .process__step p {
            font-size: 13.5px;
            color: #5c5c5c;
            line-height: 1.7;
        }

        /* ============================================================
           LOCATION
        ============================================================ */
        .loc__grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 56px;
            align-items: center;
        }

        .loc__img img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #ececec;
        }

        .loc__info h2 {
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #111111;
        }

        .loc__info > p {
            margin-top: 14px;
            font-size: 15px;
            color: #5c5c5c;
        }

        .loc__list {
            margin-top: 28px;
            display: grid;
            gap: 1px;
            background: #ececec;
            border: 1px solid #ececec;
            border-radius: 10px;
            overflow: hidden;
        }

        .loc__row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: #ffffff;
        }

        .loc__row i {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #f4f4f2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #171717;
            flex-shrink: 0;
        }

        .loc__row strong {
            display: block;
            font-size: 14px;
            color: #111111;
        }

        .loc__row span {
            font-size: 13px;
            color: #8a8a8a;
        }

        /* ============================================================
           CONTACT
        ============================================================ */
        .contact__grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 64px;
            align-items: start;
        }

        .contact__form .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .field { margin-bottom: 16px; }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #171717;
            margin-bottom: 6px;
        }

        .field label .req { color: #dc2626; }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 12px 14px;
            font-family: inherit;
            font-size: 14px;
            color: #171717;
            background: #ffffff;
            border: 1px solid #e2e2e2;
            border-radius: 8px;
            transition: border-color .2s ease;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        .field textarea { min-height: 130px; resize: vertical; }

        .contact__info h2 {
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #111111;
        }

        .contact__info > p {
            margin-top: 14px;
            font-size: 15px;
            color: #5c5c5c;
        }

        .contact__items {
            margin-top: 28px;
            display: grid;
            gap: 1px;
            background: #ececec;
            border: 1px solid #ececec;
            border-radius: 10px;
            overflow: hidden;
        }

        .contact__item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            background: #ffffff;
        }

        .contact__item i {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #f4f4f2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #171717;
            flex-shrink: 0;
        }

        .contact__item strong {
            display: block;
            font-size: 13.5px;
            color: #111111;
        }

        .contact__item span {
            font-size: 12.5px;
            color: #8a8a8a;
        }

        /* ============================================================
           FOOTER
        ============================================================ */
        .footer {
            background: #0d0d0d;
            color: #a3a3a3;
            padding: 64px 0 28px;
        }

        .footer__grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr 1fr;
            gap: 48px;
            padding-bottom: 40px;
            border-bottom: 1px solid #1f1f1f;
        }

        .footer__brand .brand__name { color: #ffffff; }
        .footer__brand .brand__tag { color: #737373; }

        .footer__brand .brand__mark {
            background: #ffffff;
            color: #111111;
        }

        .footer__brand p {
            margin-top: 16px;
            font-size: 14px;
            line-height: 1.8;
            max-width: 300px;
            color: #8a8a8a;
        }

        .footer h5 {
            font-size: 13px;
            font-weight: 600;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 18px;
        }

        .footer ul li { margin-bottom: 10px; }

        .footer ul li a {
            font-size: 14px;
            color: #8a8a8a;
        }

        .footer ul li a:hover { color: #ffffff; }

        .footer__bottom {
            padding-top: 26px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 13px;
            color: #6b6b6b;
        }

        /* ============================================================
           REVEAL
        ============================================================ */
        .reveal {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity .7s ease, transform .7s ease;
        }

        .reveal.in {
            opacity: 1;
            transform: translateY(0);
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 1024px) {
            .hero__grid, .about__grid, .loc__grid, .contact__grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            .hero__meta { justify-content: flex-start; }
            .services__grid { grid-template-columns: repeat(2, 1fr); }
            .gallery { grid-template-columns: repeat(2, 1fr); }
            .process { grid-template-columns: repeat(2, 1fr); }
            .process__step { border-right: none; padding: 24px 0; }
            .process__step:not(:first-child) { padding-left: 0; }
            .metrics__grid { grid-template-columns: repeat(2, 1fr); }
            .footer__grid { grid-template-columns: 1fr 1fr; gap: 32px; }
        }

        @media (max-width: 768px) {
            .section { padding: 64px 0; }
            .header .container { height: 64px; }
            .menu-btn { display: block; }
            .nav {
                display: none;
                position: absolute;
                top: 64px;
                left: 0;
                right: 0;
                flex-direction: column;
                align-items: stretch;
                gap: 0;
                padding: 12px 28px 20px;
                background: #ffffff;
                border-bottom: 1px solid #ececec;
            }
            .nav.open { display: flex; }
            .nav a { padding: 12px 0; border-bottom: 1px solid #f2f2f2; }
            .nav .btn { margin-top: 12px; justify-content: center; }
            .hero { padding: 56px 0 64px; }
            .hero h1 { font-size: 32px; letter-spacing: -0.8px; }
            .hero p { font-size: 15px; }
            .hero__meta { gap: 28px; flex-wrap: wrap; }
            .hero__meta .num { font-size: 22px; }
            .hero__media img { height: 300px; }
            .hero__actions .btn { width: 100%; justify-content: center; }
            .hero__actions { flex-direction: column; }
            .section-head h2 { font-size: 26px; }
            .about__text h2, .loc__info h2, .contact__info h2 { font-size: 24px; }
            .about__list { grid-template-columns: 1fr; }
            .services__grid { grid-template-columns: 1fr; }
            .gallery { grid-template-columns: 1fr; }
            .process { grid-template-columns: 1fr; }
            .metrics__grid { grid-template-columns: 1fr 1fr; }
            .contact__form .row { grid-template-columns: 1fr; }
            .footer__grid { grid-template-columns: 1fr; gap: 32px; }
            .footer__bottom { flex-direction: column; text-align: center; }
            .trust .container { flex-direction: column; align-items: flex-start; }
            .trust__items { gap: 18px; }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="header">
        <div class="container">
            <a href="#" class="brand">
                <span class="brand__mark"><i class="fas fa-hard-hat"></i></span>
                <span>
                    <span class="brand__name">FawazBTP</span>
                    <span class="brand__tag">Gestion de chantiers & Projets</span>
                </span>
            </a>

            <button class="menu-btn" onclick="document.querySelector('.nav').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>

            <nav class="nav">
                <a href="#apropos">À propos</a>
                <a href="#services">Services</a>
                <a href="#galerie">Galerie</a>
                <a href="#processus">Processus</a>
                <a href="#localisation">Localisation</a>
                <a href="#contact">Contact</a>
                <a href="{{ route('login') }}" class="btn btn--primary">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
            </nav>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="hero__grid">
                <div class="reveal">
                    <span class="eyebrow">Plateforme BTP</span>
                    <h1>La gestion documentaire<br>repensée pour le <em>BTP</em>.</h1>
                    <p>
                        Centralisez, validez et tracez vos documents techniques dans un espace unique.
                        Conçu pour les maîtres d'ouvrage, bureaux d'études et bureaux de contrôle.
                    </p>
                    <div class="hero__actions">
                        <a href="#contact" class="btn btn--primary">
                            Demander une démo <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="#services" class="btn btn--ghost">
                            Découvrir les services
                        </a>
                    </div>
                    <div class="hero__meta">
                        <div>
                            <div class="num">120+</div>
                            <div class="lbl">Projets gérés</div>
                        </div>
                        <div>
                            <div class="num">98%</div>
                            <div class="lbl">Satisfaction</div>
                        </div>
                        <div>
                            <div class="num">15</div>
                            <div class="lbl">Partenaires</div>
                        </div>
                        <div>
                            <div class="num">4.8</div>
                            <div class="lbl">Note moyenne</div>
                        </div>
                    </div>
                </div>
                <div class="hero__media reveal">
                    <img src="{{ asset('images/chantier-hero.jpg') }}"
                         alt="Chantier BTP"
                         onerror="this.src='https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=900&h=920&fit=crop'">
                    <div class="tag">
                        <i class="fas fa-shield-alt"></i>
                        Certifié sécurité & qualité
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TRUST BAR -->
    <section class="trust">
        <div class="container">
            <span class="label">Ils nous font confiance</span>
            <div class="trust__items">
                <span class="trust__item"><i class="fas fa-building"></i> Entreprises générales</span>
                <span class="trust__item"><i class="fas fa-drafting-compass"></i> Bureaux d'études</span>
                <span class="trust__item"><i class="fas fa-clipboard-check"></i> Bureaux de contrôle</span>
                <span class="trust__item"><i class="fas fa-users-cog"></i> Maîtres d'ouvrage</span>
                <span class="trust__item"><i class="fas fa-flask"></i> Laboratoires</span>
            </div>
        </div>
    </section>

    <!-- METRICS -->
    <section class="metrics">
        <div class="container">
            <div class="metrics__grid reveal">
                <div class="metric">
                    <div class="metric__num">15 000+</div>
                    <div class="metric__lbl">Documents gérés</div>
                </div>
                <div class="metric">
                    <div class="metric__num">250+</div>
                    <div class="metric__lbl">Utilisateurs actifs</div>
                </div>
                <div class="metric">
                    <div class="metric__num">3 200+</div>
                    <div class="metric__lbl">Validations effectuées</div>
                </div>
                <div class="metric">
                    <div class="metric__num">-45%</div>
                    <div class="metric__lbl">Délais réduits</div>
                </div>
            </div>
        </div>
    </section>

    <!-- À PROPOS -->
    <section id="apropos" class="section">
        <div class="container">
            <div class="about__grid">
                <div class="about__img reveal">
                    <img src="{{ asset('images/about-btp.jpg') }}"
                         alt="À propos de FawazBTP"
                         onerror="this.src='https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=900&h=880&fit=crop'">
                </div>
                <div class="about__text reveal">
                    <span class="eyebrow">À propos</span>
                    <h2>Une plateforme conçue par et pour les acteurs du BTP.</h2>
                    <p class="lead">
                        FawazBTP centralise l'ensemble des documents techniques de vos chantiers
                        et automatise les circuits de validation.
                    </p>
                    <p>
                        Plans, notes de calcul, rapports d'essais, fiches techniques : chaque document
                        est versionné, tracé et validé selon la hiérarchie des intervenants. Une
                        traçabilité complète qui réduit les erreurs et facilite l'archivage de fin de projet.
                    </p>
                    <ul class="about__list">
                        <li><i class="fas fa-check"></i> Centralisation documentaire</li>
                        <li><i class="fas fa-check"></i> Workflows automatisés</li>
                        <li><i class="fas fa-check"></i> Gestion des versions</li>
                        <li><i class="fas fa-check"></i> Traçabilité complète</li>
                        <li><i class="fas fa-check"></i> Notifications en temps réel</li>
                        <li><i class="fas fa-check"></i> Archivage sécurisé</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICES -->
    <section id="services" class="section section--muted">
        <div class="container">
            <div class="section-head section-head--center reveal">
                <span class="eyebrow">Services</span>
                <h2>Ce que FawazBTP apporte à vos projets</h2>
                <p>Des fonctionnalités pensées pour les exigences réelles des chantiers.</p>
            </div>
            <div class="services__grid">
                <div class="service reveal">
                    <div class="service__icon"><i class="fas fa-folder-open"></i></div>
                    <h3>Gestion documentaire</h3>
                    <p>Plans, notes de calcul, dossiers d'exécution, fiches techniques et rapports d'essais réunis dans un espace unique.</p>
                </div>
                <div class="service reveal">
                    <div class="service__icon"><i class="fas fa-route"></i></div>
                    <h3>Workflows de validation</h3>
                    <p>Circuits automatisés avec checklists dynamiques, affectation aux contrôleurs et décision finale tracée.</p>
                </div>
                <div class="service reveal">
                    <div class="service__icon"><i class="fas fa-code-branch"></i></div>
                    <h3>Gestion des versions</h3>
                    <p>Suivi V1, V2, V3… avec conservation de l'historique complet et accès à chaque révision.</p>
                </div>
                <div class="service reveal">
                    <div class="service__icon"><i class="fas fa-clipboard-list"></i></div>
                    <h3>Traçabilité & audit</h3>
                    <p>Journal d'audit détaillé pour une transparence totale et une analyse fine des performances.</p>
                </div>
                <div class="service reveal">
                    <div class="service__icon"><i class="fas fa-bell"></i></div>
                    <h3>Notifications</h3>
                    <p>Alertes email à chaque étape : réception, affectation, correction, validation et archivage.</p>
                </div>
                <div class="service reveal">
                    <div class="service__icon"><i class="fas fa-archive"></i></div>
                    <h3>Archivage sécurisé</h3>
                    <p>Conservation intégrale des documents, versions, observations et décisions pour capitaliser.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- GALERIE -->
    <section id="galerie" class="section">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Réalisations</span>
                <h2>Quelques chantiers accompagnés</h2>
                <p>Un aperçu des projets sur lesquels la plateforme est déployée.</p>
            </div>
            <div class="gallery">
                <figure class="reveal">
                    <img src="{{ asset('images/gallery1.jpg') }}" alt="Immeuble R+8"
                         onerror="this.src='https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=600&h=450&fit=crop'">
                    <figcaption>Construction immeuble R+8</figcaption>
                </figure>
                <figure class="reveal">
                    <img src="{{ asset('images/gallery2.jpg') }}" alt="Rénovation pont"
                         onerror="this.src='https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=600&h=450&fit=crop'">
                    <figcaption>Rénovation pont</figcaption>
                </figure>
                <figure class="reveal">
                    <img src="{{ asset('images/gallery3.jpg') }}" alt="Lycée international"
                         onerror="this.src='https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=600&h=450&fit=crop'">
                    <figcaption>Lycée international</figcaption>
                </figure>
                <figure class="reveal">
                    <img src="{{ asset('images/gallery4.jpg') }}" alt="Complexe sportif"
                         onerror="this.src='https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=600&h=450&fit=crop&crop=center'">
                    <figcaption>Complexe sportif</figcaption>
                </figure>
                <figure class="reveal">
                    <img src="{{ asset('images/gallery5.jpg') }}" alt="Résidence étudiante"
                         onerror="this.src='https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=600&h=450&fit=crop&crop=center'">
                    <figcaption>Résidence étudiante</figcaption>
                </figure>
                <figure class="reveal">
                    <img src="{{ asset('images/gallery6.jpg') }}" alt="Centre commercial"
                         onerror="this.src='https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=600&h=450&fit=crop&crop=center'">
                    <figcaption>Centre commercial</figcaption>
                </figure>
            </div>
        </div>
    </section>

    <!-- PROCESSUS -->
    <section id="processus" class="section section--muted">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Processus</span>
                <h2>Un cycle documentaire maîtrisé</h2>
                <p>De l'import du document à son archivage final, chaque étape est tracée.</p>
            </div>
            <div class="process">
                <div class="process__step reveal">
                    <div class="process__num">01 — IMPORT</div>
                    <h4>Importation</h4>
                    <p>Intégration du document avec ses métadonnées et attribution d'un identifiant unique.</p>
                </div>
                <div class="process__step reveal">
                    <div class="process__num">02 — CHECK</div>
                    <h4>Vérification</h4>
                    <p>Contrôle préalable via une checklist dynamique adaptée au type de document.</p>
                </div>
                <div class="process__step reveal">
                    <div class="process__num">03 — ANALYSE</div>
                    <h4>Analyse</h4>
                    <p>Affectation aux contrôleurs, consolidation des observations et remarques.</p>
                </div>
                <div class="process__step reveal">
                    <div class="process__num">04 — DÉCISION</div>
                    <h4>Décision</h4>
                    <p>Validation, correction ou archivage. Gestion automatisée des versions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- LOCALISATION -->
    <section id="localisation" class="section">
        <div class="container">
            <div class="loc__grid">
                <div class="loc__img reveal">
                    <img src="{{ asset('images/map-btp.jpg') }}" alt="Localisation FawazBTP"
                         onerror="this.src='https://images.unsplash.com/photo-1524661135-423995f22d0b?w=900&h=800&fit=crop'">
                </div>
                <div class="loc__info reveal">
                    <span class="eyebrow">Localisation</span>
                    <h2>Présents sur le terrain, proches de vos équipes.</h2>
                    <p>Des interlocuteurs dédiés pour vous accompagner à chaque étape de votre projet.</p>
                    <div class="loc__list">
                        <div class="loc__row">
                            <i class="fas fa-map-marker-alt"></i>
                            <div><strong>Siège social</strong><span>Fawaz</span></div>
                        </div>
                        <div class="loc__row">
                            <i class="fas fa-map-marker-alt"></i>
                            <div><strong>Agence</strong><span>Fawaz</span></div>
                        </div>
                        <div class="loc__row">
                            <i class="fas fa-phone-alt"></i>
                            <div><strong>Téléphone</strong><span>+229 01 00 00 00 00</span></div>
                        </div>
                        <div class="loc__row">
                            <i class="fas fa-envelope"></i>
                            <div><strong>Email</strong><span>Fawaz</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT -->
    <section id="contact" class="section section--muted">
        <div class="container">
            <div class="contact__grid">
                <div class="contact__form reveal">
                    <span class="eyebrow">Contact</span>
                    <div class="section-head" style="margin-bottom:28px;">
                        <h2>Parlons de votre projet.</h2>
                        <p>Une démo, un devis ou une question technique ? Écrivez-nous.</p>
                    </div>
                    <form action="#" method="POST">
                        @csrf
                        <div class="row">
                            <div class="field">
                                <label for="name">Nom complet <span class="req">*</span></label>
                                <input type="text" id="name" name="name" placeholder="Fawaz" required>
                            </div>
                            <div class="field">
                                <label for="email">Email <span class="req">*</span></label>
                                <input type="email" id="email" name="email" placeholder="Fawaz" required>
                            </div>
                        </div>
                        <div class="field">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone" placeholder="+229 01 00 00 00 00">
                        </div>
                        <div class="field">
                            <label for="subject">Sujet <span class="req">*</span></label>
                            <select id="subject" name="subject" required>
                                <option value="">Choisissez un sujet</option>
                                <option value="devis">Demande de devis</option>
                                <option value="demo">Demande de démonstration</option>
                                <option value="support">Support technique</option>
                                <option value="partenariat">Partenariat</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="message">Message <span class="req">*</span></label>
                            <textarea id="message" name="message" placeholder="Décrivez votre demande..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn--dark">
                            Envoyer le message <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
                <div class="contact__info reveal">
                    <span class="eyebrow">Pourquoi nous</span>
                    <h2>Un accompagnement sérieux et durable.</h2>
                    <p>Notre équipe vous accompagne de la mise en place au support quotidien.</p>
                    <div class="contact__items">
                        <div class="contact__item">
                            <i class="fas fa-clock"></i>
                            <div><strong>Disponibilité</strong><span>Lun — Ven : 8h00 – 18h00</span></div>
                        </div>
                        <div class="contact__item">
                            <i class="fas fa-headset"></i>
                            <div><strong>Support</strong><span>Assistance 24/7 pour les urgences</span></div>
                        </div>
                        <div class="contact__item">
                            <i class="fas fa-shield-alt"></i>
                            <div><strong>Sécurité</strong><span>Données chiffrées, hébergées en France</span></div>
                        </div>
                        <div class="contact__item">
                            <i class="fas fa-users-cog"></i>
                            <div><strong>Expertise</strong><span>Des professionnels du BTP</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer__grid">
                <div class="footer__brand">
                    <div class="brand">
                        <span class="brand__mark"><i class="fas fa-hard-hat"></i></span>
                        <span>
                            <span class="brand__name">FawazBTP</span>
                            <span class="brand__tag">Gestion de chantiers & Projets</span>
                        </span>
                    </div>
                    <p>La solution de gestion documentaire pensée pour les projets de construction exigeants.</p>
                </div>
                <div>
                    <h5>Produit</h5>
                    <ul>
                        <li><a href="#apropos">À propos</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#processus">Processus</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Ressources</h5>
                    <ul>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Support</a></li>
                        <li><a href="#">API</a></li>
                        <li><a href="#">Actualités</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Informations</h5>
                    <ul>
                        <li><a href="#">Mentions légales</a></li>
                        <li><a href="#">Confidentialité</a></li>
                        <li><a href="#">CGU / CGV</a></li>
                        <li><a href="#">Recrutement</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer__bottom">
                <span>&copy; {{ date('Y') }} FawazBTP. Tous droits réservés.</span>
                <span>Conçu par Fawaz</span>
            </div>
        </div>
    </footer>

    <script>
        // Reveal on scroll
        document.addEventListener('DOMContentLoaded', () => {
            const items = document.querySelectorAll('.reveal');
            const io = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('in');
                        io.unobserve(e.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            items.forEach(el => io.observe(el));
        });

        // Mobile menu
        document.querySelectorAll('.nav a').forEach(a => {
            a.addEventListener('click', () => document.querySelector('.nav').classList.remove('open'));
        });
    </script>

</body>
</html>
