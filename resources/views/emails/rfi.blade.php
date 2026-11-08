@extends('emails.layout')

@php
    $titres = [
        'demandee' => ["Demande d'information", $demande->demandeur->full_name . ' vous demande un document'],
        'accordee' => ['Accès accordé', 'Le document demandé est disponible'],
        'refusee' => ['Demande refusée', "Votre demande d'information n'a pas pu être satisfaite"],
        'recue' => ['Réception confirmée', 'Le demandeur a bien reçu le document'],
    ];
@endphp

@section('title', $titres[$etape][0])
@section('subtitle', $titres[$etape][1])

@section('body')
    @if($etape === 'demandee')
        <p><strong>{{ $demande->demandeur->full_name }}</strong> ({{ $demande->demandeur->structure?->nom ?? 'FawazBTP' }}) vous demande une information.
        Si vous détenez le document, donnez-lui accès depuis votre espace.</p>
    @elseif($etape === 'accordee')
        <p><strong>{{ $demande->destinataire->full_name }}</strong> vous a donné accès au document « <strong>{{ $demande->dossier?->titre }}</strong> ». Vous pouvez le consulter dès maintenant puis confirmer sa réception.</p>
    @elseif($etape === 'refusee')
        <p><strong>{{ $demande->destinataire->full_name }}</strong> n'a pas donné suite à votre demande.</p>
    @else
        <p><strong>{{ $demande->demandeur->full_name }}</strong> confirme avoir bien reçu le document « <strong>{{ $demande->dossier?->titre }}</strong> ». La demande est clôturée.</p>
    @endif

    @include('emails.partials.infos', ['titre' => 'La demande', 'lignes' => [
        'Objet' => $demande->objet,
        'Demandeur' => $demande->demandeur->full_name,
        'Destinataire' => $demande->destinataire->full_name,
        'Document' => $demande->dossier ? ($demande->dossier->identifiant . ' · ' . $demande->dossier->titre) : null,
    ]])

    @if($etape === 'demandee')
        @include('emails.partials.note', ['titre' => 'Message', 'texte' => $demande->message])
    @else
        @include('emails.partials.note', ['titre' => $etape === 'refusee' ? 'Motif' : 'Message', 'texte' => $demande->reponse, 'ton' => $etape === 'refusee' ? 'bad' : 'ok'])
    @endif
@endsection

@section('action', $etape === 'demandee' ? 'Répondre à la demande' : ($etape === 'accordee' ? 'Consulter et confirmer' : 'Voir la demande'))
@section('action_url', $url)
@section('action_note', 'Connectez-vous pour continuer.')
