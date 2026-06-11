@extends('layouts.cheflot')

@section('title', 'Demandes rejetées')

@section('content')
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-times-circle"></i> Demandes rejetées</h3>
        <div class="filters">
            <a href="{{ route('cheflot.demandes') }}" class="filter-btn inactive">Toutes</a>
            <a href="{{ route('cheflot.demandes.attente') }}" class="filter-btn inactive">En attente</a>
            <a href="{{ route('cheflot.demandes.controle') }}" class="filter-btn inactive">En contrôle</a>
            <a href="{{ route('cheflot.demandes.validees') }}" class="filter-btn inactive">Validées</a>
            <a href="{{ route('cheflot.demandes.rejetees') }}" class="filter-btn active">Rejetées</a>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead><tr><th>N°</th><th>Titre</th><th>Soumis par</th><th>Motif</th><th>Date décision</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($demandes as $demande)
                <tr>
                    <td>#{{ $demande->numero_demande }}</td>
                    <td>{{ $demande->titre_document }}</td>
                    <td>{{ $demande->soumisPar->full_name ?? '-' }}</td>
                    <td>{{ Str::limit($demande->commentaire_controleur, 50) }}</td>
                    <td>{{ $demande->date_decision ? $demande->date_decision->format('d/m/Y') : '-' }}</td>
                    <td><a href="{{ route('cheflot.demandes.show', $demande->id) }}" class="btn-link">Voir</a></td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center">Aucune demande rejetée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrapper">{{ $demandes->links() }}</div>
</div>
@endsection
