@extends($layout)

@section('title', $event->titre)

@section('content')
@php
    $initiales = fn ($u) => mb_strtoupper(mb_substr($u->prenom ?: '', 0, 1) . mb_substr($u->nom ?: '', 0, 1)) ?: '?';
    $libelles = ['confirme' => 'Confirmé', 'decline' => 'Décliné', 'en_attente' => 'En attente'];
    $compte = $event->participants->groupBy(fn ($p) => $p->pivot->statut)->map->count();
@endphp

<div class="page-header">
    <h2>{{ $event->titre }}</h2>
    <a href="{{ route('events.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif

<div class="ev-grid">
    <div class="ev-col">
        <section class="ev-card">
            <h3 class="ev-title"><span class="ev-ico"><i class="fas fa-circle-info"></i></span> Détails</h3>

            <dl class="ev-details">
                <div>
                    <dt>Organisateur</dt>
                    <dd><span class="ev-av">{{ $initiales($event->createur) }}</span>{{ $event->createur->full_name }}</dd>
                </div>
                <div>
                    <dt>Début</dt>
                    <dd><i class="far fa-calendar"></i>{{ $event->date_debut->translatedFormat('d/m/Y à H:i') }}</dd>
                </div>
                @if($event->date_fin)
                    <div>
                        <dt>Fin</dt>
                        <dd><i class="far fa-calendar-check"></i>{{ $event->date_fin->translatedFormat('d/m/Y à H:i') }}</dd>
                    </div>
                @endif
                @if($event->lieu)
                    <div>
                        <dt>Lieu</dt>
                        <dd><i class="fas fa-location-dot"></i>{{ $event->lieu }}</dd>
                    </div>
                @endif
                @if($event->structures->isNotEmpty())
                    <div class="ev-wide">
                        <dt>Structure(s) visée(s)</dt>
                        <dd class="ev-chips">@foreach($event->structures as $structure)<span class="ev-chip">{{ $structure->nom }}</span>@endforeach</dd>
                    </div>
                @endif
                @if($event->description)
                    <div class="ev-wide">
                        <dt>Description</dt>
                        <dd class="ev-desc">{{ $event->description }}</dd>
                    </div>
                @endif
            </dl>

            @if($estCreateur)
                <div class="ev-actions">
                    <a href="{{ route('events.edit', $event) }}" class="btn-outline"><i class="fas fa-pen"></i> Modifier</a>
                    <form method="POST" action="{{ route('events.destroy', $event) }}" data-confirm="« {{ $event->titre }} » sera supprimé pour tous les participants." data-confirm-title="Supprimer cet événement ?" data-confirm-ok="Supprimer" data-confirm-tone="danger">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger"><i class="fas fa-trash"></i> Supprimer</button>
                    </form>
                </div>
            @endif
        </section>

        @if($monInvitation && !$estCreateur)
            <section class="ev-card">
                <h3 class="ev-title"><span class="ev-ico"><i class="fas fa-reply"></i></span> Votre réponse</h3>
                <p class="ev-status">Statut actuel
                    <span class="ev-badge {{ $monInvitation->pivot->statut }}">{{ $libelles[$monInvitation->pivot->statut] ?? 'En attente' }}</span>
                </p>
                <div class="ev-rsvp">
                    <form method="POST" action="{{ route('events.statut', $event) }}">
                        @csrf
                        <input type="hidden" name="statut" value="confirme">
                        <button type="submit" class="ev-btn ok"><i class="fas fa-check"></i> Je serai présent(e)</button>
                    </form>
                    <form method="POST" action="{{ route('events.statut', $event) }}">
                        @csrf
                        <input type="hidden" name="statut" value="decline">
                        <button type="submit" class="ev-btn ko"><i class="fas fa-xmark"></i> Je ne pourrai pas venir</button>
                    </form>
                </div>
            </section>
        @endif
    </div>

    <section class="ev-card">
        <h3 class="ev-title">
            <span class="ev-ico"><i class="fas fa-users"></i></span> Participants
            <span class="ev-count">{{ $event->participants->count() }}</span>
        </h3>

        @if($event->participants->isNotEmpty())
            <div class="ev-sum">
                <span class="ev-badge confirme">{{ $compte['confirme'] ?? 0 }} confirmé{{ ($compte['confirme'] ?? 0) > 1 ? 's' : '' }}</span>
                <span class="ev-badge en_attente">{{ $compte['en_attente'] ?? 0 }} en attente</span>
                <span class="ev-badge decline">{{ $compte['decline'] ?? 0 }} décliné{{ ($compte['decline'] ?? 0) > 1 ? 's' : '' }}</span>
            </div>
        @endif

        <ul class="ev-people">
            @forelse($event->participants as $participant)
                <li>
                    <span class="ev-av">{{ $initiales($participant) }}</span>
                    <span class="ev-who">{{ $participant->full_name }}@if($participant->structure)<small>{{ $participant->structure->nom }}</small>@endif</span>
                    <span class="ev-badge {{ $participant->pivot->statut }}">{{ $libelles[$participant->pivot->statut] ?? 'En attente' }}</span>
                </li>
            @empty
                <li class="ev-vide">Aucun participant invité.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
