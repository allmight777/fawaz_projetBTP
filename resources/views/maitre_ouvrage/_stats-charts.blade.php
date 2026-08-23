<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 24px; }
    .stat-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 4px solid #AD1457; }
    .stat-card .stat-label { font-size: 13px; color: #6b7280; margin-bottom: 6px; }
    .stat-card .stat-number { font-size: 28px; font-weight: 800; color: #1a1a1a; }
    .stat-card.blue { border-left-color: #1565C0; }
    .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
    .chart-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .chart-card h4 { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .chart-card h4 i { color: #AD1457; }
    .chart-container { position: relative; height: 260px; }
    @media (max-width: 900px) { .charts-grid { grid-template-columns: 1fr; } }
</style>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Validés</div>
        <div class="stat-number">{{ $stats['validesCount'] }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">En analyse</div>
        <div class="stat-number">{{ $stats['enAnalyseCount'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">À corriger</div>
        <div class="stat-number">{{ $stats['aCorrigerCount'] }}</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Répartition des statuts</h4>
        <div class="chart-container">
            <canvas id="moaStatutsChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-bar"></i> Documents par type</h4>
        <div class="chart-container">
            <canvas id="moaTypeChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-line"></i> Évolution mensuelle (6 derniers mois)</h4>
        <div class="chart-container">
            <canvas id="moaEvolutionChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-column"></i> Validés / À corriger / En analyse</h4>
        <div class="chart-container">
            <canvas id="moaGroupeChart"></canvas>
        </div>
    </div>
</div>

<script>
    new Chart(document.getElementById('moaStatutsChart'), {
        type: 'doughnut',
        data: {
            labels: @json($stats['repartitionStatutsLabels']),
            datasets: [{
                data: @json($stats['repartitionStatutsData']),
                backgroundColor: ['#2563eb', '#f59e0b', '#059669', '#dc2626', '#6b7280'],
                borderWidth: 0,
            }],
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
    });

    new Chart(document.getElementById('moaTypeChart'), {
        type: 'bar',
        data: {
            labels: @json($stats['parTypeLabels']),
            datasets: [{
                label: 'Documents',
                data: @json($stats['parTypeData']),
                backgroundColor: '#AD1457',
                borderRadius: 6,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    new Chart(document.getElementById('moaEvolutionChart'), {
        type: 'line',
        data: {
            labels: @json($stats['evolutionLabels']),
            datasets: [{
                label: 'Documents reçus',
                data: @json($stats['evolutionData']),
                borderColor: '#1565C0',
                backgroundColor: 'rgba(21,101,192,0.1)',
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    new Chart(document.getElementById('moaGroupeChart'), {
        type: 'bar',
        data: {
            labels: ['Documents'],
            datasets: [
                { label: 'Validés', data: [{{ $stats['validesCount'] }}], backgroundColor: '#059669', borderRadius: 6 },
                { label: 'À corriger', data: [{{ $stats['aCorrigerCount'] }}], backgroundColor: '#dc2626', borderRadius: 6 },
                { label: 'En analyse', data: [{{ $stats['enAnalyseCount'] }}], backgroundColor: '#2563eb', borderRadius: 6 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });
</script>
