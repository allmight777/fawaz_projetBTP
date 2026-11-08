@extends('layouts.auth')

@section('title', 'Créer un compte')

@section('content')
    <h1>Créer un compte</h1>
    <p class="sub">Espace de gestion Bâtiment &amp; Travaux Publics</p>

    @php
        $etapes = [1 => ['nom', 'prenom', 'email'], 2 => ['structure_id', 'categorie_role', 'fonction', 'specialite', 'lot_id'], 3 => ['password', 'password_confirmation']];
        $depart = 1;
        foreach ($etapes as $n => $champs) { if ($errors->hasAny($champs)) { $depart = $n; break; } }
    @endphp

    <form method="POST" action="{{ route('register') }}" id="wizard" data-depart="{{ $depart }}" novalidate>
        @csrf

        <div class="wiz-head">
            <div class="wiz-bar"><i></i><i></i><i></i></div>
            <div class="wiz-title"><b id="wizN">Étape 1 sur 3</b><span id="wizT">Identité</span></div>
        </div>

        <div class="info-box" id="wizInfo">Votre compte sera créé en attente de validation. Un administrateur devra l'activer et vous attribuer un rôle avant que vous puissiez vous connecter.</div>

        <section class="step" data-step="1" data-titre="Identité">
        <div class="form-group">
            <label>Nom <span class="req">*</span></label>
            <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Votre nom" required autofocus>
            @error('nom')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" value="{{ old('prenom') }}" placeholder="Votre prénom">
            @error('prenom')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="exemple@entreprise-btp.fr" required>
            @error('email')<div class="error">{{ $message }}</div>@enderror
        </div>
        </section>

        <section class="step" data-step="2" data-titre="Organisation">
        <div class="form-group">
            <label>Organisme / Structure <span class="req">*</span></label>
            <select name="structure_id" required>
                <option value="">— Sélectionnez —</option>
                @foreach($structures as $structure)
                    <option value="{{ $structure->id }}" @selected(old('structure_id') == $structure->id)>
                        {{ $structure->nom }} ({{ ucfirst(str_replace('_', ' ', $structure->type)) }})
                    </option>
                @endforeach
            </select>
            @error('structure_id')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Catégorie de rôle <span class="req">*</span></label>
            <select name="categorie_role" required>
                <option value="">— Sélectionnez —</option>
                <option value="responsable_organisme" @selected(old('categorie_role') == 'responsable_organisme')>Responsable d'organisme</option>
                <option value="collaborateur" @selected(old('categorie_role') == 'collaborateur')>Collaborateur</option>
            </select>
            @error('categorie_role')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Fonction</label>
            <input type="text" name="fonction" value="{{ old('fonction') }}" placeholder="Votre fonction">
            @error('fonction')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Spécialité</label>
            <input type="text" name="specialite" value="{{ old('specialite') }}" placeholder="Votre spécialité">
            @error('specialite')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Lot</label>
            <select name="lot_id">
                <option value="">— Aucun lot —</option>
                @foreach($lots as $lot)
                    <option value="{{ $lot->id }}" @selected(old('lot_id') == $lot->id)>{{ $lot->code }} - {{ $lot->nom }}</option>
                @endforeach
            </select>
            @error('lot_id')<div class="error">{{ $message }}</div>@enderror
        </div>
        </section>

        <section class="step" data-step="3" data-titre="Sécurité">
        <div class="form-group">
            <label>Mot de passe <span class="req">*</span></label>
            <input type="password" name="password" placeholder="Votre mot de passe" required>
            @error('password')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Confirmer <span class="req">*</span></label>
            <input type="password" name="password_confirmation" placeholder="Confirmez" required>
            @error('password_confirmation')<div class="error">{{ $message }}</div>@enderror
        </div>
        </section>

        <div class="wiz-actions">
            <button type="button" class="btn-ghost" id="wizBack" hidden>Retour</button>
            <button type="button" class="btn-login" id="wizNext">Continuer</button>
            <button type="submit" class="btn-login" id="wizSubmit" hidden>Créer mon compte</button>
        </div>

        <div class="auth-foot">
            <a href="{{ url('/') }}">← Retour à l'accueil</a>
            <span>Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a></span>
        </div>
    </form>

    <script>
        (function () {
            var form = document.getElementById('wizard');
            var steps = Array.prototype.slice.call(form.querySelectorAll('.step'));
            var bars = form.querySelectorAll('.wiz-bar i');
            var back = document.getElementById('wizBack'), next = document.getElementById('wizNext'), submit = document.getElementById('wizSubmit');
            var courant = parseInt(form.dataset.depart, 10) || 1;
            form.classList.add('on');

            function afficher(n) {
                courant = n;
                steps.forEach(function (s, i) { s.hidden = (i + 1) !== n; });
                bars.forEach(function (b, i) { b.className = i < n ? 'done' : ''; });
                document.getElementById('wizN').textContent = 'Étape ' + n + ' sur ' + steps.length;
                document.getElementById('wizT').textContent = steps[n - 1].dataset.titre;
                document.getElementById('wizInfo').hidden = n !== 1;
                back.hidden = n === 1;
                next.hidden = n === steps.length;
                submit.hidden = n !== steps.length;
                var premier = steps[n - 1].querySelector('input, select');
                if (premier) premier.focus({ preventScroll: true });
            }
            function valide() {
                var champs = steps[courant - 1].querySelectorAll('input, select');
                for (var i = 0; i < champs.length; i++) { if (!champs[i].checkValidity()) { champs[i].reportValidity(); return false; } }
                if (courant === steps.length) {
                    var p = form.querySelector('[name=password]'), c = form.querySelector('[name=password_confirmation]');
                    if (p.value !== c.value) { c.setCustomValidity('Les mots de passe ne correspondent pas.'); c.reportValidity(); c.addEventListener('input', function () { c.setCustomValidity(''); }, { once: true }); return false; }
                }
                return true;
            }
            next.addEventListener('click', function () { if (valide()) afficher(courant + 1); });
            back.addEventListener('click', function () { afficher(courant - 1); });
            form.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && courant < steps.length) { e.preventDefault(); next.click(); }
            });
            form.addEventListener('submit', function (e) { if (!valide()) e.preventDefault(); });
            afficher(courant);
        })();
    </script>
@endsection
