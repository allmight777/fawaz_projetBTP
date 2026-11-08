@extends(auth()->user()->spaceLayout())

@section('title', 'Analyse du document')

@section('content')
@php
    $version = $documentAssignment->documentVersion;
    $dossier = $version->dossier;
    $checklistItems = $dossier->documentType->checklistItems ?? collect();
    $reponsesMap = $version->checklistReponses->keyBy('checklist_item_id');
    $dejaTermine = $documentAssignment->statut === 'termine';
    $nbCochees = $checklistItems->filter(fn ($i) => optional($reponsesMap->get($i->id))->valeur)->count();
@endphp

<div class="page-header">
    <h1>Analyse · {{ $dossier->identifiant_affiche ?? '#'.$dossier->id }}</h1>
    <a href="{{ sroute('controleur.affectations.index') }}" class="btn-back">Retour</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

<div class="an-top">
    <div class="card">
        <h3 class="card-title">Dossier à analyser</h3>
        <div class="an-grid">
            <div><small>Titre</small><strong>{{ $dossier->titre }}</strong></div>
            <div><small>Type de document</small><strong>{{ $dossier->documentType->nom ?? '—' }}</strong></div>
            <div><small>Version</small><strong>V{{ $version->numero_version }} · {{ $version->nom_affiche }}</strong></div>
            @if($documentAssignment->specialite)
                <div><small>Spécialité demandée</small><strong>{{ $documentAssignment->specialite }}</strong></div>
            @endif
        </div>
        <div class="an-files">
            <a href="{{ sroute('controleur.affectations.apercu', ['documentAssignment' => $documentAssignment->id, 'dossier' => $dossier->id, 'version' => $version->id]) }}" target="_blank" class="btn">Ouvrir l'aperçu</a>
            <a href="{{ route('dossiers.telecharger', ['dossier' => $dossier->id, 'version' => $version->id]) }}" class="btn">Télécharger</a>
        </div>
    </div>

    @if($documentAssignment->date_limite && !$dejaTermine)
        <div class="card an-delai">
            <h3 class="card-title">Délai de traitement</h3>
            <x-delai-gauge :assignment="$documentAssignment" variant="circle" />
        </div>
    @endif
</div>

{{-- Un seul formulaire : « Marquer mon analyse comme terminée » enregistre tout. --}}
<form action="{{ sroute('controleur.affectations.terminer', $documentAssignment) }}" method="POST" id="analyseForm" class="an-form">
    @csrf

    @if($checklistItems->count() > 0)
    <div class="card">
        <div class="an-bar">
            <h3 class="card-title" style="margin:0;">Vérifications</h3>
            <div class="an-progress">
                <span><b id="anCount">{{ $nbCochees }}</b> / {{ $checklistItems->count() }} validées</span>
                <div class="an-meter"><i id="anMeter" style="width: {{ $checklistItems->count() ? round($nbCochees / $checklistItems->count() * 100) : 0 }}%"></i></div>
            </div>
            @unless($dejaTermine)
                <button type="button" class="btn" id="btnCheckAll">Tout cocher</button>
            @endunless
        </div>

        <div class="an-items">
            @foreach($checklistItems as $item)
                @php $reponse = $reponsesMap->get($item->id); @endphp
                <label class="an-item">
                    <input type="checkbox" class="checklist-box" name="reponses[{{ $item->id }}]" value="1" {{ $reponse && $reponse->valeur ? 'checked' : '' }} {{ $dejaTermine ? 'disabled' : '' }}>
                    <span class="an-box"></span>
                    <span class="an-label">{{ $item->libelle }}</span>
                    @if($item->obligatoire)<em class="an-req">Obligatoire</em>@endif
                </label>
            @endforeach
        </div>
    </div>
    @endif

    @if($documentAssignment->observations->count() > 0)
    <div class="card">
        <h3 class="card-title">Mes observations précédentes</h3>
        <div class="ver-list">
            @foreach($documentAssignment->observations as $obs)
                <blockquote class="ver-quote {{ $obs->type === 'non_conformite' ? 'nc' : '' }}">
                    <div class="ver-quote-head">
                        <span>{{ $obs->type === 'non_conformite' ? 'Non-conformité' : 'Observation' }}</span>
                        <small>{{ $obs->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <p>{{ $obs->contenu }}</p>
                </blockquote>
            @endforeach
        </div>
    </div>
    @endif

    @unless($dejaTermine)
    <div class="card">
        <h3 class="card-title">Observation</h3>
        <p class="an-help">Facultative : si vous la renseignez, elle est envoyée avec votre fin d'analyse.</p>
        <div class="an-types">
            <label class="an-type"><input type="radio" name="type" value="observation" {{ old('type', 'observation') === 'observation' ? 'checked' : '' }}><span>Observation</span></label>
            <label class="an-type nc"><input type="radio" name="type" value="non_conformite" {{ old('type') === 'non_conformite' ? 'checked' : '' }}><span>Non-conformité</span></label>
        </div>
        <textarea name="contenu" rows="5" class="an-text" placeholder="Détaillez votre observation…">{{ old('contenu') }}</textarea>
    </div>

    <div class="an-final">
        <p>Vous ne validez ni ne rejetez le document : la décision appartient au responsable du Bureau de Contrôle. Ce bouton enregistre vos vérifications, envoie votre observation et clôture votre analyse.</p>
        <button type="submit" class="an-submit">Marquer mon analyse comme terminée</button>
    </div>
    @else
    <div class="alert alert-info">Votre analyse est terminée. Le responsable du Bureau de Contrôle consolidera les observations et prendra la décision finale.</div>
    @endunless
</form>

<script>
    (function () {
        var boxes = Array.prototype.slice.call(document.querySelectorAll('.checklist-box'));
        var count = document.getElementById('anCount'), meter = document.getElementById('anMeter'), btn = document.getElementById('btnCheckAll');
        if (!boxes.length) return;

        function maj() {
            var n = boxes.filter(function (b) { return b.checked; }).length;
            count.textContent = n;
            meter.style.width = Math.round(n / boxes.length * 100) + '%';
            if (btn) btn.textContent = n === boxes.length ? 'Tout décocher' : 'Tout cocher';
        }
        boxes.forEach(function (b) { b.addEventListener('change', maj); });
        if (btn) btn.addEventListener('click', function () {
            var cible = !boxes.every(function (b) { return b.checked; });
            boxes.forEach(function (b) { if (!b.disabled) b.checked = cible; });
            maj();
        });
        maj();
    })();
</script>
@endsection
