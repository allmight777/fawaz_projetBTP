@extends('layouts.entreprise')

@section('title', 'Tableau de bord')

@php
    $prenom = $user->prenom ?: $user->nom;
    $sub = collect([$user->structure?->nom, $user->fonction, $user->specialite])->filter()->implode(' · ');
    $dateLongue = ucfirst(now()->locale('fr')->translatedFormat('l j F Y'));
    $pastilles = ['valide' => 'badge-valide', 'archive' => 'badge-controle', 'a_corriger' => 'badge-rejete', 'en_analyse' => 'badge-controle'];
@endphp

@section('content')
<div class="hi">
    <div><h1>Bonjour {{ $prenom }}</h1>@if($sub)<p>{{ $sub }}</p>@endif</div>
    <span class="d">{{ $dateLongue }}</span>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-info"><h3>Mes documents</h3><div class="stat-number">{{ $mesDocuments }}</div></div><div class="stat-icon"><i class="fas fa-file-alt"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>Chez mon responsable</h3><div class="stat-number">{{ $documentsATransferer }}</div></div><div class="stat-icon"><i class="fas fa-share"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>En attente de visa</h3><div class="stat-number">{{ $documentsEnAttente - $documentsATransferer }}</div></div><div class="stat-icon"><i class="fas fa-clock"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>En analyse</h3><div class="stat-number">{{ $documentsEnAnalyse }}</div></div><div class="stat-icon"><i class="fas fa-search"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>Validés</h3><div class="stat-number">{{ $documentsValides }}</div></div><div class="stat-icon"><i class="fas fa-check-double"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>À corriger</h3><div class="stat-number">{{ $documentsACorriger }}</div></div><div class="stat-icon"><i class="fas fa-edit"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>Informatifs transmis</h3><div class="stat-number">{{ $informatifs }}</div></div><div class="stat-icon"><i class="fas fa-paper-plane"></i></div></div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>Mes derniers documents</h3>
        <a href="{{ eroute('entreprise.documents') }}" class="btn-link">Voir tous <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="table-wrapper">
        <table data-no-filter>
            <thead>
                <tr><th>Identifiant</th><th>Titre</th><th>Type</th><th>Statut</th><th>Visas</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($mesDocumentsRecents as $dossier)
                    <tr>
                        <td><a href="{{ eroute('entreprise.dossiers.show', $dossier) }}" class="btn-link">{{ $dossier->identifiant_affiche }}</a></td>
                        <td>{{ Str::limit($dossier->titre, 40) }}</td>
                        <td>{{ $dossier->documentType->nom ?? '—' }}</td>
                        <td><span class="badge {{ $pastilles[$dossier->statut] ?? 'badge-attente' }}">{{ $dossier->statut_label }}</span></td>
                        <td>@include('partials.visas-colonne')</td>
                        <td>{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i>Aucun document pour le moment</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
