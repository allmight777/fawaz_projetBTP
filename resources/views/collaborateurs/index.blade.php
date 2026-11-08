@extends($chef->spaceLayout())

@section('title', 'Mes collaborateurs')

@section('content')
@php $controle = $chef->isChefLot(); @endphp

<div class="page-header">
    <h1>Mes collaborateurs</h1>
    @if($controle && Route::has('cheflot.controleurs'))
        <a href="{{ route('cheflot.controleurs') }}" class="btn">Charge de travail des contrôleurs</a>
    @endif
</div>

<div class="col-tiles">
    <div class="col-tile"><b>{{ $stats['total'] }}</b><span>Collaborateur{{ $stats['total'] > 1 ? 's' : '' }}</span></div>
    <div class="col-tile"><b>{{ $stats['actifs'] }}</b><span>Comptes actifs</span></div>
    <div class="col-tile"><b>{{ $stats['inactifs'] }}</b><span>Comptes inactifs</span></div>
    @if($controle)
        <div class="col-tile dark"><b>{{ $stats['en_cours'] }}</b><span>Analyses en cours</span></div>
    @endif
</div>

@if($collaborateurs->isEmpty())
    <div class="empty-state">Aucun collaborateur n'est rattaché à votre structure pour le moment.</div>
@else
    <div class="col-search">
        <input type="search" id="colSearch" placeholder="Rechercher un collaborateur (nom, fonction, e-mail)…" autocomplete="off">
        <span id="colCount">{{ $collaborateurs->count() }} résultat{{ $collaborateurs->count() > 1 ? 's' : '' }}</span>
    </div>

    <div class="col-grid" id="colGrid">
        @foreach($collaborateurs as $c)
            @php
                $ini = mb_strtoupper(mb_substr($c->prenom ?: '', 0, 1) . mb_substr($c->nom ?: '', 0, 1)) ?: '?';
                $recherche = mb_strtolower($c->full_name . ' ' . $c->fonction . ' ' . $c->email . ' ' . $c->specialite);
            @endphp
            <article class="col-card" data-q="{{ $recherche }}">
                <header>
                    <span class="col-av">{{ $ini }}</span>
                    <div class="col-id">
                        <h3>{{ $c->full_name }}</h3>
                        <p>{{ $c->fonction ?: 'Fonction non renseignée' }}</p>
                    </div>
                    <span class="badge {{ $c->actif ? 'badge-valide' : 'badge-attente' }}">{{ $c->actif ? 'Actif' : 'Inactif' }}</span>
                </header>

                <dl class="col-info">
                    <div><dt>E-mail</dt><dd><a href="mailto:{{ $c->email }}">{{ $c->email }}</a></dd></div>
                    @if($c->specialite)<div><dt>Spécialité</dt><dd>{{ $c->specialite }}</dd></div>@endif
                    @if($c->lot)<div><dt>Lot</dt><dd>{{ $c->lot->code }} · {{ $c->lot->nom }}</dd></div>@endif
                    <div><dt>Membre depuis</dt><dd>{{ $c->created_at?->format('d/m/Y') }}</dd></div>
                </dl>

                <div class="col-nums">
                    @if($controle)
                        <div><b>{{ $c->affectations_en_cours_count }}</b><span>En cours</span></div>
                        <div><b>{{ $c->affectations_terminees_count }}</b><span>Terminées</span></div>
                    @else
                        <div><b>{{ $c->dossiers_importes_count }}</b><span>Documents envoyés</span></div>
                    @endif
                </div>

                <a href="{{ route('collaborateurs.show', $c) }}" class="btn col-more">Voir la fiche</a>
            </article>
        @endforeach
    </div>
    <div class="empty-state" id="colVide" hidden>Aucun collaborateur ne correspond à votre recherche.</div>

    <script>
        (function () {
            var champ = document.getElementById('colSearch'), cartes = document.querySelectorAll('.col-card');
            var vide = document.getElementById('colVide'), compte = document.getElementById('colCount');
            champ.addEventListener('input', function () {
                var q = champ.value.trim().toLowerCase(), n = 0;
                cartes.forEach(function (c) { var ok = !q || c.dataset.q.indexOf(q) !== -1; c.hidden = !ok; if (ok) n++; });
                compte.textContent = n + ' résultat' + (n > 1 ? 's' : '');
                vide.hidden = n !== 0;
            });
        })();
    </script>
@endif
@endsection
