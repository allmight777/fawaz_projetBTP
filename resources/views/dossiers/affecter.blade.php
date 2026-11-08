@extends(auth()->user()->spaceLayout())

@section('title', 'Affecter des collaborateurs')

@section('content')
<style>
label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; }
input[type="text"] { width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: var(--fs-m); }
.controleur-item { display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 8px; }
input[type="datetime-local"] { width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: var(--fs-m); font-family: inherit; }
.btn-delai { background: var(--accent-s); color: var(--accent); border: 1px solid var(--accent-3); padding: 6px 14px; border-radius: 20px; font-size: var(--fs-s); font-weight: 600; cursor: pointer; }
.btn-delai:hover { background: var(--accent); color: white; }
</style>

<div class="form-container">
    <div class="form-title"><i class="fas fa-user-check"></i> Affecter des collaborateurs</div>
    <div class="form-subtitle">{{ $documentVersion->dossier->identifiant }} — {{ $documentVersion->dossier->titre }} (V{{ $documentVersion->numero_version }})</div>

    <form method="POST" action="{{ sroute('cheflot.dossiers.affecter.store', $documentVersion) }}">
        @csrf

        <div class="form-group">
            <label>Collaborateurs</label>
            @foreach($controleurs as $controleur)
                <div class="controleur-item">
                    <input type="checkbox" name="controleurs[]" value="{{ $controleur->id }}" id="controleur-{{ $controleur->id }}">
                    <label for="controleur-{{ $controleur->id }}" style="margin:0; font-weight:400;">{{ $controleur->full_name }} @if($controleur->lot) — {{ $controleur->lot->nom }} @endif</label>
                </div>
            @endforeach
            @error('controleurs') <span style="color:#dc2626; font-size: var(--fs-s);">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label>Spécialité (optionnel)</label>
            <input type="text" name="specialite" placeholder="HSE, géotechnique, topographe, qualité...">
        </div>

        <div class="form-group">
            <label>Délai de traitement (optionnel)</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:8px;">
                <button type="button" class="btn-delai" data-heures="24">+24h</button>
                <button type="button" class="btn-delai" data-heures="48">+48h</button>
                <button type="button" class="btn-delai" data-heures="72">+3 jours</button>
                <button type="button" class="btn-delai" data-heures="120">+5 jours</button>
                <button type="button" class="btn-delai" data-heures="168">+7 jours</button>
            </div>
            <input type="datetime-local" name="date_limite" id="date_limite">
            <p style="font-size: var(--fs-s); color:#6b7280; margin-top:6px;">Date et heure limite pour que le collaborateur rende son analyse.</p>
            @error('date_limite') <span style="color:#dc2626; font-size: var(--fs-s);">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Affecter</button>
    </form>
</div>

<script>
    document.querySelectorAll('.btn-delai').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const heures = parseInt(btn.dataset.heures, 10);
            const date = new Date(Date.now() + heures * 3600 * 1000);
            date.setSeconds(0, 0);
            const offset = date.getTimezoneOffset();
            const local = new Date(date.getTime() - offset * 60000);
            document.getElementById('date_limite').value = local.toISOString().slice(0, 16);
        });
    });
</script>
@endsection
