@extends('layouts.bureau_etudes_chef')

@section('title', 'Dashboard Chef Bureau d\'Études')

@section('content')
<style>
.badge-archive { background: #e5e7eb; color: #4b5563; }
.text-muted {
        color: #6b7280;
        font-size: var(--fs-m);
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
        @if(Route::has('bureau_etudes.chef.demandes'))
        <a href="{{ route('bureau_etudes.chef.demandes') }}" class="btn-link">
            Voir toutes <i class="fas fa-arrow-right" style="font-size: var(--fs-s);"></i>
        </a>
        @endif
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
                        @if(Route::has('bureau_etudes.chef.demandes.show'))
                        <a href="{{ route('bureau_etudes.chef.demandes.show', $demande->id) }}" class="btn-link" style="font-weight:600;">
                            {{ $demande->numero_demande }}
                        </a>
                        @else
                        <span style="font-weight:600;">{{ $demande->numero_demande }}</span>
                        @endif
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
