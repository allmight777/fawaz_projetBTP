@extends('emails.layout')
@section('title', "Invitation à un événement")
@section('subtitle', 'Merci de confirmer votre présence')
@section('body')
    <p>Bonjour <strong>{{ $destinataire->prenom ?? $destinataire->nom }}</strong>, <strong>{{ $event->createur->full_name }}</strong> vous a inscrit(e) à l'événement suivant.</p>
    @include('emails.partials.infos', ['titre' => $event->titre, 'lignes' => [
        'Date' => $event->date_debut->translatedFormat('d/m/Y à H:i') . ($event->date_fin ? ' → ' . $event->date_fin->translatedFormat('d/m/Y à H:i') : ''),
        'Lieu' => $event->lieu,
    ]])
    @include('emails.partials.note', ['titre' => 'Description', 'texte' => $event->description])
    @if($autresParticipants->count() > 0)
        <div class="participants-title">Autres personnes invitées</div>
        <div>@foreach($autresParticipants as $participant)<span class="participant">{{ $participant->full_name }}</span>@endforeach</div>
    @endif
@endsection
@section('action', 'Se connecter')
@section('action_url', $url)
@section('action_note', 'Connectez-vous à votre espace pour confirmer votre présence.')
