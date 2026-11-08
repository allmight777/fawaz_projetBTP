@extends($user->spaceLayout())

@section('title', $demande->objet)

@section('content')
@php
    $pastille = ['en_attente' => 'badge-attente', 'accordee' => 'badge-valide', 'refusee' => 'badge-rejete', 'cloturee' => 'badge-controle'];
    $estDestinataire = $demande->destinataire_id === $user->id;
    $estDemandeur = $demande->demandeur_id === $user->id;
@endphp

<div class="page-header">
    <h1>Demande d'information</h1>
    <a href="{{ route('rfi.index') }}" class="btn-back">Retour</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

<div class="split" style="display:grid; grid-template-columns:1.3fr 1fr; gap:14px; align-items:start;">
    <div class="card">
        <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:14px;">
            <h3 style="font-weight:800; font-size:21px; letter-spacing:-.02em;">{{ $demande->objet }}</h3>
            <span class="badge {{ $pastille[$demande->statut] ?? '' }}">{{ $demande->statut_label }}</span>
        </div>
        <p style="white-space:pre-line;">{{ $demande->message }}</p>

        @if($demande->reponse)
            <div style="background:var(--bg); border-radius:18px; padding:16px 18px; margin-top:18px;">
                <strong>{{ $demande->statut === 'refusee' ? 'Motif du refus' : 'Réponse' }}</strong>
                <p style="white-space:pre-line; margin-top:4px;">{{ $demande->reponse }}</p>
            </div>
        @endif

        @if($demande->dossier && ($estDemandeur || $estDestinataire))
            <div style="background:var(--accent-s); border-radius:18px; padding:16px 18px; margin-top:18px; display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap;">
                <div><strong>Document partagé</strong><br><span style="color:var(--muted)">{{ $demande->dossier->identifiant_affiche }} · {{ $demande->dossier->titre }}</span></div>
                <a href="{{ route('dossiers.show', $demande->dossier) }}" class="btn">Consulter le document</a>
            </div>
        @endif

        @if($estDemandeur && $demande->statut === 'accordee')
            <form method="POST" action="{{ route('rfi.recu', $demande) }}" style="margin-top:18px;">
                @csrf
                <button type="submit" class="btn-submit">J'ai bien reçu le document</button>
                <small style="display:block; color:var(--muted); margin-top:6px;">{{ $demande->destinataire->full_name }} en sera informé par mail et la demande sera clôturée.</small>
            </form>
        @endif
    </div>

    <div style="display:grid; gap:14px;">
        <div class="card">
            <h3>Échange</h3>
            @php $ini = fn ($u) => mb_strtoupper(mb_substr($u->prenom ?: '', 0, 1) . mb_substr($u->nom ?: '', 0, 1)) ?: '?'; @endphp
            <div class="rfi-who">
                <div class="rfi-p">
                    <span class="ver-av">{{ $ini($demande->demandeur) }}</span>
                    <div><small>De</small><strong>{{ $demande->demandeur->structure?->nom ?? $demande->demandeur->full_name }}</strong><span>{{ $demande->demandeur->full_name }}</span></div>
                </div>
                <div class="rfi-link"></div>
                <div class="rfi-p">
                    <span class="ver-av">{{ $ini($demande->destinataire) }}</span>
                    <div><small>À</small><strong>{{ $demande->destinataire->structure?->nom ?? $demande->destinataire->full_name }}</strong><span>{{ $demande->destinataire->full_name }}</span></div>
                </div>
            </div>
            <div class="rfi-foot">
                
                <span>{{ $demande->created_at->format('d/m/Y à H:i') }}</span>
            </div>
        </div>

        @if($estDestinataire && $demande->statut === 'en_attente')
            <div class="card">
                <h3>Donner accès à un document</h3>
                <form method="POST" action="{{ route('rfi.accorder', $demande) }}">
                    @csrf
                    <div class="form-group">
                        <label for="dossier_id">Document à partager</label>
                        <select name="dossier_id" id="dossier_id" required>
                            <option value="">Choisir un document…</option>
                            @foreach($dossiers as $d)
                                <option value="{{ $d->id }}">{{ $d->identifiant_affiche }} · {{ $d->titre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reponse_ok">Message (facultatif)</label>
                        <textarea name="reponse" id="reponse_ok" rows="3" maxlength="2000"></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Accorder l'accès</button>
                </form>
            </div>
            <div class="card">
                <h3>Ne pas donner suite</h3>
                <form method="POST" action="{{ route('rfi.refuser', $demande) }}">
                    @csrf
                    <div class="form-group">
                        <label for="reponse_ko">Motif</label>
                        <textarea name="reponse" id="reponse_ko" rows="3" maxlength="2000" required></textarea>
                    </div>
                    <button type="submit" class="btn-danger">Refuser la demande</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
