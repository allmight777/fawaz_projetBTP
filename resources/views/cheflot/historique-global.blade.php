@extends('layouts.cheflot')

@section('title', 'Historique global des demandes')

@section('content')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .stat-card h3 {
        font-size: 28px;
        font-weight: 800;
        color: #064e3b;
        margin-bottom: 5px;
    }
    .stat-card p {
        font-size: 13px;
        color: #6b7280;
    }
    .filters-bar {
        background: white;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .filters-form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }
    .filter-group {
        flex: 1;
        min-width: 150px;
    }
    .filter-group label {
        display: block;
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 5px;
    }
    .filter-group select, .filter-group input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
    }
    .btn-filter {
        background: #047857;
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
    }
    .btn-reset {
        background: #6b7280;
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        text-decoration: none;
    }
    .btn-export {
        background: #2563eb;
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .table-container {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        text-align: left;
        padding: 12px;
        background: #f0fdf4;
        color: #064e3b;
        font-weight: 600;
        font-size: 13px;
    }
    td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
    }
    .badge-action {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .badge-creation { background: #dbeafe; color: #2563eb; }
    .badge-assignation { background: #fef3c7; color: #d97706; }
    .badge-validation { background: #d1fae5; color: #059669; }
    .badge-rejet { background: #fee2e2; color: #dc2626; }
    .pagination-wrapper {
        margin-top: 20px;
        text-align: center;
    }
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    @media (max-width: 768px) {
        .filters-form { flex-direction: column; }
        .filter-group { width: 100%; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="stats-grid">
    <div class="stat-card">
        <h3>{{ $stats['total'] }}</h3>
        <p><i class="fas fa-history"></i> Total événements</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['creations'] }}</h3>
        <p><i class="fas fa-plus-circle"></i> Créations</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['assignations'] }}</h3>
        <p><i class="fas fa-user-check"></i> Assignations</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['validations'] }}</h3>
        <p><i class="fas fa-check-circle"></i> Validations</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['rejets'] }}</h3>
        <p><i class="fas fa-times-circle"></i> Rejets</p>
    </div>
</div>

<div class="filters-bar">
    <form method="GET" action="{{ route('cheflot.historique.global') }}" class="filters-form">
        <div class="filter-group">
            <label><i class="fas fa-filter"></i> Action</label>
            <select name="action">
                <option value="">Toutes les actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                        {{ $action }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label><i class="fas fa-tag"></i> Statut</label>
            <select name="statut">
                <option value="">Tous les statuts</option>
                @foreach($statuts as $statut)
                    <option value="{{ $statut }}" {{ request('statut') == $statut ? 'selected' : '' }}>
                        {{ $statut }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label><i class="fas fa-user"></i> Utilisateur</label>
            <select name="user_id">
                <option value="">Tous les utilisateurs</option>
                @foreach($utilisateurs as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->full_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label><i class="fas fa-calendar"></i> Date début</label>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}">
        </div>

        <div class="filter-group">
            <label><i class="fas fa-calendar"></i> Date fin</label>
            <input type="date" name="date_fin" value="{{ request('date_fin') }}">
        </div>

        <button type="submit" class="btn-filter">
            <i class="fas fa-search"></i> Filtrer
        </button>

        <a href="{{ route('cheflot.historique.global') }}" class="btn-reset">
            <i class="fas fa-undo"></i> Réinitialiser
        </a>

        <a href="{{ route('cheflot.historique.export', request()->query()) }}" class="btn-export">
            <i class="fas fa-download"></i> Exporter CSV
        </a>
    </form>
</div>

<div class="table-container">
    <div class="header-actions">
        <h3><i class="fas fa-history"></i> Historique des événements</h3>
        <span style="font-size: 13px; color: #6b7280;">
            <i class="fas fa-database"></i> {{ $historiques->total() }} événements
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Action</th>
                <th>Utilisateur</th>
                <th>Demande N°</th>
                <th>Titre</th>
                <th>Changements</th>
                <th>Détails</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historiques as $h)
            <tr>
                <td>{{ $h->created_at->format('d/m/Y H:i:s') }}</td>
                <td>
                    <span class="badge-action
                        @if($h->action == 'CREATION') badge-creation
                        @elseif($h->action == 'ASSIGNATION') badge-assignation
                        @elseif($h->action == 'VALIDATION') badge-validation
                        @else badge-rejet @endif">
                        @if($h->action == 'CREATION') <i class="fas fa-plus"></i> Création
                        @elseif($h->action == 'ASSIGNATION') <i class="fas fa-user-check"></i> Assignation
                        @elseif($h->action == 'VALIDATION') <i class="fas fa-check"></i> Validation
                        @elseif($h->action == 'REJET') <i class="fas fa-times"></i> Rejet
                        @else {{ $h->action }}
                        @endif
                    </span>
                </td>
                <td>{{ $h->user->full_name ?? '-' }}</td>
                <td>
                    <a href="{{ route('cheflot.demandes.show', $h->demande_id) }}" style="color: #047857;">
                        #{{ $h->demande->numero_demande ?? '-' }}
                    </a>
                </td>
                <td>{{ Str::limit($h->demande->titre_document ?? '-', 40) }}</td>
                <td>
                    @if($h->ancien_statut || $h->nouveau_statut)
                        <span class="badge-action" style="background:#e5e7eb;">
                            {{ $h->ancien_statut ?? '-' }} <i class="fas fa-arrow-right"></i> {{ $h->nouveau_statut ?? '-' }}
                        </span>
                    @endif
                    @if($h->ancienne_version || $h->nouvelle_version)
                        <span class="badge-action" style="background:#e5e7eb;">
                            V{{ $h->ancienne_version ?? '-' }} <i class="fas fa-arrow-right"></i> V{{ $h->nouvelle_version ?? '-' }}
                        </span>
                    @endif
                </td>
                <td style="max-width: 250px;">
                    {{ Str::limit($h->details, 50) ?? '-' }}
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px;">
                        <i class="fas fa-history" style="font-size: 48px; color: #ccc;"></i>
                        <p style="margin-top: 10px;">Aucun historique trouvé</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $historiques->appends(request()->query())->links() }}
    </div>
</div>
@endsection
