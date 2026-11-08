@extends('emails.layout')
@section('title', 'Compte refusé')
@section('subtitle', 'Votre demande de création de compte a été refusée')
@section('body')
    <p>Bonjour <strong>{{ $user->full_name }}</strong>, nous regrettons de vous informer que votre demande de compte a été <strong>refusée</strong> par l'administrateur.</p>
    @include('emails.partials.note', ['titre' => 'Raison du refus', 'texte' => $raison ?: "Aucune raison spécifiée par l'administrateur.", 'ton' => 'bad'])
    @include('emails.partials.infos', ['titre' => 'Votre demande', 'lignes' => [
        'Email' => $user->email,
        'Structure' => $user->structure?->nom ?? 'Non définie',
    ]])
@endsection
@section('action', "Contacter l'administrateur")
@section('action_url', 'mailto:'.config('mail.from.address'))
@section('action_note', "Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur.")
