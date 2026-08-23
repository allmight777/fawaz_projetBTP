@extends('layouts.cheflot')

@section('title', 'Documents reçus pour contrôle')

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
    .page-header h2 i { color: #047857; margin-right: 10px; }

    .table-container {
        background: white;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .table-wrapper { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th {
        text-align: left;
        padding: 12px 14px;
        background: #f0fdf4;
        color: #064e3b;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 2px solid #d1fae5;
    }
    td { padding: 12px 14px; border-bottom: 1px solid #f0eeea; color: #333; }
    tr:hover td { background: #f7fdfa; }

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }
    .badge-entreprise { background: #dbeafe; color: #2563eb; }
    .badge-bureau_etudes { background: #ede9fe; color: #7c3aed; }
    .badge-maitre_ouvrage { background: #fce7f3; color: #db2777; }
    .badge-bureau_controle { background: #fef3c7; color: #d97706; }

    .badge-statut {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-soumis { background: #dbeafe; color: #2563eb; }
    .badge-en_cours { background: #fef3c7; color: #d97706; }
    .badge-en_analyse { background: #dbeafe; color: #2563eb; }
    .badge-valide { background: #d1fae5; color: #059669; }
    .badge-a_corriger { background: #fee2e2; color: #dc2626; }

    .btn-link {
        color: #047857;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-link:hover { color: #065f46; }

    .empty-state { padding: 60px 20px; text-align: center; color: #888; }
    .empty-state i { font-size: 48px; display: block; margin-bottom: 16px; opacity: 0.3; color: #047857; }
</style>



<div class="table-container">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Identifiant</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Envoyé par</th>
                    <th>Structure d'origine</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dossiers as $dossier)
                @php
                    $structureType = $dossier->creePar->structure->type ?? null;
                    $structureLabels = [
                        'entreprise' => 'Entreprise',
                        'bureau_etudes' => "Bureau d'Études",
                        'maitre_ouvrage' => "Maître d'Ouvrage",
                        'bureau_controle' => 'Bureau de Contrôle',
                    ];
                @endphp
                <tr>
                    <td><span style="font-family:monospace; font-size:12px; color:#047857;">{{ $dossier->identifiant_affiche ?? '#'.$dossier->id }}</span></td>
                    <td>{{ Str::limit($dossier->titre, 30) }}</td>
                    <td>{{ $dossier->documentType->nom ?? '-' }}</td>
                    <td>{{ $dossier->creePar->full_name ?? '-' }}</td>
                    <td>
                        @if($structureType)
                            <span class="badge badge-{{ $structureType }}">{{ $structureLabels[$structureType] ?? $structureType }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <span class="badge-statut badge-{{ $dossier->statut }}">{{ $dossier->statut_label }}</span>
                    </td>
                    <td style="font-size:13px; color:#888;">{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('cheflot.documents.recus.show', $dossier->id) }}" class="btn-link">
                            <i class="fas fa-eye"></i> Analyser
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            Aucun document reçu pour le moment
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
