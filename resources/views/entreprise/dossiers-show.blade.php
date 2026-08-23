@extends('layouts.entreprise')

@section('title', 'Détail du dossier')

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
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background: #5a6268;
        color: white;
    }
    .btn-primary {
        background: linear-gradient(135deg, #2563eb, #1a4fc4);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(37,99,235,0.3);
        color: white;
    }
    .btn-success {
        background: linear-gradient(135deg, #00a86b, #008a56);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,168,107,0.3);
        color: white;
    }
    .btn-outline-blue {
        background: transparent;
        color: #2563eb;
        border: 2px solid #2563eb;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .btn-outline-blue:hover {
        background: #2563eb;
        color: white;
    }
    .btn-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(245,158,11,0.3);
        color: white;
    }
    .info-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        margin-bottom: 24px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    .info-item {
        display: flex;
        flex-direction: column;
    }
    .info-item .label {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-weight: 600;
    }
    .info-item .value {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
        margin-top: 4px;
    }
    .info-item .value i {
        color: #2563eb;
        margin-right: 6px;
    }
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-block;
    }
    .badge-valide { background: #d1fae5; color: #059669; }
    .badge-en-attente { background: #fef3c7; color: #d97706; }
    .badge-en-cours { background: #dbeafe; color: #2563eb; }
    .badge-rejete { background: #fee2e2; color: #dc2626; }
    .badge-a-corriger { background: #fee2e2; color: #dc2626; }
    .badge-brouillon { background: #e5e7eb; color: #6b7280; }
    .badge-soumis { background: #dbeafe; color: #2563eb; }

    .versions-container {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .versions-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .versions-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }
    .versions-header h3 i {
        color: #2563eb;
        margin-right: 8px;
    }
    .table-wrapper {
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    th {
        text-align: left;
        padding: 12px 14px;
        background: #eff4ff;
        color: #1a1a1a;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 2px solid #dbeafe;
    }
    td {
        padding: 12px 14px;
        border-bottom: 1px solid #f0eeea;
        color: #333;
    }
    tr:hover td {
        background: #f8faff;
    }
    .btn-link {
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.3s ease;
    }
    .btn-link:hover {
        color: #1a4fc4;
        gap: 8px;
    }
    .btn-link-warning {
        color: #d97706;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.3s ease;
    }
    .btn-link-warning:hover {
        color: #b45309;
        gap: 8px;
    }
    .empty-state {
        padding: 40px 20px;
        text-align: center;
        color: #888;
    }
    .empty-state i {
        font-size: 40px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.3;
        color: #2563eb;
    }
    .version-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        background: #eff4ff;
        color: #2563eb;
    }
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        .versions-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-folder-open"></i> Détail du dossier</h2>
    <div>
        <a href="{{ route('entreprise.dossiers') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        @if($dossier->statut === 'a_corriger')
        <a href="{{ route('entreprise.dossiers.corriger', $dossier->id) }}" class="btn-warning">
            <i class="fas fa-edit"></i> Voir et modifier
        </a>
        @endif
    </div>
</div>

<!-- Informations du dossier -->
<div class="info-card">
    <div class="info-grid">
        <div class="info-item">
            <span class="label">Identifiant</span>
            <span class="value"><i class="fas fa-hashtag"></i> {{ $dossier->identifiant_formatted }}</span>
        </div>
        <div class="info-item">
            <span class="label">Titre</span>
            <span class="value">{{ $dossier->titre }}</span>
        </div>
        <div class="info-item">
            <span class="label">Type de document</span>
            <span class="value">{{ $dossier->documentType->nom ?? 'Non défini' }}</span>
        </div>
        <div class="info-item">
            <span class="label">Statut</span>
            <span class="value">
                <span class="badge {{ $dossier->statut_badge }}">
                    {{ $dossier->statut_label }}
                </span>
            </span>
        </div>
        <div class="info-item">
            <span class="label">Créé par</span>
            <span class="value"><i class="fas fa-user"></i> {{ $dossier->creePar->full_name ?? 'Inconnu' }}</span>
        </div>
        <div class="info-item">
            <span class="label">Date de création</span>
            <span class="value"><i class="fas fa-calendar-alt"></i> {{ $dossier->created_at->format('d/m/Y H:i') }}</span>
        </div>
        @if($dossier->description)
        <div class="info-item" style="grid-column: span 2;">
            <span class="label">Description</span>
            <span class="value" style="font-weight:400; color:#555;">{{ $dossier->description }}</span>
        </div>
        @endif
    </div>
</div>

<!-- Versions du dossier -->
<div class="versions-container">
    <div class="versions-header">
        <h3><i class="fas fa-code-branch"></i> Versions du document</h3>
        <span style="font-size:13px; color:#888;">
            <i class="fas fa-file-alt"></i> Total : {{ $dossier->versions->count() }} version(s)
        </span>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Fichier</th>
                    <th>Statut</th>
                    <th>Importé par</th>
                    <th>Date d'import</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dossier->versions->sortByDesc('numero_version') as $version)
                <tr>
                    <td>
                        <span class="version-badge">V{{ $version->numero_version }}</span>
                        @if($loop->first)
                            <span style="font-size:10px; color:#059669; margin-left:4px;">
                                <i class="fas fa-star"></i> Dernière
                            </span>
                        @endif
                    </td>
                    <td>
                        <i class="fas fa-file-alt" style="color:#2563eb;"></i>
                        {{ $version->nom_affiche ?? 'Sans nom' }}
                    </td>
                    <td>
                        <span class="badge
                            @if($version->statut == 'valide') badge-valide
                            @elseif($version->statut == 'en_attente_checklist') badge-en-attente
                            @elseif($version->statut == 'en_analyse') badge-en-cours
                            @elseif($version->statut == 'a_corriger') badge-a-corriger
                            @elseif($version->statut == 'soumis') badge-soumis
                            @else badge-brouillon @endif">
                            {{ $version->statut }}
                        </span>
                    </td>
                    <td>{{ $version->importePar->full_name ?? 'Inconnu' }}</td>
                    <td style="font-size:13px; color:#888;">
                        {{ $version->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                            @if($version->fichier_chemin || $version->fichier_url)
                            <a href="{{ route('entreprise.dossiers.versions.telecharger', ['dossier' => $dossier->id, 'version' => $version->id]) }}" class="btn-link" title="Télécharger">
                                <i class="fas fa-download"></i> Télécharger
                            </a>
                            @endif
                            @if($loop->first && $dossier->statut === 'a_corriger')
                            <a href="{{ route('entreprise.dossiers.corriger', $dossier->id) }}" class="btn-link-warning" title="Modifier">
                                <i class="fas fa-edit"></i> Corriger
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            Aucune version disponible pour ce dossier
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Transmissions -->
@if($dossier->transmissions->count() > 0)
<div class="versions-container" style="margin-top:24px;">
    <div class="versions-header">
        <h3><i class="fas fa-paper-plane"></i> Historique des transmissions</h3>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Mode</th>
                    <th>Commentaire</th>
                    <th>Destinataires</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dossier->transmissions->sortByDesc('created_at') as $transmission)
                <tr>
                    <td style="font-size:13px; color:#888;">
                        {{ $transmission->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <span class="badge {{ $transmission->mode == 'validation' ? 'badge-en-cours' : 'badge-soumis' }}">
                            {{ $transmission->mode == 'validation' ? 'Contrôle' : 'Informatif' }}
                        </span>
                    </td>
                    <td>{{ $transmission->commentaire ?? '-' }}</td>
                    <td>
                        @foreach($transmission->destinataires as $dest)
                            <span style="display:inline-block; background:#f0f0f0; padding:2px 10px; border-radius:12px; font-size:12px; margin:2px;">
                                {{ $dest->user->full_name ?? 'Inconnu' }}
                            </span>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
