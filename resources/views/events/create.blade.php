@extends($layout)

@section('title', 'Nouvel événement')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .event-form-page { max-width: 980px; margin: 0 auto; }

    .event-form-header {
        margin-bottom: 22px;
        animation: fadeInUp 0.5s ease-out both;
    }
    .event-form-header h2 { font-size: 24px; font-weight: 800; color: #1a1a1a; margin: 0 0 4px; }
    .event-form-header h2 i { color: var(--accent, #2563eb); margin-right: 10px; }
    .event-form-header p { color: #6b7280; font-size: 13px; margin: 0; }

    .form-section {
        background: white;
        border-radius: 18px;
        padding: 26px 28px;
        box-shadow: 0 2px 14px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        animation: fadeInUp 0.5s ease-out both;
    }
    .form-section:nth-of-type(1) { animation-delay: 0.05s; }
    .form-section:nth-of-type(2) { animation-delay: 0.15s; }
    .form-section:nth-of-type(3) { animation-delay: 0.25s; }

    .section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f0f1f3;
    }
    .section-heading .section-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: var(--accent-bg, #eff6ff);
        color: var(--accent, #2563eb);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .section-heading h3 { font-size: 16px; font-weight: 700; color: #1a1a1a; margin: 0; }
    .section-heading p { font-size: 12px; color: #9ca3af; margin: 2px 0 0; }

    .form-group { margin-bottom: 20px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-row { display: flex; gap: 18px; }
    .form-row .form-group { flex: 1; }
    @media (max-width: 640px) {
        .form-row { flex-direction: column; gap: 0; }
    }

    label { display: block; margin-bottom: 7px; font-weight: 600; color: #374151; font-size: 13px; }
    label .required { color: #dc2626; margin-left: 2px; }

    input[type="text"], input[type="datetime-local"], textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        font-size: 14px;
        font-family: inherit;
        background: #fafbfc;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    input[type="text"]:focus, input[type="datetime-local"]:focus, textarea:focus {
        outline: none;
        border-color: var(--accent, #2563eb);
        background: white;
        box-shadow: 0 0 0 4px var(--accent-bg, #eff6ff);
    }
    textarea { min-height: 100px; resize: vertical; }
    .error-text { color: #dc2626; font-size: 12px; margin-top: 6px; display: flex; align-items: center; gap: 5px; }

    .invite-block { border: 1.5px solid #eef0f3; border-radius: 14px; padding: 18px 20px; }
    .invite-block + .invite-block { margin-top: 16px; }
    .invite-block h4 { font-size: 14px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; display:flex; align-items:center; gap:8px; }
    .invite-block h4 i { color: var(--accent, #2563eb); }
    .invite-block p.hint { font-size: 12px; color: #6b7280; margin-bottom: 12px; }
    .invite-block.disabled { opacity: 0.55; background: #fafafa; }
    .locked-note { font-size: 12px; color: #9ca3af; font-style: italic; margin: 0; }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 4px;
        animation: fadeInUp 0.5s ease-out 0.35s both;
    }
    .btn-submit {
        background: linear-gradient(135deg, var(--accent, #2563eb), var(--accent-dark, #1a4fc4));
        color: white;
        border: none;
        padding: 15px 34px;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 6px 18px -4px var(--accent-bg, rgba(37,99,235,0.35));
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 24px -6px rgba(0,0,0,0.25); }
    .btn-submit i { font-size: 14px; }

    /* Tom Select — s'aligne sur le style des champs du site */
    .ts-wrapper.single .ts-control, .ts-wrapper .ts-control {
        border: 1.5px solid #e5e7eb; border-radius: 12px; padding: 7px 10px; font-family: inherit; font-size: 14px; background: #fafbfc;
    }
    .ts-wrapper.focus .ts-control { border-color: var(--accent, #2563eb); box-shadow: 0 0 0 4px var(--accent-bg, #eff6ff); background: white; }
    .ts-control .item { background: var(--accent-bg, #eff6ff); color: var(--accent-dark, #1a4fc4); border-radius: 7px; padding: 4px 9px; }
    .ts-dropdown .active { background: var(--accent-bg, #eff6ff); color: var(--accent-dark, #1a4fc4); }
</style>

<div class="event-form-page">
    <div class="event-form-header">
        <h2><i class="fas fa-calendar-plus"></i> Nouvel événement</h2>
        <p>Créez un événement et invitez des personnes et/ou des structures entières — les deux peuvent être combinés.</p>
    </div>

    <form method="POST" action="{{ route('events.store') }}">
        @csrf

        <div class="form-section">
            <div class="section-heading">
                <div class="section-icon"><i class="fas fa-circle-info"></i></div>
                <div>
                    <h3>Informations générales</h3>
                    <p>Le titre et le contexte de l'événement</p>
                </div>
            </div>

            <div class="form-group">
                <label>Titre <span class="required">*</span></label>
                <input type="text" name="titre" value="{{ old('titre') }}" placeholder="Réunion de chantier, visite de site..." required>
                @error('titre') <div class="error-text"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Détails de l'événement...">{{ old('description') }}</textarea>
                @error('description') <div class="error-text"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-section">
            <div class="section-heading">
                <div class="section-icon"><i class="fas fa-calendar-day"></i></div>
                <div>
                    <h3>Date et lieu</h3>
                    <p>Quand et où se déroule l'événement</p>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date et heure de début <span class="required">*</span></label>
                    <input type="datetime-local" name="date_debut" value="{{ old('date_debut') }}" required>
                    @error('date_debut') <div class="error-text"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Date et heure de fin</label>
                    <input type="datetime-local" name="date_fin" value="{{ old('date_fin') }}">
                    @error('date_fin') <div class="error-text"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Lieu</label>
                <input type="text" name="lieu" value="{{ old('lieu') }}" placeholder="Adresse, salle, site...">
                @error('lieu') <div class="error-text"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-section">
            <div class="section-heading">
                <div class="section-icon"><i class="fas fa-user-group"></i></div>
                <div>
                    <h3>Destinataires</h3>
                    <p>Qui recevra l'invitation par email</p>
                </div>
            </div>

            <div class="invite-block">
                <h4><i class="fas fa-user"></i> Inviter des personnes</h4>
                <p class="hint">Choisissez une ou plusieurs personnes, quelle que soit leur structure.</p>
                <select id="selectPersonnes" name="destinataires[]" multiple placeholder="Rechercher une personne...">
                    @foreach($utilisateurs as $u)
                        <option value="{{ $u->id }}" @selected(collect(old('destinataires', []))->contains($u->id))>
                            {{ $u->full_name }} — {{ $u->structure?->nom ?? 'Sans structure' }}
                        </option>
                    @endforeach
                </select>
                @error('destinataires') <div class="error-text"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div> @enderror
            </div>

            <div class="invite-block {{ $peutViserStructure ? '' : 'disabled' }}">
                <h4><i class="fas fa-building-user"></i> Inviter une/des structure(s) entière(s)</h4>
                @if($peutViserStructure)
                    <p class="hint">Tous les membres actifs des structures sélectionnées recevront l'invitation.</p>
                    <select id="selectStructures" name="structures[]" multiple placeholder="Rechercher une structure...">
                        @foreach($structures as $structure)
                            <option value="{{ $structure->id }}" @selected(collect(old('structures', []))->contains($structure->id))>
                                {{ $structure->nom }} ({{ $structure->libelle_type }})
                            </option>
                        @endforeach
                    </select>
                    @error('structures') <div class="error-text"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div> @enderror
                @else
                    <p class="locked-note"><i class="fas fa-lock"></i> Réservé aux chefs de structure et aux administrateurs.</p>
                @endif
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Créer et envoyer les invitations</button>
        </div>
    </form>
</div>

<script>
    new TomSelect('#selectPersonnes', {
        plugins: ['remove_button'],
        maxItems: null,
    });

    @if($peutViserStructure)
    new TomSelect('#selectStructures', {
        plugins: ['remove_button'],
        maxItems: null,
    });
    @endif
</script>
@endsection
