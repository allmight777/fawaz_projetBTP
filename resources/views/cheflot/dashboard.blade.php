@extends('layouts.cheflot')

@section('title', 'Dashboard Chef de Lot')

@section('content')
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; border-radius: 20px; padding: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(4,120,87,0.15); }
    .stat-info h3 { font-size: 14px; color: #666; margin-bottom: 8px; }
    .stat-number { font-size: 32px; font-weight: 800; color: #064e3b; }
    .stat-icon { width: 50px; height: 50px; background: rgba(4,120,87,0.1); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #047857; }
    .badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .badge-attente { background: #fef3c7; color: #d97706; }
    .badge-analyse { background: #dbeafe; color: #2563eb; }
    .badge-valide { background: #d1fae5; color: #059669; }
    .badge-corriger { background: #fee2e2; color: #dc2626; }
    .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; }
    .chart-card { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .chart-card h4 { font-size: 16px; font-weight: 700; color: #064e3b; margin-bottom: 15px; }
    .chart-container { position: relative; height: 250px; }
    .chart-container.small { height: 300px; max-width: 300px; margin: 0 auto; }
    .table-container { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 12px; background: #f0fdf4; color: #064e3b; font-weight: 600; font-size: 13px; }
    td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
    .btn-link { color: #047857; text-decoration: none; }
    @media (max-width: 900px) { .charts-grid { grid-template-columns: 1fr; } }
</style>

<div class="stats-grid">
    <div class="stat-card"><div><h3>Total documents reçus</h3><div class="stat-number">{{ $totalDocuments }}</div></div><div class="stat-icon"><i class="fas fa-file-alt"></i></div></div>
    <div class="stat-card"><div><h3>En attente</h3><div class="stat-number">{{ $documentsEnAttente }}</div></div><div class="stat-icon"><i class="fas fa-clock"></i></div></div>
    <div class="stat-card"><div><h3>En analyse</h3><div class="stat-number">{{ $documentsEnAnalyse }}</div></div><div class="stat-icon"><i class="fas fa-search"></i></div></div>
    <div class="stat-card"><div><h3>Validés</h3><div class="stat-number">{{ $documentsValides }}</div></div><div class="stat-icon"><i class="fas fa-check-double"></i></div></div>
    <div class="stat-card"><div><h3>À corriger</h3><div class="stat-number">{{ $documentsACorriger }}</div></div><div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div></div>

</div>

<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-line"></i> Documents reçus (6 derniers mois)</h4>
        <div class="chart-container">
            <canvas id="evolutionChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Origine des documents</h4>
        <div class="chart-container small">
            <canvas id="structureChart"></canvas>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-clock"></i> Documents récents</h3>
        <a href="{{ route('cheflot.documents.recus') }}" class="btn-link">Voir tous <i class="fas fa-arrow-right"></i></a>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr><th>Identifiant</th><th>Titre</th><th>Envoyé par</th><th>Structure</th><th>Statut</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($documentsRecents as $dossier)
                <tr>
                    <td><a href="{{ route('cheflot.documents.recus.show', $dossier->id) }}" class="btn-link">{{ $dossier->identifiant ?? '#'.$dossier->id }}</a></td>
                    <td>{{ Str::limit($dossier->titre, 30) }}</td>
                    <td>{{ $dossier->creePar->full_name ?? '-' }}</td>
                    <td>{{ $dossier->creePar->structure->nom ?? '-' }}</td>
                    <td>
                        <span class="badge
                            @if($dossier->statut == 'valide') badge-valide
                            @elseif($dossier->statut == 'a_corriger') badge-corriger
                            @elseif($dossier->statut == 'en_analyse') badge-analyse
                            @else badge-attente @endif">
                            {{ $dossier->statut }}
                        </span>
                    </td>
                    <td>{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center">Aucun document reçu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'Documents reçus',
                data: {!! json_encode($documentsData) !!},
                borderColor: '#047857',
                backgroundColor: 'rgba(4,120,87,0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const structureCtx = document.getElementById('structureChart').getContext('2d');
    new Chart(structureCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($repartitionStructures)) !!},
            datasets: [{
                data: {!! json_encode(array_values($repartitionStructures)) !!},
                backgroundColor: ['#2563eb', '#7c3aed', '#059669']
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
