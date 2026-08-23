@extends('layouts.controleur')

@section('title', 'Mes affectations')

@section('content')
<style>
    /* ===== ANIMATIONS ===== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -200% 0;
        }
        100% {
            background-position: 200% 0;
        }
    }

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
        color: #064e3b;
        margin: 0;
    }

    .page-header h2 i {
        color: #047857;
        margin-right: 10px;
    }

    .page-header .subtitle {
        font-size: 14px;
        color: #888;
        font-weight: 400;
        margin-top: 2px;
    }

    .stats-mini {
        display: flex;
        gap: 20px;
        align-items: center;
        flex-wrap: wrap;
    }

    .stats-mini .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #555;
        background: #f8fdf9;
        padding: 6px 14px;
        border-radius: 20px;
        border: 1px solid #d1fae5;
    }

    .stats-mini .stat-item .number {
        font-weight: 700;
        color: #064e3b;
        font-size: 15px;
    }

    .stats-mini .stat-item i {
        color: #047857;
        font-size: 14px;
    }

    /* ===== TABLE ===== */
    .table-container {
        background: white;
        border-radius: 20px;
        padding: 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
        animation: fadeInUp 0.5s ease-out;
    }

    .table-header {
        padding: 20px 24px 16px 24px;
        border-bottom: 1px solid #f0fdf4;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .table-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: #064e3b;
        margin: 0;
    }

    .table-header h3 i {
        color: #047857;
        margin-right: 8px;
    }

    .table-header .count-badge {
        background: #f0fdf4;
        color: #047857;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .table-wrapper {
        overflow-x: auto;
        padding: 0 4px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        text-align: left;
        padding: 14px 20px;
        font-size: 11px;
        text-transform: uppercase;
        color: #6b7280;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
        background: #fafdfb;
    }

    td {
        padding: 14px 20px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        vertical-align: middle;
        transition: background 0.2s ease;
    }

    tr {
        transition: all 0.3s ease;
        animation: fadeInUp 0.4s ease-out both;
    }

    tr:nth-child(1) { animation-delay: 0.05s; }
    tr:nth-child(2) { animation-delay: 0.10s; }
    tr:nth-child(3) { animation-delay: 0.15s; }
    tr:nth-child(4) { animation-delay: 0.20s; }
    tr:nth-child(5) { animation-delay: 0.25s; }
    tr:nth-child(6) { animation-delay: 0.30s; }
    tr:nth-child(7) { animation-delay: 0.35s; }
    tr:nth-child(8) { animation-delay: 0.40s; }
    tr:nth-child(9) { animation-delay: 0.45s; }
    tr:nth-child(10) { animation-delay: 0.50s; }

    tr:hover td {
        background: #f8fdf9;
    }

    .dossier-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dossier-icon {
        width: 36px;
        height: 36px;
        background: #f0fdf4;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #047857;
        font-size: 16px;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    tr:hover .dossier-icon {
        background: #d1fae5;
        transform: scale(1.05);
    }

    .dossier-id {
        font-weight: 600;
        color: #064e3b;
        font-size: 13px;
    }

    .dossier-titre {
        color: #333;
        font-size: 13px;
    }

    .version-badge {
        display: inline-block;
        padding: 2px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        background: #eff6ff;
        color: #2563eb;
    }

    /* ===== BADGES ===== */
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-en_attente {
        background: #fef3c7;
        color: #d97706;
    }

    .badge-en_cours {
        background: #dbeafe;
        color: #2563eb;
    }

    .badge-termine {
        background: #d1fae5;
        color: #059669;
    }

    .badge-rejete {
        background: #fee2e2;
        color: #dc2626;
    }

    .badge-valide {
        background: #d1fae5;
        color: #059669;
    }

    .badge-a_corriger {
        background: #fee2e2;
        color: #dc2626;
    }

    .badge-en_analyse {
        background: #e0e7ff;
        color: #4f46e5;
    }

    /* ===== BOUTON ===== */
    .btn-voir {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #047857;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        padding: 6px 16px;
        border-radius: 8px;
        border: 1px solid transparent;
        transition: all 0.3s ease;
    }

    .btn-voir:hover {
        background: #f0fdf4;
        border-color: #d1fae5;
        transform: translateX(4px);
        gap: 10px;
    }

    .btn-voir i {
        font-size: 12px;
        transition: transform 0.3s ease;
    }

    .btn-voir:hover i {
        transform: translateX(3px);
    }

    /* ===== EMPTY STATE ===== */
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
        color: #047857;
    }

    .empty-state h4 {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
        color: #888;
    }

    /* ===== PAGINATION ===== */
    .pagination-wrapper {
        padding: 16px 24px 20px 24px;
        border-top: 1px solid #f0fdf4;
    }

    .pagination-wrapper .pagination {
        display: flex;
        justify-content: center;
        gap: 4px;
    }

    .pagination-wrapper .pagination .page-item {
        list-style: none;
    }

    .pagination-wrapper .pagination .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        border-radius: 8px;
        color: #555;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .pagination-wrapper .pagination .page-link:hover {
        background: #f0fdf4;
        color: #047857;
    }

    .pagination-wrapper .pagination .active .page-link {
        background: #047857;
        color: white;
    }

    .pagination-wrapper .pagination .disabled .page-link {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .stats-mini {
            width: 100%;
            justify-content: flex-start;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        td, th {
            padding: 10px 14px;
            font-size: 13px;
        }

        .dossier-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }

        .dossier-icon {
            width: 28px;
            height: 28px;
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        td, th {
            padding: 8px 10px;
            font-size: 12px;
        }

        .badge {
            font-size: 10px;
            padding: 2px 8px;
        }

        .btn-voir {
            font-size: 12px;
            padding: 4px 12px;
        }

        .version-badge {
            font-size: 10px;
            padding: 1px 8px;
        }
    }
</style>



<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-list"></i> Documents assignés</h3>
        <span class="count-badge">{{ $affectations->total() }} document(s)</span>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Dossier</th>
                    <th>Titre</th>
                    <th>Version</th>
                    <th>Statut</th>
                    <th>Date d'affectation</th>
                    <th>Délai</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($affectations as $affectation)
                    <tr>
                        <td>
                            <div class="dossier-info">
                                <div class="dossier-icon">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <div>
                                    <div class="dossier-id">#{{ $affectation->documentVersion->dossier->id }}</div>
                                    <div class="dossier-titre">{{ Str::limit($affectation->documentVersion->dossier->identifiant ?? 'Sans identifiant', 20) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ Str::limit($affectation->documentVersion->dossier->titre, 35) }}</td>
                        <td>
                            <span class="version-badge">V{{ $affectation->documentVersion->numero_version }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $affectation->statut }}">
                                @if($affectation->statut == 'en_attente')
                                    <i class="fas fa-clock"></i> En attente
                                @elseif($affectation->statut == 'en_cours')
                                    <i class="fas fa-spinner"></i> En cours
                                @elseif($affectation->statut == 'termine')
                                    <i class="fas fa-check-circle"></i> Terminé
                                @else
                                    {{ $affectation->statut }}
                                @endif
                            </span>
                        </td>
                        <td style="font-size:13px; color:#888;">
                            <i class="fas fa-calendar-alt" style="margin-right:4px;"></i>
                            {{ $affectation->date_affectation->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @if($affectation->date_limite)
                                <x-delai-gauge :assignment="$affectation" variant="bar" />
                            @else
                                <span style="font-size:12px; color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('controleur.affectations.show', $affectation) }}" class="btn-voir">
                                Analyser <i class="fas fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h4>Aucune affectation</h4>
                                <p>Vous n'avez pas encore de documents assignés.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($affectations->hasPages())
        <div class="pagination-wrapper">
            {{ $affectations->links() }}
        </div>
    @endif
</div>
@endsection
