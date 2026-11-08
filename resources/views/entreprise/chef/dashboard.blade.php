@extends('layouts.entreprise')

@section('title', 'Tableau de bord')

@php
    $prenom = $user->prenom ?: $user->nom;
    $sub = collect([$user->structure?->nom, $user->fonction, $user->specialite])->filter()->implode(' · ');
    $dateLongue = ucfirst(now()->locale('fr')->translatedFormat('l j F Y'));

    $totalRepartition = array_sum($repartition);
    $tauxValidation = $documentsSoumis > 0 ? round($documentsValides / $documentsSoumis * 100) : null;
    $nbProjets = $projetsEnCours + $projetsTermines;
    $maxMois = max(1, max($projetsData));

    // statut d'un dossier => classe de pastille
    $classes = ['valide' => 'a', 'archive' => 'a', 'en_analyse' => 'b', 'a_corriger' => 'd', 'transmis' => 'b'];
    // Répartition : [libellé, nombre, couleur]
    $segs = [
        ['Validés',    $repartition[0], 'var(--accent)'],
        ['En attente', $repartition[1], 'var(--accent-2)'],
        ['En analyse', $repartition[2], 'var(--accent-3)'],
        ['À corriger', $repartition[3], 'var(--ink)'],
    ];
@endphp

@section('content')
<div class="hi">
    <div><h1>Bonjour {{ $prenom }}</h1>@if($sub)<p>{{ $sub }}</p>@endif</div>
    <span class="d">{{ $dateLongue }}</span>
</div>

<div class="bento">
    <div class="t hero">
        <div class="l">Taux de validation</div>
        <div class="big">{{ $tauxValidation ?? '—' }}@if($tauxValidation !== null)<small>%</small>@endif</div>
        <div class="ft">
            <div class="meter"><i style="width:{{ $tauxValidation ?? 0 }}%"></i></div>
            <span>{{ $documentsValides }} validé{{ $documentsValides > 1 ? 's' : '' }} sur {{ $documentsSoumis }} au visa</span>
        </div>
    </div>

    <div class="t dark">
        @if($nonConformites > 0)<span class="tagu">À traiter</span>@endif
        <div class="l">Non-conformités</div>
        <div class="v">{{ $nonConformites }}</div>
        <div class="s">{{ $nonConformites > 0 ? 'document(s) à corriger en priorité' : 'Aucune à signaler' }}</div>
    </div>
    <div class="t">
        <div class="l">En attente de validation</div>
        <div class="v">{{ $documentsEnAttente }}</div>
        <div class="s">{{ $documentsATransferer > 0 ? $documentsATransferer . ' à transférer · ' : '' }}{{ $attenteAnciennes > 0 ? $attenteAnciennes . ' depuis plus de 5 jours' : 'aucun retard' }}</div>
    </div>

    <div class="t">
        <div class="l">Documents en cours de visa</div>
        <div class="v">{{ $projetsEnCours }}</div>
        <div class="s">sur {{ $nbProjets }} {{ $nbProjets > 1 ? 'documents' : 'document' }} au visa</div>
    </div>
    <div class="t soft">
        <div class="l">Documents validés</div>
        <div class="v">{{ $projetsTermines }}</div>
        <div class="s">{{ $informatifs }} informatif{{ $informatifs > 1 ? 's' : '' }} transmis</div>
    </div>

    <div class="t chart">
        <div class="hd"><h2>Documents émis par mois</h2><small>6 derniers mois</small></div>
        <div class="cols">
            @foreach($projetsData as $i => $v)
                <div class="col {{ $i === 5 ? 'cur' : '' }}"><i style="height:{{ $v > 0 ? max(6, round($v / $maxMois * 100)) : 3 }}%" title="{{ $v }}"></i><span>{{ $months[$i] }}</span></div>
            @endforeach
        </div>
    </div>

    <div class="t split">
        <h2>Répartition</h2>
        <div class="stack">
            @foreach($segs as [$nom, $nb, $couleur])
                @if($nb > 0)<i style="flex:{{ $nb }};--c:{{ $couleur }}"></i>@endif
            @endforeach
        </div>
        <ul class="key">
            @foreach($segs as [$nom, $nb, $couleur])
                <li><i style="--c:{{ $couleur }}"></i>{{ $nom }}<b>{{ $nb }}</b></li>
            @endforeach
        </ul>
    </div>

    <div class="t list">
        <div class="hd"><h2>Derniers documents</h2><a href="{{ route('entreprise.documents') }}">Tout voir →</a></div>
        @forelse($documentsRecents as $dos)
            @php $classe = $classes[$dos->statut] ?? 'c'; $dossier = $dos; @endphp
            <div class="r r6">
                <span class="ref"><a href="{{ route('entreprise.dossiers.show', $dos) }}">{{ $dos->identifiant_affiche }}</a></span>
                <span class="n">{{ \Illuminate\Support\Str::limit($dos->titre ?? 'Sans titre', 60) }}</span>
                <span class="m">{{ collect([$dos->lot?->nom, $dos->documentType?->nom])->filter()->implode(' · ') ?: '—' }}</span>
                <span class="p {{ $classe }}">{{ $dos->statut_label }}</span>
                <span>@include('partials.visas-colonne')</span>
                <span class="dt">{{ $dos->created_at?->format('d/m/Y') }}</span>
            </div>
        @empty
            <div class="empty"><b>Aucun document pour le moment</b>Les documents que vous soumettez apparaîtront ici.</div>
        @endforelse
    </div>
</div>
@endsection
