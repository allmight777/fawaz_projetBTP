@extends(auth()->user()->spaceLayout())

@section('title', 'Tableau de bord')

@section('content')
<div class="stats-grid">
    <div class="stat-card"><div><h3>Mes affectations</h3><div class="stat-number">{{ $totalDocuments }}</div></div><div class="stat-icon"><i class="fas fa-file-alt"></i></div></div>
    <div class="stat-card"><div><h3>En attente</h3><div class="stat-number">{{ $documentsEnAttente }}</div></div><div class="stat-icon"><i class="fas fa-clock"></i></div></div>
    <div class="stat-card"><div><h3>En cours</h3><div class="stat-number">{{ $documentsEnCours }}</div></div><div class="stat-icon"><i class="fas fa-spinner"></i></div></div>
    <div class="stat-card"><div><h3>Terminées</h3><div class="stat-number">{{ $documentsTermines }}</div></div><div class="stat-icon"><i class="fas fa-check-double"></i></div></div>
    <div class="stat-card"><div><h3>En retard</h3><div class="stat-number">{{ $documentsEnRetard }}</div></div><div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div></div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-line"></i> Affectations reçues (6 derniers mois)</h4>
        <div class="chart-container"><canvas id="evolutionChart"></canvas></div>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Répartition par statut</h4>
        <div class="chart-container small"><canvas id="statutChart"></canvas></div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-clock"></i> Affectations récentes</h3>
        <a href="{{ sroute('controleur.affectations.index') }}" class="btn-link">Voir toutes <i class="fas fa-arrow-right"></i></a>
    </div>
    <div style="overflow-x:auto;">
        <table data-no-filter>
            <thead>
                <tr><th>Identifiant</th><th>Titre</th><th>Type</th><th>Statut</th><th>Date limite</th><th>Reçue le</th></tr>
            </thead>
            <tbody>
                @forelse($affectationsRecentes as $affectation)
                    @php $dossier = $affectation->documentVersion->dossier; @endphp
                    <tr>
                        <td><a href="{{ sroute('controleur.affectations.show', $affectation) }}" class="btn-link">{{ $dossier->identifiant_affiche }}</a></td>
                        <td>{{ Str::limit($dossier->titre, 40) }}</td>
                        <td>{{ $dossier->documentType->nom ?? '-' }}</td>
                        <td>
                            <span class="badge {{ ['termine' => 'badge-valide', 'en_cours' => 'badge-controle'][$affectation->statut] ?? 'badge-attente' }}">
                                {{ ['en_attente' => 'En attente', 'en_cours' => 'En cours', 'termine' => 'Terminée'][$affectation->statut] ?? $affectation->statut }}
                            </span>
                        </td>
                        <td>{{ $affectation->date_limite?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $affectation->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center">Aucune affectation</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var accent = getComputedStyle(document.body).getPropertyValue('--accent').trim() || '#047857';
        new Chart(document.getElementById('evolutionChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{ label: 'Affectations', data: {!! json_encode($affectationsData) !!}, borderColor: accent, backgroundColor: accent + '22', tension: 0.4, fill: true, borderWidth: 3, pointBackgroundColor: accent }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
        new Chart(document.getElementById('statutChart').getContext('2d'), {
            type: 'doughnut',
            data: { labels: {!! json_encode($statutLabels) !!}, datasets: [{ data: {!! json_encode($statutData) !!}, backgroundColor: [accent + '55', accent + 'AA', accent], borderWidth: 3, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } } }
        });
    });
</script>
@endpush
