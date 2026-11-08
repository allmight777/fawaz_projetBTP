

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Validés</div>
        <div class="stat-number">{{ $stats['validesCount'] }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">En cours de visa</div>
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
        <h4><i class="fas fa-chart-column"></i> Validés / À corriger / En cours de visa</h4>
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
                { label: 'En cours de visa', data: [{{ $stats['enAnalyseCount'] }}], backgroundColor: '#2563eb', borderRadius: 6 },
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
