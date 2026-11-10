@extends('layouts.admin')

@section('title', 'Tableau de bord Administrateur')

@section('content')
<style>
.stat-change {
        font-size: var(--fs-s);
        color: #4caf50;
        margin-top: 8px;
    }
.chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
.chart-header h4 {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }
.chart-header select {
        background: #f5f5f5;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 5px 10px;
        font-size: var(--fs-m);
        cursor: pointer;
    }
.badge-admin { background: rgba(255,68,68,0.1); color: #ff4444; }
.badge-chef { background: rgba(139,30,63,0.1); color: #8b1e3f; }
.badge-controleur { background: rgba(52,211,153,0.1); color: #34d399; }
</style>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Utilisateurs</h3>
            <div class="stat-number">{{ $totalUsers }}</div>
            <div class="stat-change">
                <i class="fas fa-chart-line"></i> Tous rôles confondus
            </div>
        </div>
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Lots</h3>
            <div class="stat-number">{{ $totalLots }}</div>
            <div class="stat-change">
                <i class="fas fa-layer-group"></i> Projets actifs
            </div>
        </div>
        <div class="stat-icon">
            <i class="fas fa-layer-group"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Collaborateurs de visa</h3>
            <div class="stat-number">{{ $totalControleurs }}</div>
            <div class="stat-change">
                <i class="fas fa-hard-hat"></i> Sur les lots
            </div>
        </div>
        <div class="stat-icon">
            <i class="fas fa-hard-hat"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Utilisateurs Actifs</h3>
            <div class="stat-number">{{ $usersActifs }}</div>
            <div class="stat-change">
                <i class="fas fa-check-circle"></i> En activité
            </div>
        </div>
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h4><i class="fas fa-chart-bar"></i> Répartition par rôle</h4>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="roleChart"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h4><i class="fas fa-chart-pie"></i> Statut des utilisateurs</h4>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h4><i class="fas fa-chart-line"></i> Évolution des inscriptions</h4>
            <select id="yearSelect" onchange="changeYear()">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="evolutionChart"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h4><i class="fas fa-chart-pie"></i> Répartition par lot</h4>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="lotPieChart"></canvas>
        </div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h4><i class="fas fa-chart-bar"></i> Collaborateurs par lot</h4>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="lotBarChart"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h4><i class="fas fa-chart-line"></i> Taux d'activité par lot (%)</h4>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="activityChart"></canvas>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-users"></i> Liste des utilisateurs</h3>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> Ajouter
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table data-no-filter>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Lot</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td><strong>{{ $user->full_name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge
                            @if($user->role == 'ADMIN') badge-admin
                            @elseif($user->role == 'CHEF LOT') badge-chef
                            @else badge-controleur @endif">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td>{{ $user->lot->nom ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $user->actif ? 'badge-actif' : 'badge-inactif' }}">
                            {{ $user->actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" style="color: #8b1e3f; margin-right: 10px;">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none; border:none; color:#dc3545; cursor:pointer;" data-confirm="Cette action est irréversible." data-confirm-title="Supprimer cet élément ?" data-confirm-ok="Supprimer" data-confirm-tone="danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $users->links() }}
    </div>
</div>

<!-- Scripts Chart.js - Placés ICI à l'intérieur de la section -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Récupérer les données PHP
        const roleLabels = @json($roleLabels);
        const roleData = @json($roleData);
        const statusData = @json($statusData);
        const monthlyLabels = @json($monthlyLabels);
        const monthlyData = @json($monthlyData);
        const lotPieLabels = @json($lotPieLabels);
        const lotPieData = @json($lotPieData);
        const lotLabels = @json($lotLabels);
        const lotData = @json($lotData);
        const lotActivityLabels = @json($lotActivityLabels);
        const lotActivityData = @json($lotActivityData);

        // Graphique 1: Répartition par rôle
        const roleCtx = document.getElementById('roleChart');
        if (roleCtx) {
            new Chart(roleCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: roleLabels,
                    datasets: [{
                        label: "Nombre d'utilisateurs",
                        data: roleData,
                        backgroundColor: ['#ff4444', '#8b1e3f', '#34d399'],
                        borderRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }

        // Graphique 2: Statut
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Actifs', 'Inactifs', 'Désactivés', 'En attente'],
                    datasets: [{
                        data: statusData,
                        backgroundColor: ['#34d399', '#f59e0b', '#ef4444', '#9ca3af'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Graphique 3: Évolution des inscriptions
        const evolutionCtx = document.getElementById('evolutionChart');
        if (evolutionCtx) {
            new Chart(evolutionCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Inscriptions',
                        data: monthlyData,
                        borderColor: '#8b1e3f',
                        backgroundColor: 'rgba(139,30,63,0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointBackgroundColor: '#8b1e3f',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }

        // Graphique 4: Camembert lots
        const lotPieCtx = document.getElementById('lotPieChart');
        if (lotPieCtx && lotPieLabels.length > 0) {
            const lotColors = ['#8b1e3f', '#34d399', '#4caf50', '#2196f3', '#9c27b0', '#f44336'];
            new Chart(lotPieCtx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: lotPieLabels,
                    datasets: [{
                        data: lotPieData,
                        backgroundColor: lotColors.slice(0, lotPieLabels.length),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' }
                    }
                }
            });
        }

        // Graphique 5: Barres lots
        const lotBarCtx = document.getElementById('lotBarChart');
        if (lotBarCtx) {
            new Chart(lotBarCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: lotLabels,
                    datasets: [{
                        label: 'Nombre de collaborateurs',
                        data: lotData,
                        backgroundColor: '#8b1e3f',
                        borderRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }

        // Graphique 6: Taux d'activité
        const activityCtx = document.getElementById('activityChart');
        if (activityCtx) {
            new Chart(activityCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: lotActivityLabels,
                    datasets: [{
                        label: "Taux d'activité (%)",
                        data: lotActivityData,
                        borderColor: '#34d399',
                        backgroundColor: 'rgba(52,211,153,0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointBackgroundColor: '#34d399',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    function changeYear() {
        const year = document.getElementById('yearSelect').value;
        window.location.href = '{{ route("admin.dashboard") }}?year=' + year;
    }
</script>
@endsection
