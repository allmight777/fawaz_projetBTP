@extends('layouts.cheflot')

@section('title', 'Équipe & statistiques')

@section('content')
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; border-radius: 20px; padding: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(4,120,87,0.15); }
    .stat-info h3 { font-size: 14px; color: #666; margin-bottom: 8px; }
    .stat-number { font-size: 28px; font-weight: 800; color: #064e3b; }
    .stat-icon { width: 50px; height: 50px; background: rgba(4,120,87,0.1); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #047857; }

    .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; }
    .chart-card { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .chart-card h4 { font-size: 16px; font-weight: 700; color: #064e3b; margin-bottom: 15px; }
    .chart-container { position: relative; height: 280px; }
    .chart-container.small { height: 300px; max-width: 300px; margin: 0 auto; }
    @media (max-width: 900px) { .charts-grid { grid-template-columns: 1fr; } }

    .top-card {
        background: linear-gradient(135deg, #047857, #064e3b);
        border-radius: 20px;
        padding: 22px;
        color: white;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 30px;
    }
    .top-card .avatar-big {
        width: 56px; height: 56px; border-radius: 50%; background: rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; flex-shrink: 0;
    }
    .top-card .top-label { font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px; }
    .top-card .top-name { font-size: 18px; font-weight: 700; margin: 2px 0; }
    .top-card .top-detail { font-size: 13px; opacity: 0.9; }

    .controleurs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
    .controleur-card { background: white; border-radius: 20px; padding: 22px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.3s; }
    .controleur-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(4,120,87,0.12); }
    .controleur-header { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
    .avatar {
        width: 46px; height: 46px; border-radius: 50%; background: #047857; color: white;
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; flex-shrink: 0;
    }
    .controleur-name { font-weight: 700; color: #064e3b; font-size: 15px; }
    .controleur-specialite { font-size: 12px; color: #6b7280; margin-top: 2px; }
    .badge-actif { background: #d1fae5; color: #059669; padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; margin-left: auto; }
    .badge-inactif { background: #fee2e2; color: #dc2626; padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; margin-left: auto; }

    .mini-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 14px; }
    .mini-stat .num { font-size: 18px; font-weight: 800; color: #064e3b; }
    .mini-stat .label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; }
    .mini-stat.attente .num { color: #d97706; }
    .mini-stat.cours .num { color: #2563eb; }
    .mini-stat.termine .num { color: #059669; }

    .empty-state { background: white; border-radius: 20px; padding: 50px; text-align: center; color: #6b7280; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
</style>

@php
    $totalCollaborateurs = $controleurs->count();
    $totalActifs = $controleurs->where('actif', true)->count();
    $totalTermines = $controleurs->sum(fn($c) => $c->stats['termine']);
    $totalEnCours = $controleurs->sum(fn($c) => $c->stats['en_cours']);
@endphp

<div class="stats-grid">
    <div class="stat-card"><div><h3>Collaborateurs</h3><div class="stat-number">{{ $totalCollaborateurs }}</div></div><div class="stat-icon"><i class="fas fa-users"></i></div></div>
    <div class="stat-card"><div><h3>Actifs</h3><div class="stat-number">{{ $totalActifs }}</div></div><div class="stat-icon"><i class="fas fa-user-check"></i></div></div>
    <div class="stat-card"><div><h3>Chefs de structure</h3><div class="stat-number">{{ $totalChefs }}</div></div><div class="stat-icon"><i class="fas fa-user-tie"></i></div></div>
    <div class="stat-card"><div><h3>Documents en cours</h3><div class="stat-number">{{ $totalEnCours }}</div></div><div class="stat-icon"><i class="fas fa-spinner"></i></div></div>
    <div class="stat-card"><div><h3>Documents terminés</h3><div class="stat-number">{{ $totalTermines }}</div></div><div class="stat-icon"><i class="fas fa-check-double"></i></div></div>
</div>

@if($topCollaborateur && $topCollaborateur->stats['termine'] > 0)
<div class="top-card">
    <div class="avatar-big">{{ strtoupper(substr($topCollaborateur->full_name ?? 'C', 0, 1)) }}</div>
    <div>
        <div class="top-label"><i class="fas fa-trophy"></i> Collaborateur le plus performant</div>
        <div class="top-name">{{ $topCollaborateur->full_name }}</div>
        <div class="top-detail">{{ $topCollaborateur->stats['termine'] }} document(s) traité(s) au total</div>
    </div>
</div>
@endif

<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-bar"></i> Documents traités par collaborateur</h4>
        <div class="chart-container">
            <canvas id="collabChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Répartition globale</h4>
        <div class="chart-container small">
            <canvas id="repartitionChart"></canvas>
        </div>
    </div>
</div>

@if($controleurs->isEmpty())
    <div class="empty-state">
        <i class="fas fa-user-slash" style="font-size:36px; margin-bottom:12px; display:block;"></i>
        Aucun collaborateur enregistré pour le moment.
    </div>
@else
    <div class="controleurs-grid">
        @foreach($controleurs as $controleur)
            <div class="controleur-card">
                <div class="controleur-header">
                    <div class="avatar">{{ strtoupper(substr($controleur->full_name ?? 'C', 0, 1)) }}</div>
                    <div>
                        <div class="controleur-name">{{ $controleur->full_name }}</div>
                        <div class="controleur-specialite">
                            <i class="fas fa-tag"></i> {{ $controleur->specialite ?? 'Aucune spécialité' }}
                        </div>
                    </div>
                    @if($controleur->actif)
                        <span class="badge-actif">Actif</span>
                    @else
                        <span class="badge-inactif">Inactif</span>
                    @endif
                </div>

                <div class="mini-stats">
                    <div class="mini-stat">
                        <div class="num">{{ $controleur->stats['total'] }}</div>
                        <div class="label">Total</div>
                    </div>
                    <div class="mini-stat attente">
                        <div class="num">{{ $controleur->stats['en_attente'] }}</div>
                        <div class="label">Attente</div>
                    </div>
                    <div class="mini-stat cours">
                        <div class="num">{{ $controleur->stats['en_cours'] }}</div>
                        <div class="label">En cours</div>
                    </div>
                    <div class="mini-stat termine">
                        <div class="num">{{ $controleur->stats['termine'] }}</div>
                        <div class="label">Terminés</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<script>
    const collabCtx = document.getElementById('collabChart').getContext('2d');
    new Chart(collabCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    label: 'Terminés',
                    data: {!! json_encode($chartDataTermine) !!},
                    backgroundColor: '#059669',
                    borderRadius: 6,
                },
                {
                    label: 'En cours',
                    data: {!! json_encode($chartDataEnCours) !!},
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    const repartitionCtx = document.getElementById('repartitionChart').getContext('2d');
    new Chart(repartitionCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($repartitionGlobale)) !!},
            datasets: [{
                data: {!! json_encode(array_values($repartitionGlobale)) !!},
                backgroundColor: ['#d97706', '#2563eb', '#059669']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
        }
    });
</script>
@endsection
