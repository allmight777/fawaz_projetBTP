@extends('layouts.entreprise')

@section('title', 'Mes dossiers')

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
    .btn-outline-blue {
        background: transparent;
        color: #2563eb;
        border: 2px solid #2563eb;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
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
    .table-container {
        background: white;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        overflow: hidden;
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
    .badge-rejete { background: #fee2e2; color: #dc2626; }
    .badge-en-cours { background: #dbeafe; color: #2563eb; }
    .badge-brouillon { background: #e5e7eb; color: #6b7280; }
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
    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: #888;
    }
    .empty-state i {
        font-size: 48px;
        display: block;
        margin-bottom: 16px;
        opacity: 0.3;
        color: #2563eb;
    }
    .identifiant-tag {
        font-family: monospace;
        font-size: 12px;
        color: #2563eb;
        background: #eff4ff;
        padding: 2px 8px;
        border-radius: 6px;
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-folder-open"></i> Mes dossiers</h2>
    <div>
        <a href="{{ route('entreprise.documents') }}" class="btn-outline-blue">
            <i class="fas fa-file-alt"></i> Voir les documents
        </a>
    </div>
</div>

<div class="table-container">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Identifiant</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Versions</th>
                    <th>Statut</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dossiers as $dossier)
                <tr>
                    <td>
                        <span class="identifiant-tag">{{ $dossier->identifiant ?? '#'.$dossier->id }}</span>
                    </td>
                    <td>{{ Str::limit($dossier->titre ?? 'Sans titre', 40) }}</td>
                    <td>{{ $dossier->documentType->nom ?? '-' }}</td>
                    <td>{{ $dossier->versions->count() }}</td>
                    <td>
                        <span class="badge
                            @if($dossier->statut == 'valide') badge-valide
                            @elseif($dossier->statut == 'en_cours') badge-en-cours
                            @elseif($dossier->statut == 'a_corriger') badge-rejete
                            @elseif($dossier->statut == 'soumis') badge-en-attente
                            @else badge-brouillon @endif">
                            {{ $dossier->statut }}
                        </span>
                    </td>
                    <td style="font-size:13px; color:#888;">
                        {{ $dossier->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <a href="{{ route('entreprise.dossiers.show', $dossier->id) }}" class="btn-link" title="Voir le dossier">
                            <i class="fas fa-eye"></i> Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            Aucun dossier trouvé
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">
        {{ $dossiers->links() }}
    </div>
</div>
@endsection
