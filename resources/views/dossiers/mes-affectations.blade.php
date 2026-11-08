@extends(auth()->user()->spaceLayout())

@section('title', 'Mes affectations')

@section('content')
<style>
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
        font-size: var(--fs-m);
        color: #555;
        background: #f8fdf9;
        padding: 6px 14px;
        border-radius: 20px;
        border: 1px solid var(--accent-3);
    }
.stats-mini .stat-item .number {
        font-weight: 700;
        color: var(--accent-d);
        font-size: var(--fs-m);
    }
.stats-mini .stat-item i {
        color: var(--accent);
        font-size: var(--fs-m);
    }
.dossier-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
.dossier-icon {
        width: 36px;
        height: 36px;
        background: var(--accent-s);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent);
        font-size: 16px;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
.dossier-id {
        font-weight: 600;
        color: var(--accent-d);
        font-size: var(--fs-m);
    }
.dossier-titre {
        color: #333;
        font-size: var(--fs-m);
    }
.version-badge {
        display: inline-block;
        padding: 2px 12px;
        border-radius: 12px;
        font-size: var(--fs-s);
        font-weight: 700;
        background: #eff6ff;
        color: #2563eb;
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
        background: var(--accent-3);
        color: var(--accent-d);
    }
.badge-a_corriger {
        background: #fee2e2;
        color: #dc2626;
    }
.badge-en_analyse {
        background: #e0e7ff;
        color: #4f46e5;
    }
.btn-voir {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
        font-size: var(--fs-m);
        padding: 6px 16px;
        border-radius: 8px;
        border: 1px solid transparent;
        transition: all 0.3s ease;
    }
.btn-voir:hover {
        background: var(--accent-s);
        border-color: var(--accent-3);
        transform: translateX(4px);
        gap: 10px;
    }
.btn-voir i {
        font-size: var(--fs-s);
        transition: transform 0.3s ease;
    }
.btn-voir:hover i {
        transform: translateX(3px);
    }
@media (max-width: 768px) {
.stats-mini {
            width: 100%;
            justify-content: flex-start;
        }
.dossier-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
.dossier-icon {
            width: 28px;
            height: 28px;
            font-size: var(--fs-m);
        }
}
@media (max-width: 480px) {
.btn-voir {
            font-size: var(--fs-s);
            padding: 4px 12px;
        }
.version-badge {
            font-size: var(--fs-s);
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
        <table data-filtres="statut,date">
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
                        <td style="font-size: var(--fs-m); color:#888;">
                            <i class="fas fa-calendar-alt" style="margin-right:4px;"></i>
                            {{ $affectation->date_affectation->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @if($affectation->date_limite)
                                <x-delai-gauge :assignment="$affectation" variant="bar" />
                            @else
                                <span style="font-size: var(--fs-s); color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ sroute('controleur.affectations.show', $affectation) }}" class="btn-voir">
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
