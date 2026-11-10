@php
    $derniereVersion = $dossier->versions->last();
    $user = Auth::user();
    $pastille = ['valide' => 'badge-valide', 'archive' => 'badge-controle', 'a_corriger' => 'badge-rejete'];
    $dossier->loadMissing('versions.visas.user', 'versions.visas.structure', 'circuitVisas.decideur');
    $circuitService = app(\App\Services\CircuitVisaService::class);
@endphp
@extends($user->spaceLayout())

@section('title', $dossier->identifiant_affiche)

@section('content')
<div class="page-header">
    <h1>{{ $dossier->identifiant_affiche }} · {{ Str::limit($dossier->titre, 60) }}</h1>
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dossiers.index') }}" class="btn-back">Retour</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div style="display:grid; gap:14px;">
    <div class="card">
        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:14px; align-items:center;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <span class="badge {{ $pastille[$dossier->statut] ?? 'badge-attente' }}">{{ $dossier->statut_label }}</span>
                <span class="badge badge-controle">{{ $dossier->mode_traitement === 'validation' ? 'Validation documentaire' : 'Transmission simple' }}</span>
                <span class="badge badge-attente">V{{ $dossier->version_courante }}</span>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                @if($dossier->statut === 'a_corriger' && $dossier->cree_par === $user->id)
                    <a href="{{ route('dossiers.versions.create', $dossier) }}" class="btn-submit" style="width:auto; display:inline-block;">Importer la correction</a>
                @endif
                @if($dossier->mode_traitement === 'simple' && $dossier->statut === 'transmis' && $dossier->transmissions->isEmpty())
                    <a href="{{ route('dossiers.transmettre.form', $dossier) }}" class="btn-submit" style="width:auto; display:inline-block;">Transmettre</a>
                @endif
                @if($derniereVersion && $circuitService->peutAffecter($user, $derniereVersion))
                    <a href="{{ sroute('cheflot.dossiers.affecter', $derniereVersion) }}" class="btn-submit" style="width:auto; display:inline-block;">Affecter un collaborateur</a>
                @endif
                @if($derniereVersion && $circuitService->peutDecider($user, $derniereVersion))
                    <a href="{{ sroute('cheflot.dossiers.decision', $derniereVersion) }}" class="btn-submit" style="width:auto; display:inline-block;">Consolidation / Décision</a>
                @endif
                @php $version = $derniereVersion; @endphp
                @include('partials.modifier-decision')
                @include('partials.partager-circuit', ['libelle' => true])
                @if($dossier->retransmissiblePar($user) && !($dossier->mode_traitement === 'simple' && $dossier->statut === 'transmis' && $dossier->transmissions->isEmpty()))
                    <a href="{{ route('dossiers.retransmettre.form', $dossier) }}" class="btn"><i class="fas fa-paper-plane"></i> Transmettre</a>
                @endif
                @if($user->estChefVisa() && $dossier->statut === 'valide')
                    <a href="{{ sroute('cheflot.dossiers.diffusion', $dossier) }}" class="btn-submit" style="width:auto; display:inline-block;">Diffuser</a>
                @endif
                @if($dossier->archivablePar($user))
                    <form method="POST" action="{{ route('dossiers.archiver', $dossier) }}"
                          data-confirm="{{ $dossier->identifiant_affiche }} · {{ Str::limit($dossier->titre, 60) }}" data-confirm-title="Archiver ce document ?" data-confirm-ok="Archiver" data-confirm-icon="box-archive" data-confirm-tone="warn" data-confirm-details="Il quitte vos documents et passe dans les archives|Il est figé : plus aucune modification ni suppression, pour personne|Les parties prenantes sont prévenues par email">
                        @csrf
                        <button type="submit" class="btn-danger">Archiver</button>
                    </form>
                @endif
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-top:22px;">
            @foreach(['Type' => '['.$dossier->documentType->code.'] '.$dossier->documentType->nom, 'Structure émettrice' => $dossier->structureEmettrice?->nom ?? '—', 'Lot' => $dossier->lot?->nom ?? '—', 'Créé par' => $dossier->creePar->full_name] as $label => $valeur)
                <div><div style="font-size: var(--fs-s); color:var(--muted); font-weight:700;">{{ $label }}</div><div style="font-weight:700;">{{ $valeur }}</div></div>
            @endforeach
        </div>
        @if($dossier->description)<p style="margin-top:16px; color:var(--muted);">{{ $dossier->description }}</p>@endif
    </div>

    @include('partials.applicable')
    @include('partials.circuit-visas')
    @include('partials.circuit-historique')

    <div class="card">
        <h3 class="card-title">Historique des versions</h3>
        <div class="vers">
            @foreach($dossier->versions->sortByDesc('numero_version') as $version)
                @php
                    $pv = ['valide' => 'badge-valide', 'a_corriger' => 'badge-rejete'][$version->statut] ?? 'badge-attente';
                    $initiales = fn ($u) => mb_strtoupper(mb_substr($u?->prenom ?: '', 0, 1) . mb_substr($u?->nom ?: '', 0, 1)) ?: '?';
                    $libAff = ['en_attente' => 'En attente', 'en_cours' => 'En cours', 'termine' => 'Terminé'];
                @endphp
                <article class="ver">
                    <header class="ver-head">
                        <div class="ver-id">
                            <span class="ver-n">V{{ $version->numero_version }}</span>
                            <div>
                                <span class="badge {{ $pv }}">{{ $version->libelle_statut }}</span>
                                <div class="ver-meta">Importée par {{ $version->importePar->full_name }} · {{ $version->date_import->format('d/m/Y à H:i') }}</div>
                            </div>
                        </div>
                        <div class="ver-actions">
                            @if($version->hasFichier())
                                <a href="{{ route('dossiers.telecharger', [$dossier, $version]) }}" class="btn">Télécharger</a>
                            @endif
                            @if(!$dossier->isArchived() && $version->statut === 'en_attente_checklist' && $dossier->cree_par === $user->id)
                                <a href="{{ route('document-versions.checklist', $version) }}" class="btn-submit" style="width:auto; display:inline-block;">Compléter la checklist</a>
                            @endif
                        </div>
                    </header>

                    @if($version->visas->isNotEmpty())
                        <section class="ver-sec">
                            <h4>Visas</h4>
                            <div class="ver-chips">
                                @foreach($version->visas as $visa)
                                    <span class="ver-chip"><b>{{ $visa->type === 'validation' ? 'Validation' : 'Envoi' }}</b> {{ $visa->user?->full_name }}@if($visa->structure) · {{ $visa->structure->nom }}@endif · {{ $visa->signe_le->format('d/m/Y') }}</span>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @php $affectationsVisibles = $version->affectations->filter(fn ($a) => $a->controleur?->structureVisa() === $user->structureVisa()); @endphp
                    @if($peutVoirAnalyse && $affectationsVisibles->isNotEmpty())
                        <section class="ver-sec">
                            <h4>Collaborateurs</h4>
                            <div class="ver-list">
                                @foreach($affectationsVisibles as $affectation)
                                    <div class="ver-row">
                                        <span class="ver-av">{{ $initiales($affectation->controleur) }}</span>
                                        <div class="ver-who"><strong>{{ $affectation->controleur->full_name }}</strong>@if($affectation->specialite)<small>{{ $affectation->specialite }}</small>@endif</div>
                                        <span class="badge badge-attente">{{ $libAff[$affectation->statut] ?? ucfirst(str_replace('_', ' ', $affectation->statut)) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        @php $observations = $affectationsVisibles->flatMap->observations; @endphp
                        @if($observations->isNotEmpty())
                            <section class="ver-sec">
                                <h4>Observations</h4>
                                <div class="ver-list">
                                    @foreach($observations as $observation)
                                        <blockquote class="ver-quote {{ $observation->type === 'non_conformite' ? 'nc' : '' }} {{ $observation->retenue ? 'retenue' : '' }}">
                                            <div class="ver-quote-head">
                                                <span>{{ $observation->type === 'non_conformite' ? 'Non-conformité' : 'Observation' }}@if($observation->retenue) · retenue @endif</span>
                                                <small>{{ $observation->auteur->full_name }}</small>
                                            </div>
                                            <p>{{ $observation->contenu }}</p>
                                        </blockquote>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    @endif

                    @if($peutVoirAnalyse)
                        @foreach($version->decisions as $decision)
                            <div class="ver-verdict {{ $decision->estValidee() ? 'ok' : 'ko' }}">
                                <strong>{{ $decision->estValidee() ? 'Validé' : 'À corriger' }}</strong>
                                <span>par {{ $decision->validateur->full_name }} · {{ $decision->date_decision->format('d/m/Y à H:i') }}</span>
                                @if($decision->commentaires)<p>{{ $decision->commentaires }}</p>@endif
                            </div>
                        @endforeach
                    @endif
                </article>
            @endforeach
        </div>
    </div>

    @if($dossier->transmissions->isNotEmpty())
        <div class="card">
            <h3 class="card-title">Transmissions</h3>
            @foreach($dossier->transmissions as $transmission)
                <div style="padding:12px 0; border-top:1px solid var(--line); font-size: var(--fs-m);">
                    <strong>{{ match($transmission->mode) { 'simple' => 'Transmission simple', 'validation' => 'Envoi en circuit de visa', 'rfi' => 'Réponse à une demande d\'information', default => 'Diffusion post-validation' } }}</strong>
                    par {{ $transmission->emetteur->full_name }} le {{ $transmission->date_transmission->format('d/m/Y H:i') }}
                    <div style="font-size: var(--fs-s); color:var(--muted);">
                        Destinataires : {{ $transmission->destinataires->map(fn($d) => $d->structure?->nom ?? $d->user?->full_name)->filter()->implode(', ') ?: '—' }}
                    </div>
                    @if($transmission->commentaire)<div style="font-size: var(--fs-m); color:var(--muted); margin-top:2px;">{{ $transmission->commentaire }}</div>@endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
