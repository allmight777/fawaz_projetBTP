@extends($user->spaceLayout())

@section('title', "Demandes d'information")

@section('content')
@php
    $pastille = ['en_attente' => 'en_attente', 'accordee' => 'confirme', 'refusee' => 'decline', 'cloturee' => 'cloturee'];
    $ini = fn ($u) => mb_strtoupper(mb_substr($u->prenom ?: '', 0, 1) . mb_substr($u->nom ?: '', 0, 1)) ?: '?';
    $colonnes = [
        ['Reçues', 'À traiter par vous', 'fa-inbox', $recues, 'demandeur', 'De'],
        ['Envoyées', 'Demandes que vous avez faites', 'fa-paper-plane', $envoyees, 'destinataire', 'À'],
    ];
    $enAttente = $recues->where('statut', 'en_attente')->count();
@endphp

<div class="page-header">
    <h1>Demandes d'information</h1>
    @if($user->peutViser())
        <a href="{{ route('rfi.create') }}" class="btn"><i class="fas fa-plus"></i> Nouvelle demande</a>
    @endif
</div>

@if(session('success'))<div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif

<div class="ev-grid rfi-grid">
    @foreach($colonnes as [$titre, $aide, $icone, $liste, $autre, $prefixe])
        <section class="ev-card">
            <h3 class="ev-title">
                <span class="ev-ico"><i class="fas {{ $icone }}"></i></span>
                <span class="rfi-heading">{{ $titre }}<small>{{ $aide }}</small></span>
                <span class="ev-count">{{ $liste->count() }}</span>
            </h3>

            @if($titre === 'Reçues' && $enAttente > 0)
                <p class="rfi-alert"><i class="fas fa-bell"></i> {{ $enAttente }} demande{{ $enAttente > 1 ? 's' : '' }} en attente de votre réponse</p>
            @endif

            <ul class="rfi-list">
                @forelse($liste as $d)
                    <li>
                        <a href="{{ route('rfi.show', $d) }}" class="rfi-item">
                            <span class="ev-av">{{ $ini($d->$autre) }}</span>
                            <span class="rfi-body">
                                <strong>{{ $d->objet }}</strong>
                                <small>{{ $prefixe }} {{ $d->$autre->structure?->nom ?? $d->$autre->full_name }} · {{ $d->created_at->format('d/m/Y') }}</small>
                            </span>
                            <span class="ev-badge {{ $pastille[$d->statut] ?? 'en_attente' }}">{{ $d->statut_label }}</span>
                        </a>
                    </li>
                @empty
                    <li class="rfi-vide">
                        <i class="fas {{ $icone }}"></i>
                        <strong>Aucune demande</strong>
                        <span>{{ $titre === 'Reçues' ? "Les demandes qui vous sont adressées apparaîtront ici." : "Vos demandes d'information apparaîtront ici." }}</span>
                    </li>
                @endforelse
            </ul>
        </section>
    @endforeach
</div>
@endsection
