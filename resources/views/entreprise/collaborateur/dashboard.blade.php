@extends('layouts.entreprise')

@section('title', 'Dashboard - Collaborateur Entreprise')

@section('content')
<style>
    /* ===== STATS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(37,99,235,0.12);
    }

    .stat-card .stat-info h3 {
        font-size: 13px;
        color: #888;
        font-weight: 500;
        margin: 0 0 6px 0;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .stat-card .stat-number {
        font-size: 28px;
        font-weight: 800;
        color: #1a1a1a;
        line-height: 1;
    }

    .stat-card .stat-number.blue { color: #2563eb; }
    .stat-card .stat-number.green { color: #00a86b; }
    .stat-card .stat-number.orange { color: #ff6b00; }
    .stat-card .stat-number.red { color: #dc2626; }

    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .stat-card .stat-icon.blue-bg { background: rgba(37,99,235,0.1); color: #2563eb; }
    .stat-card .stat-icon.green-bg { background: rgba(0,168,107,0.1); color: #00a86b; }
    .stat-card .stat-icon.orange-bg { background: rgba(255,107,0,0.1); color: #ff6b00; }
    .stat-card .stat-icon.red-bg { background: rgba(220,38,38,0.1); color: #dc2626; }

    /* ===== BADGES ===== */
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-block;
    }

    .badge-attente { background: #fef3c7; color: #d97706; }
    .badge-controle { background: #dbeafe; color: #2563eb; }
    .badge-valide { background: #d1fae5; color: #059669; }
    .badge-rejete { background: #fee2e2; color: #dc2626; }
    .badge-entreprise { background: #eff4ff; color: #2563eb; }

    /* ===== SECTION ENTREPRISE ===== */
    .entreprise-banner {
        background: linear-gradient(135deg, #eff4ff 0%, #dbeafe 100%);
        border-radius: 16px;
        padding: 18px 24px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        border-left: 4px solid #2563eb;
    }

    .entreprise-banner .entreprise-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .entreprise-banner .entreprise-info .entreprise-icon {
        width: 44px;
        height: 44px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2563eb;
        font-size: 20px;
        box-shadow: 0 2px 8px rgba(37,99,235,0.1);
    }

    .entreprise-banner .entreprise-info h3 {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    .entreprise-banner .entreprise-info p {
        color: #555;
        font-size: 13px;
        margin: 2px 0 0 0;
    }

    .entreprise-banner .entreprise-info p i {
        color: #2563eb;
    }

    /* ===== TABLE ===== */
    .table-container {
        background: white;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .table-header h3 {
        font-size: 17px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    .table-header h3 i {
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
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .btn-link:hover {
        color: #1a4fc4;
        gap: 10px;
    }

    .empty-state {
        padding: 40px;
        text-align: center;
        color: #888;
    }

    .empty-state i {
        font-size: 32px;
        display: block;
        margin-bottom: 10px;
        opacity: 0.3;
        color: #2563eb;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .stat-card {
            padding: 16px 18px;
        }

        .stat-card .stat-number {
            font-size: 22px;
        }

        .entreprise-banner {
            flex-direction: column;
            text-align: center;
            padding: 16px 20px;
        }

        .entreprise-banner .entreprise-info {
            flex-direction: column;
        }

        .table-container {
            padding: 16px;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat-card .stat-number {
            font-size: 18px;
        }

        .stat-card .stat-info h3 {
            font-size: 11px;
        }

        .stat-card .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }
    }
</style>

<!-- ===== BANNIÈRE ENTREPRISE ===== -->
<div class="entreprise-banner">
    <div class="entreprise-info">
        <div class="entreprise-icon">
            <i class="fas fa-user-tie"></i>
        </div>
        <div>
            <h3>Mon espace collaborateur</h3>
            <p>
                <i class="fas fa-user"></i> {{ $user->full_name }}
                @if($user->fonction)
                    · <i class="fas fa-briefcase"></i> {{ $user->fonction }}
                @endif
                @if($user->specialite)
                    · <i class="fas fa-microscope"></i> {{ $user->specialite }}
                @endif
            </p>
        </div>
    </div>
    <div>
        <span class="badge badge-entreprise">
            <i class="fas fa-user"></i> Collaborateur
        </span>
    </div>
</div>

<!-- ===== STATS ===== -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Mes documents</h3>
            <div class="stat-number blue">{{ $mesDocuments ?? 0 }}</div>
        </div>
        <div class="stat-icon blue-bg"><i class="fas fa-file-alt"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>En attente</h3>
            <div class="stat-number orange">{{ $documentsEnAttente ?? 0 }}</div>
        </div>
        <div class="stat-icon orange-bg"><i class="fas fa-clock"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Validés</h3>
            <div class="stat-number green">{{ $documentsValides ?? 0 }}</div>
        </div>
        <div class="stat-icon green-bg"><i class="fas fa-check-double"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>À corriger</h3>
            <div class="stat-number red">{{ $documentsACorriger ?? 0 }}</div>
        </div>
        <div class="stat-icon red-bg"><i class="fas fa-edit"></i></div>
    </div>
</div>

<!-- ===== TABLEAU ===== -->
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-file-alt"></i> Mes derniers documents</h3>
        <a href="{{ route('entreprise.documents') }}" class="btn-link">
            Voir tous <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mesDocumentsRecents ?? [] as $document)
                <tr>
                    <td>
                        <a href="#" class="btn-link" style="font-weight:600;">
                            #{{ $document->id ?? 'N/A' }}
                        </a>
                    </td>
                    <td>{{ Str::limit($document->titre ?? 'Sans titre', 40) }}</td>
                    <td>{{ $document->type ?? 'Document' }}</td>
                    <td>
                        <span class="badge
                            @if(($document->statut ?? '') == 'EN ATTENTE') badge-attente
                            @elseif(($document->statut ?? '') == 'EN CONTROLE') badge-controle
                            @elseif(($document->statut ?? '') == 'VALIDE') badge-valide
                            @else badge-rejete @endif">
                            {{ $document->statut ?? 'En attente' }}
                        </span>
                    </td>
                    <td style="font-size:13px; color:#888;">
                        {{ isset($document->created_at) ? $document->created_at->format('d/m/Y H:i') : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            Aucun document trouvé
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
