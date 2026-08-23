@extends('layouts.cheflot')

@section('title', 'Statistiques du contrôle')

@section('content')
<style>
    .filters-bar {
        background: white;
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 24px;
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        align-items: end;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .filters-bar .field { display: flex; flex-direction: column; gap: 6px; }
    .filters-bar label { font-size: 12px; font-weight: 600; color: #6b7280; }
    .filters-bar input, .filters-bar select {
        padding: 9px 12px;
        border: 1px solid #e0e8f0;
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
    }
    .btn-filter {
        background: #047857;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
    }
    .btn-pdf {
        background: linear-gradient(135deg, #dc2626, #991b1b);
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
    }
    .btn-pdf:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; border-radius: 20px; padding: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .stat-info h4 { font-size: 13px; color: #6b7280; margin-bottom: 8px; }
    .stat-number { font-size: 28px; font-weight: 800; color: #064e3b; }
    .stat-icon { width: 46px; height: 46px; background: rgba(4,120,87,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #047857; }

    .charts-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
    .chart-card { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .chart-card h4 { font-size: 15px; font-weight: 700; color: #064e3b; margin-bottom: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
    .chart-container { position: relative; height: 260px; }

    .table-container { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 12px; background: #f0fdf4; color: #064e3b; font-weight: 600; font-size: 13px; }
    td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
    .progress-bar { background: #e5e7eb; border-radius: 10px; height: 8px; overflow: hidden; }
    .progress-fill { background: #047857; height: 100%; border-radius: 10px; }

    @media (max-width: 768px) { .charts-grid { grid-template-columns: 1fr; } .stats-grid { grid-template-columns: 1fr; } }
</style>

<form method="GET" action="{{ route('cheflot.statistiques') }}" class="filters-bar">
    <div class="field">
        <label>Date début</label>
        <input type="date" name="date_debut" value="{{ $dateDebut->format('Y-m-d') }}">
    </div>
    <div class="field">
        <label>Date fin</label>
        <input type="date" name="date_fin" value="{{ $dateFin->format('Y-m-d') }}">
    </div>
    <div class="field">
        <label>Collaborateur</label>
        <select name="collaborateur_id">
            <option value="">Tout le monde</option>
            @foreach($collaborateurs as $c)
                <option value="{{ $c->id }}" {{ $collaborateurId == $c->id ? 'selected' : '' }}>{{ $c->full_name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filtrer</button>
    <a href="{{ route('cheflot.statistiques.export-pdf', request()->query()) }}" class="btn-pdf">
        <i class="fas fa-file-pdf"></i> Télécharger le rapport PDF
    </a>
</form>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-info"><h4>Total documents</h4><div class="stat-number">{{ $stats['total_documents'] }}</div></div><div class="stat-icon"><i class="fas fa-file-alt"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h4>En attente</h4><div class="stat-number">{{ $stats['par_statut']['soumis'] + $stats['par_statut']['en_cours'] }}</div></div><div class="stat-icon"><i class="fas fa-clock"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h4>En analyse</h4><div class="stat-number">{{ $stats['par_statut']['en_analyse'] }}</div></div><div class="stat-icon"><i class="fas fa-search"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h4>Validés</h4><div class="stat-number">{{ $stats['par_statut']['valide'] }}</div></div><div class="stat-icon"><i class="fas fa-check-double"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h4>À corriger</h4><div class="stat-number">{{ $stats['par_statut']['a_corriger'] }}</div></div><div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div></div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Répartition par statut</h4>
        <div class="chart-container"><canvas id="statutChart"></canvas></div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Répartition par structure d'origine</h4>
        <div class="chart-container"><canvas id="structureChart"></canvas></div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-bar"></i> Documents traités par collaborateur</h4>
        <div class="chart-container"><canvas id="collabChart"></canvas></div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-line"></i> Évolution sur la période</h4>
        <div class="chart-container"><canvas id="evolutionChart"></canvas></div>
    </div>
</div>

<div class="table-container">
    <h4 style="font-size:15px; font-weight:700; color:#064e3b; margin-bottom:16px; border-bottom:2px solid #e5e7eb; padding-bottom:10px;">
        <i class="fas fa-list"></i> Détail par type de document
    </h4>
    <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Type de document</th><th>Nombre</th><th>Pourcentage</th><th>Progression</th></tr></thead>
            <tbody>
                @php $totalType = $stats['total_documents'] > 0 ? $stats['total_documents'] : 1; @endphp
                @forelse($stats['par_type'] as $typeNom => $count)
                <tr>
                    <td>{{ $typeNom }}</td>
                    <td>{{ $count }}</td>
                    <td>{{ round(($count / $totalType) * 100, 1) }}%</td>
                    <td style="width:200px;"><div class="progress-bar"><div class="progress-fill" style="width:{{ ($count / $totalType) * 100 }}%;"></div></div></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center">Aucune donnée pour cette période</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    new Chart(document.getElementById('statutChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Soumis', 'En cours', 'En analyse', 'Validés', 'À corriger'],
            datasets: [{
                data: {!! json_encode(array_values($stats['par_statut'])) !!},
                backgroundColor: ['#2563eb', '#f59e0b', '#7c3aed', '#10b981', '#ef4444']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('structureChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($stats['par_structure'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($stats['par_structure'])) !!},
                backgroundColor: ['#2563eb', '#7c3aed', '#059669']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('collabChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($stats['par_collaborateur'])) !!},
            datasets: [{
                label: 'Documents terminés',
                data: {!! json_encode(array_values($stats['par_collaborateur'])) !!},
                backgroundColor: '#047857',
                borderRadius: 8
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    new Chart(document.getElementById('evolutionChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($stats['evolution_mensuelle'], 'mois')) !!},
            datasets: [{
                label: 'Documents reçus',
                data: {!! json_encode(array_column($stats['evolution_mensuelle'], 'total')) !!},
                borderColor: '#047857',
                backgroundColor: 'rgba(4,120,87,0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3,
                pointRadius: 4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
</script>
@endsection
