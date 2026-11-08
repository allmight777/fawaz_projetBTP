@extends(auth()->user()->spaceLayout())

@section('title', 'Statistiques du contrôle')

@section('content')
<style>
.btn-filter {
        background: var(--accent);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: var(--fs-m);
        cursor: pointer;
    }
.btn-pdf {
        background: linear-gradient(135deg, #dc2626, #991b1b);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: var(--fs-m);
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
.btn-pdf:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.progress-bar { background: #e5e7eb; border-radius: 10px; height: 8px; overflow: hidden; }
.progress-fill { background: var(--accent); height: 100%; border-radius: 10px; }
</style>

<form method="GET" action="{{ sroute('cheflot.statistiques') }}" class="filters-bar filters-grid">
    <div class="filters-fields">
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
    </div>
    <div class="filters-actions two">
        <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filtrer</button>
        <a href="{{ sroute('cheflot.statistiques.export-pdf', request()->query()) }}" class="btn-pdf">
            <i class="fas fa-file-pdf"></i> Télécharger le rapport PDF
        </a>
    </div>
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
    <h4 style="font-size: var(--fs-m); font-weight:700; color:var(--accent-d); margin-bottom:16px; border-bottom:2px solid #e5e7eb; padding-bottom:10px;">
        <i class="fas fa-list"></i> Détail par type de document
    </h4>
    <div style="overflow-x:auto;">
        <table data-no-filter>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const accent = getComputedStyle(document.body).getPropertyValue('--accent').trim() || '#047857';
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
                backgroundColor: ['#2563eb', '#7c3aed', accent]
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
                backgroundColor: accent,
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
                borderColor: accent,
                backgroundColor: accent + '22',
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
