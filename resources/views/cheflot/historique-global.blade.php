@extends('layouts.cheflot')

@section('title', 'Historique global des demandes')

@section('content')
<style>
.filters-form {
        display: flex;
        flex-direction: column;
        gap: 14px;
        width: 100%;
    }
.filter-group { min-width: 0; }
.filter-group label {
        display: block;
        font-size: var(--fs-s);
        color: var(--muted);
        font-weight: 700;
        margin-bottom: 6px;
    }
.filter-group select, .filter-group input { width: 100%; min-width: 0; }
.filters-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
.filters-actions > * {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px 20px;
        border-radius: 999px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
    }
.btn-filter { background: var(--accent); color: #fff; border: 0; }
.btn-filter:hover { background: var(--accent-d); }
.btn-reset { background: #fff; color: var(--ink); border: 1.5px solid var(--line); }
.btn-reset:hover { border-color: var(--accent); color: var(--accent-d); }
.btn-export { background: var(--accent-s); color: var(--accent-d); border: 1.5px solid transparent; }
.btn-export:hover { border-color: var(--accent); }
.badge-action {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: var(--fs-s);
        font-weight: 600;
        display: inline-block;
    }
.badge-creation { background: #dbeafe; color: #2563eb; }
.badge-assignation { background: #fef3c7; color: #d97706; }
.badge-validation { background: #d1fae5; color: #059669; }
.badge-rejet { background: #fee2e2; color: #dc2626; }
.header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
@media (max-width: 768px) {
.filters-actions { grid-template-columns: 1fr; }
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
        <div class="filters-fields">
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

        </div>

        <div class="filters-actions">
            <button type="submit" class="btn-filter">
                <i class="fas fa-search"></i> Filtrer
            </button>

            <a href="{{ route('cheflot.historique.global') }}" class="btn-reset">
                <i class="fas fa-undo"></i> Réinitialiser
            </a>

            <a href="{{ route('cheflot.historique.export', request()->query()) }}" class="btn-export">
                <i class="fas fa-download"></i> Exporter CSV
            </a>
        </div>
    </form>
</div>

<div class="table-container">
    <div class="header-actions">
        <h3><i class="fas fa-history"></i> Historique des événements</h3>
        <span style="font-size: var(--fs-m); color: #6b7280;">
            <i class="fas fa-database"></i> {{ $historiques->total() }} événements
        </span>
    </div>

    <table data-no-filter>
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
