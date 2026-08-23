@extends('layouts.controleur')

@section('title', 'Analyse du document')

@section('content')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .btn-back {
        background: #e5e7eb;
        color: #333;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-back:hover { background: #d1d5db; }

    .card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .card h3 {
        font-size: 15px;
        font-weight: 700;
        color: #064e3b;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .card h3 i { color: #047857; }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .label { color: #6b7280; }
    .info-row .value { font-weight: 600; color: #064e3b; }

    .btn-preview {
        color: #7c3aed;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.3s ease;
        margin-right: 12px;
    }
    .btn-preview:hover { color: #5b21b6; gap: 8px; }

    .btn-download {
        color: #047857;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-download:hover { color: #064e3b; gap: 8px; }

    .checklist-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .checklist-item:last-child { border-bottom: none; }
    .checklist-item input[type="checkbox"] {
        width: 20px; height: 20px;
        accent-color: #047857;
        cursor: pointer;
        flex-shrink: 0;
    }
    .checklist-item .item-label { flex: 1; font-size: 14px; color: #333; }
    .checklist-item .item-badge {
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 12px;
        flex-shrink: 0;
    }
    .item-badge.obligatoire { background: #fee2e2; color: #dc2626; }
    .item-badge.facultatif { background: #f3f4f6; color: #6b7280; }

    .btn-save {
        background: #2563eb;
        color: white;
        border: none;
        padding: 10px 22px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        margin-top: 12px;
    }
    .btn-save:hover { background: #1a4fc4; }

    .observations-list { margin-bottom: 16px; }
    .observation-item {
        background: #f9fafb;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 10px;
        border-left: 3px solid #047857;
    }
    .observation-item .obs-meta {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 4px;
        display: flex;
        justify-content: space-between;
    }
    .observation-item p { font-size: 13px; color: #333; }

    .decision-buttons { display: flex; gap: 12px; margin-bottom: 16px; }
    .decision-option {
        flex: 1;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
    }
    .decision-option:hover { border-color: #047857; background: #f0fdf4; }
    .decision-option input[type="radio"] { display: none; }
    .decision-option i { font-size: 22px; display: block; margin-bottom: 8px; }
    .decision-option.valide i { color: #059669; }
    .decision-option.corriger i { color: #dc2626; }
    .decision-option:has(input[type="radio"]:checked) {
        border-color: #047857;
        background: #f0fdf4;
        box-shadow: 0 0 0 3px rgba(4,120,87,0.1);
    }
    .decision-label { font-size: 13px; font-weight: 600; color: #333; }

    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-weight: 600; font-size: 13px; color: #333; margin-bottom: 6px; }
    .form-group textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e0e8f0;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        min-height: 90px;
        resize: vertical;
    }
    .form-group textarea:focus { outline: none; border-color: #047857; box-shadow: 0 0 0 3px rgba(4,120,87,0.1); }
    .error-text { color: #dc2626; font-size: 12px; margin-top: 6px; }

    .btn-submit {
        background: linear-gradient(135deg, #047857, #064e3b);
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(4,120,87,0.3); }

    .already-done {
        padding: 16px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        color: #064e3b;
        font-size: 14px;
    }
</style>

@php
    $version = $documentAssignment->documentVersion;
    $dossier = $version->dossier;
    $checklistItems = $dossier->documentType->checklistItems ?? collect();
    $reponsesMap = $version->checklistReponses->keyBy('checklist_item_id');
    $dejaTermine = $documentAssignment->statut === 'termine';
@endphp

@if($documentAssignment->date_limite && !$dejaTermine)
<div class="card" style="text-align:center;">
    <h3 style="justify-content:center;"><i class="fas fa-hourglass-half"></i> Délai de traitement</h3>
    <x-delai-gauge :assignment="$documentAssignment" variant="circle" />
</div>
@endif

<div class="card">
    <h3><i class="fas fa-folder"></i> Informations du dossier</h3>
    <div class="info-row">
        <span class="label">Identifiant</span>
        <span class="value">{{ $dossier->identifiant_affiche ?? '#'.$dossier->id }}</span>
    </div>
    <div class="info-row">
        <span class="label">Titre</span>
        <span class="value">{{ $dossier->titre }}</span>
    </div>
    <div class="info-row">
        <span class="label">Type de document</span>
        <span class="value">{{ $dossier->documentType->nom ?? '-' }}</span>
    </div>
    <div class="info-row">
        <span class="label">Version</span>
        <span class="value">V{{ $version->numero_version }} — {{ $version->nom_affiche }}</span>
    </div>
    @if($documentAssignment->specialite)
    <div class="info-row">
        <span class="label">Spécialité demandée</span>
        <span class="value">{{ $documentAssignment->specialite }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="label">Fichier</span>
        <div>
            <a href="{{ route('controleur.affectations.apercu', ['documentAssignment' => $documentAssignment->id, 'dossier' => $dossier->id, 'version' => $version->id]) }}" target="_blank" class="btn-preview">
                <i class="fas fa-eye"></i> Aperçu
            </a>
            <a href="{{ route('dossiers.telecharger', ['dossier' => $dossier->id, 'version' => $version->id]) }}" class="btn-download">
                <i class="fas fa-download"></i> Télécharger
            </a>
        </div>
    </div>
</div>

@if($checklistItems->count() > 0)
<div class="card">
    <h3><i class="fas fa-list-check"></i> Vérifications</h3>
    <form action="{{ route('controleur.affectations.reponses', $documentAssignment) }}" method="POST">
        @csrf
        @foreach($checklistItems as $item)
        @php $reponse = $reponsesMap->get($item->id); @endphp
        <div class="checklist-item">
            <input type="checkbox" name="reponses[{{ $item->id }}]" value="1" {{ $reponse && $reponse->valeur ? 'checked' : '' }} {{ $dejaTermine ? 'disabled' : '' }}>
            <span class="item-label">{{ $item->libelle }}</span>
            <span class="item-badge {{ $item->obligatoire ? 'obligatoire' : 'facultatif' }}">
                {{ $item->obligatoire ? 'Obligatoire' : 'Facultatif' }}
            </span>
        </div>
        @endforeach

        @unless($dejaTermine)
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Enregistrer les vérifications</button>
        @endunless
    </form>
</div>
@endif

@unless($dejaTermine)
<div class="card">
    <h3><i class="fas fa-comment-medical"></i> Ajouter une observation</h3>
    <form action="{{ route('controleur.affectations.observations.store', $documentAssignment) }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Type</label>
            <select name="type" required>
                <option value="observation">Observation</option>
                <option value="non_conformite">Non-conformité</option>
            </select>
            @error('type')<div class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label>Contenu</label>
            <textarea name="contenu" required placeholder="Détaillez votre observation..."></textarea>
            @error('contenu')<div class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn-save"><i class="fas fa-paper-plane"></i> Envoyer l'observation</button>
    </form>
</div>
@endunless

@if($documentAssignment->observations->count() > 0)
<div class="card">
    <h3><i class="fas fa-comment-dots"></i> Observations</h3>
    <div class="observations-list">
        @foreach($documentAssignment->observations as $obs)
        <div class="observation-item">
            <div class="obs-meta">
                <span>{{ $obs->auteur->full_name ?? '-' }}</span>
                <span>{{ $obs->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <p>{{ $obs->contenu }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="card">
    <h3><i class="fas fa-flag-checkered"></i> Fin d'analyse</h3>

    @if($dejaTermine)
        <div class="already-done">
            <i class="fas fa-check-circle"></i> Votre analyse est terminée. Le responsable du Bureau de Contrôle consolidera les observations et prendra la décision finale.
        </div>
    @else
        <p style="font-size:13px; color:#6b7280; margin-bottom:14px;">
            Une fois vos vérifications et observations transmises, marquez votre analyse comme terminée. Vous ne validez ni ne rejetez pas le document : la décision finale appartient au responsable du Bureau de Contrôle.
        </p>

        @if(session('error'))
        <div style="background:#fee2e2; color:#dc2626; padding:10px 14px; border-radius:8px; margin-bottom:14px; font-size:13px;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('controleur.affectations.terminer', $documentAssignment) }}" method="POST">
            @csrf
            <button type="submit" class="btn-submit"><i class="fas fa-flag-checkered"></i> Marquer mon analyse comme terminée</button>
        </form>
    @endif
</div>
@endsection
