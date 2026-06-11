@extends('layouts.cheflot')

@section('title', 'Statistiques des demandes')

@section('content')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-info h4 { font-size: 13px; color: #6b7280; margin-bottom: 8px; }
    .stat-number { font-size: 32px; font-weight: 800; color: #064e3b; }
    .stat-icon { width: 50px; height: 50px; background: rgba(4,120,87,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #047857; }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    .chart-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .chart-card h4 {
        font-size: 16px;
        font-weight: 700;
        color: #064e3b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 10px;
    }
    .chart-container { position: relative; height: 280px; }

    .table-container {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .table-container h4 {
        font-size: 16px;
        font-weight: 700;
        color: #064e3b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 10px;
    }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 12px; background: #f0fdf4; color: #064e3b; font-weight: 600; font-size: 13px; }
    td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
    .progress-bar {
        background: #e5e7eb;
        border-radius: 10px;
        height: 8px;
        overflow: hidden;
    }
    .progress-fill {
        background: #047857;
        height: 100%;
        border-radius: 10px;
    }

    @media (max-width: 768px) {
        .charts-grid { grid-template-columns: 1fr; }
        .stats-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info"><h4><i class="fas fa-file-alt"></i> Total demandes</h4><div class="stat-number">{{ $stats['total_demandes'] }}</div></div>
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info"><h4><i class="fas fa-clock"></i> En attente</h4><div class="stat-number">{{ $stats['par_statut']['EN ATTENTE'] ?? 0 }}</div></div>
        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info"><h4><i class="fas fa-check-circle"></i> En contrôle</h4><div class="stat-number">{{ $stats['par_statut']['EN CONTROLE'] ?? 0 }}</div></div>
        <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info"><h4><i class="fas fa-check-double"></i> Validées</h4><div class="stat-number">{{ $stats['par_statut']['VALIDE'] ?? 0 }}</div></div>
        <div class="stat-icon"><i class="fas fa-thumbs-up"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info"><h4><i class="fas fa-times-circle"></i> Rejetées</h4><div class="stat-number">{{ $stats['par_statut']['REJETE'] ?? 0 }}</div></div>
        <div class="stat-icon"><i class="fas fa-thumbs-down"></i></div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Répartition par statut</h4>
        <div class="chart-container">
            <canvas id="statutChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Répartition par priorité</h4>
        <div class="chart-container">
            <canvas id="prioriteChart"></canvas>
        </div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-bar"></i> Demandes par lot</h4>
        <div class="chart-container">
            <canvas id="lotChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-line"></i> Évolution mensuelle (12 mois)</h4>
        <div class="chart-container">
            <canvas id="evolutionChart"></canvas>
        </div>
    </div>
</div>

<div class="table-container">
    <h4><i class="fas fa-chart-simple"></i> Détail des lots</h4>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr><th>Lot</th><th>Nombre de demandes</th><th>Pourcentage</th><th>Progression</th></tr>
            </thead>
            <tbody>
                @php $total = $stats['total_demandes'] > 0 ? $stats['total_demandes'] : 1; @endphp
                @foreach($stats['par_lot'] as $lotNom => $count)
                <tr>
                    <td>{{ $lotNom }}</td>
                    <td>{{ $count }}</td>
                    <td>{{ round(($count / $total) * 100, 1) }}%</td>
                    <td style="width: 200px;"><div class="progress-bar"><div class="progress-fill" style="width: {{ ($count / $total) * 100 }}%;"></div></div></td>
                </tr>
                @endforeach
                @if(count($stats['par_lot']) == 0)
                <tr><td colspan="4" style="text-align:center">Aucune donnée disponible</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique des statuts
        const statutCtx = document.getElementById('statutChart').getContext('2d');
        new Chart(statutCtx, {
            type: 'doughnut',
            data: {
                labels: ['En attente', 'En contrôle', 'Validées', 'Rejetées'],
                datasets: [{
                    data: [
                        {{ $stats['par_statut']['EN ATTENTE'] ?? 0 }},
                        {{ $stats['par_statut']['EN CONTROLE'] ?? 0 }},
                        {{ $stats['par_statut']['VALIDE'] ?? 0 }},
                        {{ $stats['par_statut']['REJETE'] ?? 0 }}
                    ],
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // Graphique des priorités
        const prioriteCtx = document.getElementById('prioriteChart').getContext('2d');
        new Chart(prioriteCtx, {
            type: 'pie',
            data: {
                labels: ['Basse', 'Moyenne', 'Haute', 'Urgente'],
                datasets: [{
                    data: [
                        {{ $stats['par_priorite']['Basse'] ?? 0 }},
                        {{ $stats['par_priorite']['Moyenne'] ?? 0 }},
                        {{ $stats['par_priorite']['Haute'] ?? 0 }},
                        {{ $stats['par_priorite']['Urgente'] ?? 0 }}
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#dc2626'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // Graphique des lots
        const lotCtx = document.getElementById('lotChart').getContext('2d');
        const lotLabels = {!! json_encode(array_keys($stats['par_lot'])) !!};
        const lotData = {!! json_encode(array_values($stats['par_lot'])) !!};
        new Chart(lotCtx, {
            type: 'bar',
            data: {
                labels: lotLabels,
                datasets: [{
                    label: 'Nombre de demandes',
                    data: lotData,
                    backgroundColor: '#047857',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // Graphique d'évolution
        const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
        const evolutionLabels = {!! json_encode(array_column($stats['evolution_mensuelle'], 'mois')) !!};
        const evolutionData = {!! json_encode(array_column($stats['evolution_mensuelle'], 'total')) !!};
        new Chart(evolutionCtx, {
            type: 'line',
            data: {
                labels: evolutionLabels,
                datasets: [{
                    label: 'Demandes créées',
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
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    });
</script>
@endsection
