@extends('layouts.cheflot')

@section('title', 'Demandes validées')

@section('content')
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-check-double"></i> Demandes validées</h3>
        <div class="filters">
            <a href="{{ route('cheflot.demandes') }}" class="filter-btn inactive">Toutes</a>
            <a href="{{ route('cheflot.demandes.attente') }}" class="filter-btn inactive">En attente</a>
            <a href="{{ route('cheflot.demandes.controle') }}" class="filter-btn inactive">En contrôle</a>
            <a href="{{ route('cheflot.demandes.validees') }}" class="filter-btn active">Validées</a>
            <a href="{{ route('cheflot.demandes.rejetees') }}" class="filter-btn inactive">Rejetées</a>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead><tr><th>N°</th><th>Titre</th><th>Soumis par</th><th>Version</th><th>Date décision</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($demandes as $demande)
                <tr>
                    <td>#{{ $demande->numero_demande }}</td>
                    <td>{{ $demande->titre_document }}</td>
                    <td>{{ $demande->soumisPar->full_name ?? '-' }}</td>
                    <td><span class="badge badge-valide">{{ $demande->version }}</span></td>
                    <td>{{ $demande->date_decision ? $demande->date_decision->format('d/m/Y') : '-' }}</td>
                    <td><a href="{{ route('cheflot.demandes.show', $demande->id) }}" class="btn-link">Voir</a></td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center">Aucune demande validée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrapper">{{ $demandes->links() }}</div>
</div>
@endsection
