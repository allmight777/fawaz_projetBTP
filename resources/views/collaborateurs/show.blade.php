@extends($chef->spaceLayout())

@section('title', $collaborateur->full_name)

@section('content')
@php
    $c = $collaborateur;
    $ini = mb_strtoupper(mb_substr($c->prenom ?: '', 0, 1) . mb_substr($c->nom ?: '', 0, 1)) ?: '?';
    $pv = ['valide' => 'badge-valide', 'archive' => 'badge-controle', 'a_corriger' => 'badge-rejete'];
    $libAff = ['en_attente' => 'En attente', 'en_cours' => 'En cours', 'termine' => 'Terminée'];
@endphp

<div class="page-header">
    <h1>Fiche collaborateur</h1>
    <a href="{{ route('collaborateurs.index') }}" class="btn-back">Retour à la liste</a>
</div>

<div class="card col-hero">
    <span class="col-av big">{{ $ini }}</span>
    <div class="col-hero-id">
        <h2>{{ $c->full_name }}</h2>
        <p>{{ $c->fonction ?: 'Fonction non renseignée' }}@if($c->structure) · {{ $c->structure->nom }}@endif</p>
        <div class="col-hero-badges">
            <span class="badge {{ $c->actif ? 'badge-valide' : 'badge-attente' }}">Compte {{ $c->actif ? 'actif' : 'inactif' }}</span>
            @if($c->specialite)<span class="badge badge-controle">{{ $c->specialite }}</span>@endif
        </div>
    </div>
    <a href="mailto:{{ $c->email }}" class="btn-submit" style="width:auto; display:inline-block;">Écrire un e-mail</a>
</div>

<div class="col-split">
    <div class="card">
        <h3 class="card-title">Informations</h3>
        <dl class="col-facts">
            <div><dt>Nom complet</dt><dd>{{ $c->full_name }}</dd></div>
            <div><dt>E-mail</dt><dd><a href="mailto:{{ $c->email }}">{{ $c->email }}</a></dd></div>
            <div><dt>Fonction</dt><dd>{{ $c->fonction ?: '—' }}</dd></div>
            <div><dt>Spécialité</dt><dd>{{ $c->specialite ?: '—' }}</dd></div>
            <div><dt>Structure</dt><dd>{{ $c->structure?->nom ?? '—' }}</dd></div>
            <div><dt>Lot</dt><dd>{{ $c->lot ? $c->lot->code . ' · ' . $c->lot->nom : '—' }}</dd></div>
            <div><dt>Membre depuis</dt><dd>{{ $c->created_at?->format('d/m/Y') }}</dd></div>
        </dl>
    </div>

    <div class="card">
        <h3 class="card-title">Activité</h3>
        <div class="col-nums big">
            <div><b>{{ $compteurs['documents'] }}</b><span>Documents envoyés</span></div>
            <div><b>{{ $compteurs['en_cours'] }}</b><span>Analyses en cours</span></div>
            <div><b>{{ $compteurs['terminees'] }}</b><span>Analyses terminées</span></div>
        </div>
    </div>
</div>

@if($dossiers->isNotEmpty())
<div class="card" style="margin-top:14px;">
    <h3 class="card-title">Derniers documents envoyés</h3>
    <div class="col-rows">
        @foreach($dossiers as $d)
            @php $lien = isset($visibles[$d->id]); @endphp
            <{{ $lien ? 'a href='.route('dossiers.show', $d) : 'div' }} class="row-link">
                <div class="rl-top">
                    <strong>{{ $d->identifiant_affiche }} · {{ Str::limit($d->titre, 70) }}</strong>
                    <span class="badge {{ $pv[$d->statut] ?? 'badge-attente' }}">{{ $d->statut_label }}</span>
                </div>
                <small>{{ $d->documentType->nom ?? '—' }} · {{ $d->created_at->format('d/m/Y') }}</small>
            </{{ $lien ? 'a' : 'div' }}>
        @endforeach
    </div>
</div>
@endif

@if($affectations->isNotEmpty())
<div class="card" style="margin-top:14px;">
    <h3 class="card-title">Dernières analyses</h3>
    <div class="col-rows">
        @foreach($affectations as $a)
            @php $d = $a->documentVersion?->dossier; $lien = $d && isset($visibles[$d->id]); @endphp
            <{{ $lien ? 'a href='.route('dossiers.show', $d) : 'div' }} class="row-link">
                <div class="rl-top">
                    <strong>{{ $d?->identifiant_affiche }} · {{ Str::limit($d?->titre, 70) }}</strong>
                    <span class="badge {{ $a->statut === 'termine' ? 'badge-valide' : 'badge-attente' }}">{{ $libAff[$a->statut] ?? ucfirst(str_replace('_', ' ', $a->statut)) }}</span>
                </div>
                <small>Affectée le {{ $a->created_at->format('d/m/Y') }}@if($a->date_limite && $a->statut !== 'termine') · échéance {{ $a->date_limite->format('d/m/Y à H:i') }}@endif</small>
            </{{ $lien ? 'a' : 'div' }}>
        @endforeach
    </div>
</div>
@endif

@if($dossiers->isEmpty() && $affectations->isEmpty())
    <div class="empty-state" style="margin-top:14px;">Aucune activité pour ce collaborateur pour le moment.</div>
@endif
@endsection
