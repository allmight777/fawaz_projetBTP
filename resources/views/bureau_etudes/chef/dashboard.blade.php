@extends('layouts.bureau_etudes_chef')

@section('title', 'Dashboard Chef Bureau d\'Études')

@section('content')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s, box-shadow 0.3s;
        border-left: 4px solid #047857;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(4,120,87,0.12);
    }
    .stat-info h3 {
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 6px;
        letter-spacing: 0.3px;
    }
    .stat-number {
        font-size: 30px;
        font-weight: 800;
        color: #064e3b;
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        background: rgba(4,120,87,0.08);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #047857;
        flex-shrink: 0;
    }

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .badge-attente { background: #fef3c7; color: #d97706; }
    .badge-analyse { background: #dbeafe; color: #2563eb; }
    .badge-valide { background: #d1fae5; color: #059669; }
    .badge-rejete { background: #fee2e2; color: #dc2626; }
    .badge-archive { background: #e5e7eb; color: #4b5563; }

    .charts-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    .chart-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .chart-card h4 {
        font-size: 15px;
        font-weight: 700;
        color: #064e3b;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .chart-card h4 i {
        color: #047857;
    }
    .chart-container {
        position: relative;
        height: 240px;
    }
    .chart-container.small {
        height: 280px;
        max-width: 280px;
        margin: 0 auto;
    }

    .table-container {
        background: white;
        border-radius: 20px;
        padding: 24px;
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
    .table-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: #064e3b;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .table-header h3 i {
        color: #047857;
    }
    .btn-link {
        color: #047857;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: color 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-link:hover {
        color: #064e3b;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        text-align: left;
        padding: 12px 14px;
        background: #f8fafc;
        color: #1f2937;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }
    td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f3f5;
        font-size: 14px;
        color: #1f2937;
    }
    tr:hover td {
        background: #fafcfa;
    }
    .text-muted {
        color: #6b7280;
        font-size: 13px;
    }

    @media (max-width: 900px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 500px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total demandes</h3>
            <div class="stat-number">{{ $totalDemandes ?? 0 }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
    </div>
    <div class="stat-card" style="border-left-color: #f59e0b;">
        <div class="stat-info">
            <h3>En attente</h3>
            <div class="stat-number">{{ $enAttente ?? 0 }}</div>
        </div>
        <div class="stat-icon" style="background: rgba(245,158,11,0.08); color:#f59e0b;"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stat-card" style="border-left-color: #3b82f6;">
        <div class="stat-info">
            <h3>En contrôle</h3>
            <div class="stat-number">{{ $enControle ?? 0 }}</div>
        </div>
        <div class="stat-icon" style="background: rgba(59,130,246,0.08); color:#3b82f6;"><i class="fas fa-search"></i></div>
    </div>
    <div class="stat-card" style="border-left-color: #10b981;">
        <div class="stat-info">
            <h3>Validées</h3>
            <div class="stat-number">{{ $validees ?? 0 }}</div>
        </div>
        <div class="stat-icon" style="background: rgba(16,185,129,0.08); color:#10b981;"><i class="fas fa-check-double"></i></div>
    </div>
    <div class="stat-card" style="border-left-color: #ef4444;">
        <div class="stat-info">
            <h3>Rejetées</h3>
            <div class="stat-number">{{ $rejetees ?? 0 }}</div>
        </div>
        <div class="stat-icon" style="background: rgba(239,68,68,0.08); color:#ef4444;"><i class="fas fa-times-circle"></i></div>
    </div>
</div>

<!-- CHARTS -->
<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-line"></i> Évolution des demandes (6 derniers mois)</h4>
        <div class="chart-container">
            <canvas id="evolutionChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Répartition par type</h4>
        <div class="chart-container small">
            <canvas id="typeChart"></canvas>
        </div>
    </div>
</div>

<!-- TABLEAU DES DEMANDES RÉCENTES -->
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-clock"></i> Demandes récentes</h3>
        <a href="{{ route('bureau_etudes.chef.demandes') }}" class="btn-link">
            Voir toutes <i class="fas fa-arrow-right" style="font-size:12px;"></i>
        </a>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>N° Demande</th>
                    <th>Titre</th>
                    <th>Soumis par</th>
                    <th>Lot</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandesRecentes ?? [] as $demande)
                <tr>
                    <td>
                        <a href="{{ route('bureau_etudes.chef.demandes.show', $demande->id) }}" class="btn-link" style="font-weight:600;">
                            {{ $demande->numero_demande }}
                        </a>
                    </td>
                    <td>{{ Str::limit($demande->titre_document ?? $demande->titre, 35) }}</td>
                    <td>{{ $demande->soumisPar->full_name ?? $demande->soumisPar->name ?? '-' }}</td>
                    <td>{{ $demande->lot->numero_lot ?? $demande->lot->name ?? '-' }}</td>
                    <td>
                        <span class="badge
                            @if($demande->statut == 'VALIDE') badge-valide
                            @elseif($demande->statut == 'REJETE') badge-rejete
                            @elseif($demande->statut == 'EN CONTROLE') badge-analyse
                            @elseif($demande->statut == 'ARCHIVE') badge-archive
                            @else badge-attente @endif">
                            {{ $demande->statut }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $demande->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px 0; color:#6b7280;">
                        <i class="fas fa-inbox" style="font-size:24px; display:block; margin-bottom:10px; opacity:0.5;"></i>
                        Aucune demande récente
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- CHARTS SCRIPTS -->
@php
    $defaultMonths = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'];
    $defaultEvolution = [0, 0, 0, 0, 0, 0];
    $defaultTypes = ['Plan', 'Rapport', 'Contrat', 'Devis', 'Facture', 'Autre'];
    $defaultTypeData = [0, 0, 0, 0, 0, 0];

    $monthsJson = json_encode($months ?? $defaultMonths);
    $evolutionJson = json_encode($evolutionData ?? $defaultEvolution);
    $typeLabelsJson = json_encode($typeLabels ?? $defaultTypes);
    $typeDataJson = json_encode($typeData ?? $defaultTypeData);
@endphp

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Évolution
        const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
        new Chart(evolutionCtx, {
            type: 'line',
            data: {
                labels: {!! $monthsJson !!},
                datasets: [{
                    label: 'Demandes reçues',
                    data: {!! $evolutionJson !!},
                    borderColor: '#047857',
                    backgroundColor: 'rgba(4,120,87,0.08)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#047857',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // Répartition par type
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: {!! $typeLabelsJson !!},
                datasets: [{
                    data: {!! $typeDataJson !!},
                    backgroundColor: [
                        '#047857', '#059669', '#10b981',
                        '#34d399', '#6ee7b7', '#a7f3d0'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { size: 11, weight: '500' },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                },
                cutout: '65%'
            }
        });
    });
</script>
@endsection
