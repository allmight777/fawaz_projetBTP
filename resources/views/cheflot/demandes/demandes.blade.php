@extends('layouts.cheflot')

@section('title', 'Toutes les demandes')

@section('content')
<style>
    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(4,120,87,0.1); }
    .stat-info h4 { font-size: 13px; color: #6b7280; margin-bottom: 8px; }
    .stat-number { font-size: 28px; font-weight: 800; color: #064e3b; }
    .stat-icon { width: 48px; height: 48px; background: rgba(4,120,87,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #047857; }

    /* Charts */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    .chart-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .chart-card h4 {
        font-size: 16px;
        font-weight: 700;
        color: #064e3b;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Table */
    .table-container {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .filter-btn {
        padding: 8px 16px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .filter-btn.active {
        background: #047857;
        color: white;
    }
    .filter-btn.inactive {
        background: #f3f4f6;
        color: #6b7280;
    }
    .filter-btn.inactive:hover {
        background: #e5e7eb;
    }
    .btn-create {
        background: linear-gradient(135deg, #047857, #065f46);
        color: white;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.3s;
    }
    .btn-create:hover {
        transform: translateY(-2px);
        color: white;
    }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 12px; background: #f0fdf4; color: #064e3b; font-weight: 600; font-size: 13px; }
    td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
    .badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .badge-attente { background: #fef3c7; color: #d97706; }
    .badge-controle { background: #dbeafe; color: #2563eb; }
    .badge-valide { background: #d1fae5; color: #059669; }
    .badge-rejete { background: #fee2e2; color: #dc2626; }
    .badge-basse { background: #d1fae5; color: #059669; }
    .badge-moyenne { background: #fef3c7; color: #d97706; }
    .badge-haute { background: #fee2e2; color: #dc2626; }
    .badge-urgente { background: #fecaca; color: #b91c1c; }
    .btn-link { color: #047857; text-decoration: none; }
    .btn-link:hover { text-decoration: underline; }
    .pagination-wrapper { margin-top: 20px; text-align: center; }
    @media (max-width: 768px) {
        .charts-grid { grid-template-columns: 1fr; }
        .table-container { padding: 15px; overflow-x: auto; }
        th, td { padding: 8px; font-size: 12px; }
    }
</style>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h4>Total Demandes</h4>
            <div class="stat-number">{{ $totalDemandes ?? $demandes->total() }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h4>En Attente</h4>
            <div class="stat-number">{{ $demandesEnAttente ?? \App\Models\Demande::where('statut', 'EN ATTENTE')->count() }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h4>En Contrôle</h4>
            <div class="stat-number">{{ $demandesEnControle ?? \App\Models\Demande::where('statut', 'EN CONTROLE')->count() }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h4>Validées</h4>
            <div class="stat-number">{{ $demandesValidees ?? \App\Models\Demande::where('statut', 'VALIDE')->count() }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h4>Rejetées</h4>
            <div class="stat-number">{{ $demandesRejetees ?? \App\Models\Demande::where('statut', 'REJETE')->count() }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
    </div>
</div>

<!-- Graphiques -->
<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Demandes par priorité</h4>
        <canvas id="prioriteChart" style="height: 250px;"></canvas>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-line"></i> Évolution des demandes (6 mois)</h4>
        <canvas id="evolutionChart" style="height: 250px;"></canvas>
    </div>
</div>

<!-- Tableau des demandes -->
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-file-alt"></i> Liste des demandes</h3>
        <div class="filters">
            <a href="{{ route('cheflot.demandes') }}" class="filter-btn {{ request()->routeIs('cheflot.demandes') && !request()->has('statut') ? 'active' : 'inactive' }}">Toutes</a>
            <a href="{{ route('cheflot.demandes.attente') }}" class="filter-btn {{ request()->routeIs('cheflot.demandes.attente') ? 'active' : 'inactive' }}">En attente</a>
            <a href="{{ route('cheflot.demandes.controle') }}" class="filter-btn {{ request()->routeIs('cheflot.demandes.controle') ? 'active' : 'inactive' }}">En contrôle</a>
            <a href="{{ route('cheflot.demandes.validees') }}" class="filter-btn {{ request()->routeIs('cheflot.demandes.validees') ? 'active' : 'inactive' }}">Validées</a>
            <a href="{{ route('cheflot.demandes.rejetees') }}" class="filter-btn {{ request()->routeIs('cheflot.demandes.rejetees') ? 'active' : 'inactive' }}">Rejetées</a>
        </div>
        <a href="{{ route('demandes.create') }}" class="btn-create">
            <i class="fas fa-plus"></i> Créer une demande
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Titre</th>
                    <th>Soumis par</th>
                    <th>Entreprise</th>
                    <th>Lot</th>
                    <th>Version</th>
                    <th>Priorité</th>
                    <th>Statut</th>
                    <th>Contrôleur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                <tr>
                    <td><a href="{{ route('cheflot.demandes.show', $demande->id) }}" class="btn-link">#{{ $demande->numero_demande }}</a></td>
                    <td>{{ Str::limit($demande->titre_document, 30) }}</td>
                    <td>{{ $demande->soumisPar->full_name ?? '-' }}</td>
                    <td>{{ $demande->entreprise }}</td>
                    <td>{{ $demande->lot->nom ?? '-' }}</td>
                    <td><span class="badge badge-valide">{{ $demande->version }}</span></td>
                    <td><span class="badge badge-{{ strtolower($demande->priorite) }}">{{ $demande->priorite }}</span></td>
                    <td>
                        <span class="badge
                            @if($demande->statut == 'EN ATTENTE') badge-attente
                            @elseif($demande->statut == 'EN CONTROLE') badge-controle
                            @elseif($demande->statut == 'VALIDE') badge-valide
                            @else badge-rejete @endif">
                            {{ $demande->statut }}
                        </span>
                    </td>
                    <td>{{ $demande->controleur->full_name ?? 'Non assigné' }}</td>
                    <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('cheflot.demandes.show', $demande->id) }}" class="btn-link" title="Voir">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('cheflot.demandes.historique', $demande->id) }}" class="btn-link" title="Historique">
                            <i class="fas fa-history"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 48px; color: #ccc;"></i>
                        <p style="margin-top: 10px;">Aucune demande trouvée</p>
                        <a href="{{ route('demandes.create') }}" class="btn-create" style="margin-top: 10px;">
                            <i class="fas fa-plus"></i> Créer la première demande
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $demandes->links() }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Données pour les graphiques - Version CORRIGÉE
    @php
        $prioritesData = isset($priorites) ? $priorites : [
            'Basse' => 0,
            'Moyenne' => 0,
            'Haute' => 0,
            'Urgente' => 0
        ];
        $evolutionData = isset($demandesData) ? $demandesData : [];
        $evolutionLabelsData = isset($months) ? $months : [];
    @endphp

    const prioriteData = {
        labels: {!! json_encode(array_keys($prioritesData)) !!},
        values: {!! json_encode(array_values($prioritesData)) !!}
    };

    const evolutionData = {!! json_encode($evolutionData) !!};
    const evolutionLabels = {!! json_encode($evolutionLabelsData) !!};

    // Graphique des priorités
    const prioriteCtx = document.getElementById('prioriteChart').getContext('2d');
    new Chart(prioriteCtx, {
        type: 'doughnut',
        data: {
            labels: prioriteData.labels,
            datasets: [{
                data: prioriteData.values,
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#dc2626'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Graphique d'évolution
    if (evolutionLabels.length > 0) {
        const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
        new Chart(evolutionCtx, {
            type: 'line',
            data: {
                labels: evolutionLabels,
                datasets: [{
                    label: 'Nombre de demandes',
                    data: evolutionData,
                    borderColor: '#047857',
                    backgroundColor: 'rgba(4,120,87,0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointBackgroundColor: '#047857',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'top' } }
            }
        });
    }
</script>
@endsection
