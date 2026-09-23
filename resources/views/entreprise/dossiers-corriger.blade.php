@extends('layouts.entreprise')

@section('title', 'Corriger le document')

@section('content')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .page-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }
    .page-header h2 i {
        color: #2563eb;
        margin-right: 10px;
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
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        margin-bottom: 20px;
    }
    .card h3 {
        font-size: 15px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .card h3 i { color: #2563eb; }

    .observation-box {
        background: #fef3c7;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
    }
    .observation-box .obs-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 12px;
        color: #92400e;
        font-weight: 600;
    }
    .observation-box p {
        margin: 0;
        color: #333;
        font-size: 14px;
        line-height: 1.5;
    }
    .no-observation {
        color: #888;
        font-size: 13px;
        text-align: center;
        padding: 16px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0eeea;
        font-size: 14px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .label { color: #888; }
    .info-row .value { font-weight: 600; color: #1a1a1a; }

    .form-group { margin-bottom: 16px; }
    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 13px;
        color: #333;
        margin-bottom: 6px;
    }
    .form-group .required { color: #dc2626; }
    .form-group input[type="file"] {
        width: 100%;
        padding: 10px;
        border: 2px dashed #e0e8f0;
        border-radius: 8px;
        background: #fafcff;
        cursor: pointer;
    }
    .form-group textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e0e8f0;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        min-height: 80px;
        resize: vertical;
    }
    .form-group textarea:focus, .form-group input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    .error-text {
        color: #dc2626;
        font-size: 12px;
        margin-top: 6px;
    }

    .btn-submit {
        background: linear-gradient(135deg, #2563eb, #1a4fc4);
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
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(37,99,235,0.3);
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-edit"></i> Corriger le document</h2>
    <a href="{{ route('entreprise.dossiers.show', $dossier->id) }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Retour au dossier
    </a>
</div>

@if(session('error'))
<div style="background:#fee2e2; color:#dc2626; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<!-- Infos dossier -->
<div class="card">
    <h3><i class="fas fa-folder"></i> Informations du dossier</h3>
    <div class="info-row">
        <span class="label">Identifiant</span>
        <span class="value">{{ $dossier->identifiant ?? '#'.$dossier->id }}</span>
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
        <span class="label">Version actuelle</span>
        <span class="value">V{{ $derniereVersion->numero_version ?? 1 }}</span>
    </div>
    @if($derniereVersion)
    <div class="info-row">
        <span class="label">Fichier actuel</span>
        <span class="value">{{ $derniereVersion->nom_affiche }}</span>
    </div>
    @endif
</div>

<!-- Observations du contrôleur -->
<!-- Observations du Bureau de Contrôle -->
<div class="card">
    <h3><i class="fas fa-comment-dots"></i> Observations du Bureau de Contrôle</h3>

    @if(isset($observations) && $observations->count() > 0)
        @foreach($observations as $obs)
            @php
                $auteur = $obs->auteur;
                $affectation = $obs->assignment;
                $statutLabel = $obs->type === 'non_conformite' ? 'Non-conformité' : 'Observation';
                $couleur = $obs->type === 'non_conformite' ? '#dc2626' : '#92400e';
                $bg = $obs->type === 'non_conformite' ? '#fee2e2' : '#fef3c7';
                $border = $obs->type === 'non_conformite' ? '#fca5a5' : '#fde68a';
            @endphp
            <div class="observation-box" style="background:{{ $bg }}; border-color:{{ $border }};">
                <div class="obs-header" style="color:{{ $couleur }};">
                    <span>
                        <i class="fas fa-user-check"></i>
                        {{ $auteur->full_name ?? 'Collaborateur' }}
                        @if($affectation && $affectation->specialite)
                            — <em>{{ $affectation->specialite }}</em>
                        @endif
                        <span style="font-size:11px; font-weight:700; margin-left:6px; text-transform:uppercase;">
                            [{{ $statutLabel }}]
                        </span>
                    </span>
                    <span>{{ $obs->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p>{!! nl2br(e($obs->contenu)) !!}</p>
            </div>
        @endforeach
    @elseif($decision && $decision->commentaires)
        <div class="observation-box">
            <div class="obs-header">
                <span><i class="fas fa-user-check"></i> {{ $decision->validateur->full_name ?? 'Contrôleur' }}</span>
                <span>{{ $decision->date_decision ? $decision->date_decision->format('d/m/Y H:i') : '' }}</span>
            </div>
            <p>{{ $decision->commentaires }}</p>
        </div>
    @else
        <div class="no-observation">
            <i class="fas fa-info-circle"></i> Aucune observation détaillée n'a été laissée. Le document a été marqué "à corriger" sans commentaire précis. N'hésitez pas à contacter le Bureau de Contrôle si besoin.
        </div>
    @endif
</div>

<!-- Vérifications des collaborateurs -->
@if(isset($affectations) && $affectations->count() > 0)
<div class="card">
    <h3><i class="fas fa-list-check"></i> Vérifications effectuées par le Bureau de Contrôle</h3>

    @foreach($affectations as $affectation)
        @php
            $versionAnalysee = $affectation->documentVersion;
            $checklist = $versionAnalysee->checklistReponses ?? collect();
        @endphp

        <div style="margin-bottom:18px; padding:14px 16px; background:#f9fafb; border-radius:10px; border-left:3px solid {{ $affectation->statut === 'termine' ? '#047857' : '#f59e0b' }};">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:10px;">
                <strong style="font-size:14px; color:#1a1a1a;">
                    {{ $affectation->controleur->full_name ?? 'Collaborateur' }}
                    @if($affectation->specialite)
                        <span style="color:#888; font-weight:400;"> — {{ $affectation->specialite }}</span>
                    @endif
                </strong>
                <span style="font-size:11px; padding:3px 10px; border-radius:12px; background:{{ $affectation->statut === 'termine' ? '#d1fae5' : '#fef3c7' }}; color:{{ $affectation->statut === 'termine' ? '#059669' : '#d97706' }}; font-weight:600;">
                    {{ $affectation->statut === 'termine' ? 'Terminé' : 'En attente' }}
                </span>
            </div>

            @if($checklist->count() > 0)
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    @foreach($checklist as $rep)
                        <span style="font-size:11px; padding:3px 10px; border-radius:12px; background:{{ $rep->valeur ? '#d1fae5' : '#f3f4f6' }}; color:{{ $rep->valeur ? '#059669' : '#6b7280' }};">
                            {{ $rep->valeur ? '✓' : '✗' }} {{ $rep->checklistItem->libelle ?? '' }}
                        </span>
                    @endforeach
                </div>
            @else
                <p style="font-size:12px; color:#888; font-style:italic; margin:0;">
                    Aucune vérification enregistrée.
                </p>
            @endif
        </div>
    @endforeach
</div>
@endif

<!-- Formulaire de correction -->
<div class="card">
    <h3><i class="fas fa-upload"></i> Envoyer la version corrigée</h3>

    <form action="{{ route('entreprise.dossiers.corriger.store', $dossier->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>Fichier corrigé <span class="required">*</span></label>
            <input type="file" name="fichier" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar">
            <div style="font-size:12px; color:#888; margin-top:6px;">
                <i class="fas fa-info-circle"></i> Formats acceptés : PDF, Word, Excel, PowerPoint, TXT, ZIP, RAR. Taille max : 5 Mo.
                Ce fichier deviendra automatiquement la <strong>V{{ ($derniereVersion->numero_version ?? 1) + 1 }}</strong> du dossier.
            </div>
            @error('fichier')
                <div class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Commentaire (optionnel)</label>
            <textarea name="commentaire" placeholder="Expliquez brièvement les corrections apportées..."></textarea>
            @error('commentaire')
                <div class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-paper-plane"></i> Envoyer la correction
        </button>
    </form>
</div>
@endsection
