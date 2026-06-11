@extends('layouts.cheflot')

@section('title', 'Demandes en contrôle')

@section('content')
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-check-circle"></i> Demandes en cours de contrôle</h3>
        <div class="filters">
            <a href="{{ route('cheflot.demandes') }}" class="filter-btn inactive">Toutes</a>
            <a href="{{ route('cheflot.demandes.attente') }}" class="filter-btn inactive">En attente</a>
            <a href="{{ route('cheflot.demandes.controle') }}" class="filter-btn active">En contrôle</a>
            <a href="{{ route('cheflot.demandes.validees') }}" class="filter-btn inactive">Validées</a>
            <a href="{{ route('cheflot.demandes.rejetees') }}" class="filter-btn inactive">Rejetées</a>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <tr>
            <thead><tr><th>N°</th><th>Titre</th><th>Soumis par</th><th>Contrôleur</th><th>Priorité</th><th>Échéance</th><th>Délai</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($demandes as $demande)
                <tr>
                    <td>#{{ $demande->numero_demande }}</td>
                    <td>{{ $demande->titre_document }}</td>
                    <td>{{ $demande->soumisPar->full_name ?? '-' }}</td>
                    <td>{{ $demande->controleur->full_name ?? '-' }}</td>
                    <td><span class="badge badge-{{ strtolower($demande->priorite) }}">{{ $demande->priorite }}</span></td>
                    <td>{{ $demande->echeance_controle ? $demande->echeance_controle->format('d/m/Y') : '-' }}</td>
                    <td><span class="badge {{ $demande->statut_delai == 'EN RETARD' ? 'badge-rejete' : 'badge-valide' }}">{{ $demande->statut_delai }}</span></td>
                    <td><a href="{{ route('cheflot.demandes.show', $demande->id) }}" class="btn-link">Voir</a></td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center">Aucune demande en contrôle</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrapper">{{ $demandes->links() }}</div>
</div>
@endsection
