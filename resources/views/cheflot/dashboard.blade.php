@extends(auth()->user()->spaceLayout())

@section('title', 'Tableau de bord')

@section('content')


<div class="stats-grid">
    <div class="stat-card"><div><h3>Documents reçus</h3><div class="stat-number">{{ $totalDocuments }}</div></div><div class="stat-icon"><i class="fas fa-file-alt"></i></div></div>
    <div class="stat-card"><div><h3>En attente de mon visa</h3><div class="stat-number">{{ $documentsEnAttente }}</div></div><div class="stat-icon"><i class="fas fa-clock"></i></div></div>
    <div class="stat-card"><div><h3>En analyse</h3><div class="stat-number">{{ $documentsEnAnalyse }}</div></div><div class="stat-icon"><i class="fas fa-search"></i></div></div>
    <div class="stat-card"><div><h3>Visés par ma structure</h3><div class="stat-number">{{ $documentsValides }}</div></div><div class="stat-icon"><i class="fas fa-check-double"></i></div></div>
    <div class="stat-card"><div><h3>À corriger</h3><div class="stat-number">{{ $documentsACorriger }}</div></div><div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div></div>
    <div class="stat-card"><div><h3>Informatifs reçus</h3><div class="stat-number">{{ $documentsInformatifs }}</div></div><div class="stat-icon"><i class="fas fa-paper-plane"></i></div></div>

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
        <a href="{{ sroute('cheflot.documents.recus') }}" class="btn-link">Voir tous <i class="fas fa-arrow-right"></i></a>
    </div>
    <div style="overflow-x:auto;">
        <table data-no-filter>
            <thead>
                <tr><th>Identifiant</th><th>Titre</th><th>Envoyé par</th><th>Structure</th><th>Statut</th><th>Visas</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($documentsRecents as $dossier)
                <tr>
                    <td><a href="{{ sroute('cheflot.documents.recus.show', $dossier->id) }}" class="btn-link">{{ $dossier->identifiant ?? '#'.$dossier->id }}</a></td>
                    <td>{{ Str::limit($dossier->titre, 30) }}</td>
                    <td>{{ $dossier->creePar->full_name ?? '-' }}</td>
                    <td>{{ $dossier->creePar->structure->nom ?? '-' }}</td>
                    <td>
                        <span class="badge
                            @if($dossier->statut == 'valide') badge-valide
                            @elseif($dossier->statut == 'a_corriger') badge-corriger
                            @elseif($dossier->statut == 'en_analyse') badge-analyse
                            @else badge-attente @endif">
                            {{ $dossier->statut_label }}
                        </span>
                    </td>
                    <td>@include('partials.visas-colonne')</td>
                    <td>{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center">Aucun document reçu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const accent = getComputedStyle(document.body).getPropertyValue('--accent').trim() || '#047857';
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'Documents reçus',
                data: {!! json_encode($documentsData) !!},
                borderColor: accent,
                backgroundColor: accent + '22',
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
                backgroundColor: ['#2563eb', '#047857', '#8B6FD6', '#d97706', '#E91E8C']
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
