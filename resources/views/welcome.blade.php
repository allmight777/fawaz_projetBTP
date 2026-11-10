<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FawazBTP — Gestion documentaire BTP</title>
    <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}?v={{ @filemtime(public_path('css/ui.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ @filemtime(public_path('css/landing.css')) }}">
</head>
<body>

<div class="pillbar">
    <a href="#" class="logo"><b>Fawaz<span>BTP</span></b></a>
    <nav id="lpNav">
        <a href="#apropos">À propos</a>
        <a href="#services">Services</a>
        <a href="#galerie">Galerie</a>
        <a href="#processus">Processus</a>
        <a href="#localisation">Localisation</a>
        <a href="#contact">Contact</a>
    </nav>
    <a href="{{ route('login') }}" class="btn">Connexion</a>
    <button type="button" class="menu-btn" onclick="document.getElementById('lpNav').classList.toggle('open')">Menu</button>
</div>

<main class="lp">

    {{-- Héros --}}
    <section class="lp-hero">
        <div class="lp-main reveal">
            <span class="lp-eyebrow">Plateforme BTP</span>
            <h1>La gestion documentaire repensée pour le <em>BTP</em>.</h1>
            <p>Centralisez, validez et tracez vos documents techniques dans un espace unique. Conçu pour les maîtres d'ouvrage, bureaux d'études et bureaux de contrôle.</p>
            <div class="lp-actions">
                <a href="#contact" class="lp-btn white">Demander une démo <i class="fas fa-arrow-right"></i></a>
                <a href="#services" class="lp-btn ghost">Découvrir les services</a>
            </div>
        </div>
        <div class="lp-t lp-media reveal">
            <img src="{{ asset('images/chantier-hero.jpg') }}" alt="Chantier BTP"
                 onerror="this.src='https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=900&h=920&fit=crop'">
            <div class="lp-tag"><i class="fas fa-shield-alt"></i> Certifié sécurité &amp; qualité</div>
        </div>
    </section>

    <section class="lp-nums reveal">
        <div class="lp-t"><div class="lp-num">120+</div><div class="lp-lbl">Projets gérés</div></div>
        <div class="lp-t"><div class="lp-num">98%</div><div class="lp-lbl">Satisfaction</div></div>
        <div class="lp-t"><div class="lp-num">15</div><div class="lp-lbl">Partenaires</div></div>
        <div class="lp-t"><div class="lp-num">4.8</div><div class="lp-lbl">Note moyenne</div></div>
    </section>

    <section class="lp-t lp-trust reveal">
        <span class="label">Ils nous font confiance</span>
        <span class="lp-chip"><i class="fas fa-building"></i>Entreprises générales</span>
        <span class="lp-chip"><i class="fas fa-drafting-compass"></i>Bureaux d'études</span>
        <span class="lp-chip"><i class="fas fa-clipboard-check"></i>Bureaux de contrôle</span>
        <span class="lp-chip"><i class="fas fa-users-cog"></i>Maîtres d'ouvrage</span>
        <span class="lp-chip"><i class="fas fa-flask"></i>Laboratoires</span>
    </section>

    <section class="lp-dark reveal">
        <div class="m"><div class="n">15 000+</div><div class="l">Documents gérés</div></div>
        <div class="m"><div class="n">250+</div><div class="l">Utilisateurs actifs</div></div>
        <div class="m"><div class="n">3 200+</div><div class="l">Validations effectuées</div></div>
        <div class="m"><div class="n">-45%</div><div class="l">Délais réduits</div></div>
    </section>

    {{-- À propos --}}
    <section id="apropos" class="lp-split">
        <div class="lp-t lp-img reveal">
            <img src="{{ asset('images/about-btp.jpg') }}" alt="À propos de FawazBTP"
                 onerror="this.src='https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=900&h=880&fit=crop'">
        </div>
        <div class="lp-t lp-text reveal">
            <span class="lp-eyebrow">À propos</span>
            <h2>Une plateforme conçue par et pour les acteurs du BTP.</h2>
            <p class="lead">FawazBTP centralise l'ensemble des documents techniques de vos chantiers et automatise les circuits de validation.</p>
            <p>Plans, notes de calcul, rapports d'essais, fiches techniques : chaque document est versionné, tracé et validé selon la hiérarchie des intervenants. Une traçabilité complète qui réduit les erreurs et facilite l'archivage de fin de projet.</p>
            <ul class="lp-list">
                <li><i class="fas fa-check"></i>Centralisation documentaire</li>
                <li><i class="fas fa-check"></i>Workflows automatisés</li>
                <li><i class="fas fa-check"></i>Gestion des versions</li>
                <li><i class="fas fa-check"></i>Traçabilité complète</li>
                <li><i class="fas fa-check"></i>Notifications en temps réel</li>
                <li><i class="fas fa-check"></i>Archivage sécurisé</li>
            </ul>
        </div>
    </section>

    {{-- Services --}}
    <section id="services">
        <div class="lp-head reveal">
            <span class="lp-eyebrow">Services</span>
            <h2>Ce que FawazBTP apporte à vos projets</h2>
            <p>Des fonctionnalités pensées pour les exigences réelles des chantiers.</p>
        </div>
        <div class="lp-grid3" style="margin-top:14px">
            @foreach([
                ['fa-folder-open', 'Gestion documentaire', 'Plans, notes de calcul, dossiers d\'exécution, fiches techniques et rapports d\'essais réunis dans un espace unique.'],
                ['fa-route', 'Workflows de validation', 'Circuits automatisés avec checklists dynamiques, affectation aux contrôleurs et décision finale tracée.'],
                ['fa-code-branch', 'Gestion des versions', 'Suivi V1, V2, V3… avec conservation de l\'historique complet et accès à chaque révision.'],
                ['fa-clipboard-list', 'Traçabilité & audit', 'Journal d\'audit détaillé pour une transparence totale et une analyse fine des performances.'],
                ['fa-bell', 'Notifications', 'Alertes email à chaque étape : réception, affectation, correction, validation et archivage.'],
                ['fa-archive', 'Archivage sécurisé', 'Conservation intégrale des documents, versions, observations et décisions pour capitaliser.'],
            ] as [$icone, $titre, $texte])
                <div class="lp-t lp-svc reveal">
                    <div class="ic"><i class="fas {{ $icone }}"></i></div>
                    <h3>{{ $titre }}</h3>
                    <p>{{ $texte }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Galerie --}}
    <section id="galerie">
        <div class="lp-head reveal">
            <span class="lp-eyebrow">Réalisations</span>
            <h2>Quelques chantiers accompagnés</h2>
            <p>Un aperçu des projets sur lesquels la plateforme est déployée.</p>
        </div>
        <div class="lp-gal" style="margin-top:14px">
            @foreach([
                ['gallery1', 'Immeuble R+8', 'Immeuble R+8', '1429497419816-9ca5cfb4571a'],
                ['gallery2', 'Terrassement et voirie', 'Terrassement & VRD', '1517089596392-fb9a9033e05b'],
                ['gallery3', 'Centre culturel', 'Centre culturel', '1487958449943-2429e8be8625'],
                ['gallery4', 'Complexe sportif', 'Complexe sportif', '1531834685032-c34bf0d84c77'],
                ['gallery5', 'Résidence étudiante', 'Résidence étudiante', '1545324418-cc1a3fa10c00'],
                ['gallery6', 'Tours de bureaux', 'Tours de bureaux', '1486406146926-c627a92ad1ab'],
            ] as [$fichier, $alt, $legende, $secours])
                <figure class="reveal">
                    <img src="{{ asset('images/' . $fichier . '.jpg') }}" alt="{{ $alt }}"
                         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-{{ $secours }}?w=600&h=450&fit=crop'">
                    <figcaption>{{ $legende }}</figcaption>
                </figure>
            @endforeach
        </div>
    </section>

    {{-- Processus --}}
    <section id="processus">
        <div class="lp-head reveal">
            <span class="lp-eyebrow">Processus</span>
            <h2>Un cycle documentaire maîtrisé</h2>
            <p>De l'import du document à son archivage final, chaque étape est tracée.</p>
        </div>
        <div class="lp-steps" style="margin-top:14px">
            <div class="lp-t lp-step reveal"><span class="num">01 — IMPORT</span><h4>Importation</h4><p>Intégration du document avec ses métadonnées et attribution d'un identifiant unique.</p></div>
            <div class="lp-t lp-step reveal"><span class="num">02 — CHECK</span><h4>Vérification</h4><p>Contrôle préalable via une checklist dynamique adaptée au type de document.</p></div>
            <div class="lp-t lp-step reveal"><span class="num">03 — ANALYSE</span><h4>Analyse</h4><p>Affectation aux contrôleurs, consolidation des observations et remarques.</p></div>
            <div class="lp-t lp-step reveal"><span class="num">04 — DÉCISION</span><h4>Décision</h4><p>Validation, correction ou archivage. Gestion automatisée des versions.</p></div>
        </div>
    </section>

    {{-- Localisation --}}
    <section id="localisation" class="lp-split">
        <div class="lp-t lp-text reveal">
            <span class="lp-eyebrow">Localisation</span>
            <h2>Présents sur le terrain, proches de vos équipes.</h2>
            <p class="lead">Des interlocuteurs dédiés pour vous accompagner à chaque étape de votre projet.</p>
            <div class="lp-rows">
                <div class="lp-row"><i class="fas fa-map-marker-alt"></i><div><strong>Siège social</strong><span>Fawaz</span></div></div>
                <div class="lp-row"><i class="fas fa-map-marker-alt"></i><div><strong>Agence</strong><span>Fawaz</span></div></div>
                <div class="lp-row"><i class="fas fa-phone-alt"></i><div><strong>Téléphone</strong><span>+229 01 00 00 00 00</span></div></div>
                <div class="lp-row"><i class="fas fa-envelope"></i><div><strong>Email</strong><span>Fawaz</span></div></div>
            </div>
        </div>
        <div class="lp-t lp-img reveal">
            <img src="{{ asset('images/map-btp.jpg') }}" alt="Localisation FawazBTP"
                 onerror="this.src='https://images.unsplash.com/photo-1524661135-423995f22d0b?w=900&h=800&fit=crop'">
        </div>
    </section>

    {{-- Contact --}}
    <section id="contact" class="lp-contact">
        <div class="lp-t lp-form reveal">
            <span class="lp-eyebrow">Contact</span>
            <h2>Parlons de votre projet.</h2>
            <p style="margin:10px 0 24px">Une démo, un devis ou une question technique ? Écrivez-nous.</p>
            <form action="#" method="POST">
                @csrf
                <div class="row">
                    <div class="field"><label for="name">Nom complet <span class="req">*</span></label><input type="text" id="name" name="name" placeholder="Fawaz" required></div>
                    <div class="field"><label for="email">Email <span class="req">*</span></label><input type="email" id="email" name="email" placeholder="Fawaz" required></div>
                </div>
                <div class="field"><label for="phone">Téléphone</label><input type="tel" id="phone" name="phone" placeholder="+229 01 00 00 00 00"></div>
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
                <div class="field"><label for="message">Message <span class="req">*</span></label><textarea id="message" name="message" placeholder="Décrivez votre demande..." required></textarea></div>
                <button type="submit" class="lp-btn dark">Envoyer le message <i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
        <div class="lp-t lp-text lp-info reveal">
            <span class="lp-eyebrow">Pourquoi nous</span>
            <h2>Un accompagnement sérieux et durable.</h2>
            <p class="lead" style="color:#c3cce0">Notre équipe vous accompagne de la mise en place au support quotidien.</p>
            <div class="lp-rows">
                <div class="lp-row"><i class="fas fa-clock"></i><div><strong>Disponibilité</strong><span>Lun — Ven : 8h00 – 18h00</span></div></div>
                <div class="lp-row"><i class="fas fa-headset"></i><div><strong>Support</strong><span>Assistance 24/7 pour les urgences</span></div></div>
                <div class="lp-row"><i class="fas fa-shield-alt"></i><div><strong>Sécurité</strong><span>Données chiffrées, hébergées en France</span></div></div>
                <div class="lp-row"><i class="fas fa-users-cog"></i><div><strong>Expertise</strong><span>Des professionnels du BTP</span></div></div>
            </div>
        </div>
    </section>

    <footer class="lp-foot">
        <div class="lp-foot-grid">
            <div>
                <div class="logo"><b>Fawaz<span>BTP</span></b></div>
                <p>La solution de gestion documentaire pensée pour les projets de construction exigeants.</p>
            </div>
            <div><h5>Produit</h5><ul><li><a href="#apropos">À propos</a></li><li><a href="#services">Services</a></li><li><a href="#processus">Processus</a></li><li><a href="#contact">Contact</a></li></ul></div>
            <div><h5>Ressources</h5><ul><li><a href="#">Documentation</a></li><li><a href="#">Support</a></li><li><a href="#">API</a></li><li><a href="#">Actualités</a></li></ul></div>
            <div><h5>Informations</h5><ul><li><a href="#">Mentions légales</a></li><li><a href="#">Confidentialité</a></li><li><a href="#">CGU / CGV</a></li><li><a href="#">Recrutement</a></li></ul></div>
        </div>
        <div class="lp-bottom"><span>&copy; {{ date('Y') }} FawazBTP. Tous droits réservés.</span><span>Conçu par Fawaz</span></div>
    </footer>
</main>

<script>
    // Apparition au défilement
    document.addEventListener('DOMContentLoaded', function () {
        var items = document.querySelectorAll('.reveal');
        if (!('IntersectionObserver' in window)) { items.forEach(function (i) { i.classList.add('in'); }); return; }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
        }, { threshold: .08 });
        items.forEach(function (i) { io.observe(i); });
    });
</script>
</body>
</html>
