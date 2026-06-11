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
    .badge-controle { background: #dbeafe; color: #2563eb; }
    .badge-valide { background: #d1fae5; color: #059669; }
    .badge-rejete { background: #fee2e2; color: #dc2626; }
    .charts-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
    .chart-card { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .chart-card h4 { font-size: 16px; font-weight: 700; color: #064e3b; margin-bottom: 15px; }
    .table-container { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 12px; background: #f0fdf4; color: #064e3b; font-weight: 600; font-size: 13px; }
    td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
    .btn-link { color: #047857; text-decoration: none; }
    @media (max-width: 768px) { .charts-grid { grid-template-columns: 1fr; } }
</style>

<div class="stats-grid">
    <div class="stat-card"><div><h3>Total Demandes</h3><div class="stat-number">{{ $totalDemandes }}</div></div><div class="stat-icon"><i class="fas fa-file-alt"></i></div></div>
    <div class="stat-card"><div><h3>En Attente</h3><div class="stat-number">{{ $demandesEnAttente }}</div></div><div class="stat-icon"><i class="fas fa-clock"></i></div></div>
    <div class="stat-card"><div><h3>En Contrôle</h3><div class="stat-number">{{ $demandesEnControle }}</div></div><div class="stat-icon"><i class="fas fa-check-circle"></i></div></div>
    <div class="stat-card"><div><h3>Validées</h3><div class="stat-number">{{ $demandesValidees }}</div></div><div class="stat-icon"><i class="fas fa-check-double"></i></div></div>
    <div class="stat-card"><div><h3>Rejetées</h3><div class="stat-number">{{ $demandesRejetees }}</div></div><div class="stat-icon"><i class="fas fa-times-circle"></i></div></div>
    <div class="stat-card"><div><h3>En Retard</h3><div class="stat-number">{{ $demandesEnRetard }}</div></div><div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div></div>
</div>

<div class="charts-grid">
    <div class="chart-card"><h4><i class="fas fa-chart-line"></i> Évolution des demandes (6 mois)</h4><canvas id="evolutionChart" style="height:250px"></canvas></div>
    <div class="chart-card"><h4><i class="fas fa-chart-pie"></i> Demandes par priorité</h4><canvas id="prioriteChart" style="height:250px"></canvas></div>
</div>

<div class="table-container">
    <div class="table-header"><h3><i class="fas fa-clock"></i> Demandes récentes</h3><a href="{{ route('cheflot.demandes') }}" class="btn-link">Voir toutes <i class="fas fa-arrow-right"></i></a></div>
    <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>N°</th><th>Titre</th><th>Soumis par</th><th>Lot</th><th>Priorité</th><th>Statut</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($demandesRecentes as $demande)
                <tr>
                    <td><a href="{{ route('cheflot.demandes.show', $demande->id) }}" class="btn-link">#{{ $demande->numero_demande }}</a></td>
                    <td>{{ $demande->titre_document }}</td>
                    <td>{{ $demande->soumisPar->full_name ?? '-' }}</td>
                    <td>{{ $demande->lot->nom ?? '-' }}</td>
                    <td><span class="badge @if($demande->priorite=='Haute'||$demande->priorite=='Urgente') badge-attente @endif">{{ $demande->priorite }}</span></td>
                    <td><span class="badge @if($demande->statut=='EN ATTENTE') badge-attente @elseif($demande->statut=='EN CONTROLE') badge-controle @elseif($demande->statut=='VALIDE') badge-valide @else badge-rejete @endif">{{ $demande->statut }}</span></td>
                    <td>{{ $demande->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center">Aucune demande</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(evolutionCtx, {
        type: 'line',
        data: { labels: {!! json_encode($months) !!}, datasets: [{ label: 'Demandes', data: {!! json_encode($demandesData) !!}, borderColor: '#047857', backgroundColor: 'rgba(4,120,87,0.1)', tension: 0.4, fill: true }] },
        options: { responsive: true, maintainAspectRatio: true }
    });

    const prioriteCtx = document.getElementById('prioriteChart').getContext('2d');
    new Chart(prioriteCtx, {
        type: 'doughnut',
        data: { labels: ['Basse', 'Moyenne', 'Haute', 'Urgente'], datasets: [{ data: {!! json_encode(array_values($priorites)) !!}, backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#dc2626'] }] },
        options: { responsive: true, maintainAspectRatio: true }
    });
</script>
@endsection
