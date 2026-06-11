@extends('layouts.cheflot')

@section('title', 'Demandes en attente')

@section('content')
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-clock"></i> Demandes en attente d'assignation</h3>
        <div class="filters">
            <a href="{{ route('cheflot.demandes') }}" class="filter-btn inactive">Toutes</a>
            <a href="{{ route('cheflot.demandes.attente') }}" class="filter-btn active">En attente</a>
            <a href="{{ route('cheflot.demandes.controle') }}" class="filter-btn inactive">En contrôle</a>
            <a href="{{ route('cheflot.demandes.validees') }}" class="filter-btn inactive">Validées</a>
            <a href="{{ route('cheflot.demandes.rejetees') }}" class="filter-btn inactive">Rejetées</a>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead><tr><th>N°</th><th>Titre</th><th>Soumis par</th><th>Lot</th><th>Priorité</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($demandes as $demande)
                <tr>
                    <td>#{{ $demande->numero_demande }}</td>
                    <td>{{ $demande->titre_document }}</td>
                    <td>{{ $demande->soumisPar->full_name ?? '-' }}</td>
                    <td>{{ $demande->lot->nom ?? '-' }}</td>
                    <td><span class="badge badge-{{ strtolower($demande->priorite) }}">{{ $demande->priorite }}</span></td>
                    <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('cheflot.demandes.show', $demande->id) }}" class="btn-link">Assigner</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center">Aucune demande en attente</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrapper">{{ $demandes->links() }}</div>
</div>
@endsection
