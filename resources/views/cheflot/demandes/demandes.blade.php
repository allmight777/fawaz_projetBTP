@extends('layouts.cheflot')

@section('title', 'Toutes les demandes')

@section('content')
<style>
.filter-btn {
        padding: 8px 16px;
        border-radius: 10px;
        text-decoration: none;
        font-size: var(--fs-m);
        font-weight: 500;
        transition: all 0.3s;
    }
.filter-btn.active {
        background: #047857;
        color: white;
    }
.filter-btn.inactive {
        background: #f3f4f6;
        color: #6b7280;
    }
.filter-btn.inactive:hover {
        background: #e5e7eb;
    }
.btn-create {
        background: linear-gradient(135deg, #047857, #065f46);
        color: white;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.3s;
    }
.btn-create:hover {
        transform: translateY(-2px);
        color: white;
    }
.badge-urgente { background: #fecaca; color: #b91c1c; }
</style>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h4>Total Demandes</h4>
            <div class="stat-number">{{ $totalDemandes ?? $demandes->total() }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h4>En Attente</h4>
            <div class="stat-number">{{ $demandesEnAttente ?? \App\Models\Demande::where('statut', 'EN ATTENTE')->count() }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h4>En Contrôle</h4>
            <div class="stat-number">{{ $demandesEnControle ?? \App\Models\Demande::where('statut', 'EN CONTROLE')->count() }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h4>Validées</h4>
            <div class="stat-number">{{ $demandesValidees ?? \App\Models\Demande::where('statut', 'VALIDE')->count() }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h4>Rejetées</h4>
            <div class="stat-number">{{ $demandesRejetees ?? \App\Models\Demande::where('statut', 'REJETE')->count() }}</div>
        </div>
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
    </div>
</div>

<!-- Graphiques -->
<div class="charts-grid">
    <div class="chart-card">
        <h4><i class="fas fa-chart-pie"></i> Demandes par priorité</h4>
        <canvas id="prioriteChart" style="height: 250px;"></canvas>
    </div>
    <div class="chart-card">
        <h4><i class="fas fa-chart-line"></i> Évolution des demandes (6 mois)</h4>
        <canvas id="evolutionChart" style="height: 250px;"></canvas>
    </div>
</div>

<!-- Tableau des demandes -->
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-file-alt"></i> Liste des demandes</h3>
        <div class="filters">
            <a href="{{ route('cheflot.demandes') }}" class="filter-btn {{ request()->routeIs('cheflot.demandes') && !request()->has('statut') ? 'active' : 'inactive' }}">Toutes</a>
            <a href="{{ route('cheflot.demandes.attente') }}" class="filter-btn {{ request()->routeIs('cheflot.demandes.attente') ? 'active' : 'inactive' }}">En attente</a>
            <a href="{{ route('cheflot.demandes.controle') }}" class="filter-btn {{ request()->routeIs('cheflot.demandes.controle') ? 'active' : 'inactive' }}">En contrôle</a>
            <a href="{{ route('cheflot.demandes.validees') }}" class="filter-btn {{ request()->routeIs('cheflot.demandes.validees') ? 'active' : 'inactive' }}">Validées</a>
            <a href="{{ route('cheflot.demandes.rejetees') }}" class="filter-btn {{ request()->routeIs('cheflot.demandes.rejetees') ? 'active' : 'inactive' }}">Rejetées</a>
        </div>
        <a href="{{ route('demandes.create') }}" class="btn-create">
            <i class="fas fa-plus"></i> Créer une demande
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table data-filtres="statut,date">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Titre</th>
                    <th>Soumis par</th>
                    <th>Entreprise</th>
                    <th>Lot</th>
                    <th>Version</th>
                    <th>Priorité</th>
                    <th>Statut</th>
                    <th>Contrôleur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                <tr>
                    <td><a href="{{ route('cheflot.demandes.show', $demande->id) }}" class="btn-link">#{{ $demande->numero_demande }}</a></td>
                    <td>{{ Str::limit($demande->titre_document, 30) }}</td>
                    <td>{{ $demande->soumisPar->full_name ?? '-' }}</td>
                    <td>{{ $demande->entreprise }}</td>
                    <td>{{ $demande->lot->nom ?? '-' }}</td>
                    <td><span class="badge badge-valide">{{ $demande->version }}</span></td>
                    <td><span class="badge badge-{{ strtolower($demande->priorite) }}">{{ $demande->priorite }}</span></td>
                    <td>
                        <span class="badge
                            @if($demande->statut == 'EN ATTENTE') badge-attente
                            @elseif($demande->statut == 'EN CONTROLE') badge-controle
                            @elseif($demande->statut == 'VALIDE') badge-valide
                            @else badge-rejete @endif">
                            {{ $demande->statut }}
                        </span>
                    </td>
                    <td>{{ $demande->controleur->full_name ?? 'Non assigné' }}</td>
                    <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('cheflot.demandes.show', $demande->id) }}" class="btn-link" title="Voir">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('cheflot.demandes.historique', $demande->id) }}" class="btn-link" title="Historique">
                            <i class="fas fa-history"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 48px; color: #ccc;"></i>
                        <p style="margin-top: 10px;">Aucune demande trouvée</p>
                        <a href="{{ route('demandes.create') }}" class="btn-create" style="margin-top: 10px;">
                            <i class="fas fa-plus"></i> Créer la première demande
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $demandes->links() }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Données pour les graphiques - Version CORRIGÉE
    @php
        $prioritesData = isset($priorites) ? $priorites : [
            'Basse' => 0,
            'Moyenne' => 0,
            'Haute' => 0,
            'Urgente' => 0
        ];
        $evolutionData = isset($demandesData) ? $demandesData : [];
        $evolutionLabelsData = isset($months) ? $months : [];
    @endphp

    const prioriteData = {
        labels: {!! json_encode(array_keys($prioritesData)) !!},
        values: {!! json_encode(array_values($prioritesData)) !!}
    };

    const evolutionData = {!! json_encode($evolutionData) !!};
    const evolutionLabels = {!! json_encode($evolutionLabelsData) !!};

    // Graphique des priorités
    const prioriteCtx = document.getElementById('prioriteChart').getContext('2d');
    new Chart(prioriteCtx, {
        type: 'doughnut',
        data: {
            labels: prioriteData.labels,
            datasets: [{
                data: prioriteData.values,
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#dc2626'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Graphique d'évolution
    if (evolutionLabels.length > 0) {
        const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
        new Chart(evolutionCtx, {
            type: 'line',
            data: {
                labels: evolutionLabels,
                datasets: [{
                    label: 'Nombre de demandes',
                    data: evolutionData,
                    borderColor: '#047857',
                    backgroundColor: 'rgba(4,120,87,0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointBackgroundColor: '#047857',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'top' } }
            }
        });
    }
</script>
@endsection
