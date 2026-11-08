@extends('emails.layout')
@section('title', 'Compte suspendu')
@section('subtitle', 'Votre accès à la plateforme est mis en pause')
@section('body')
    <p>Bonjour <strong>{{ $user->full_name }}</strong>, votre compte a été <strong>désactivé temporairement</strong> par l'administrateur. Vous ne pouvez plus vous connecter tant qu'il n'a pas été réactivé.</p>
    @include('emails.partials.note', ['titre' => 'Raison', 'texte' => $raison ?? null, 'ton' => 'warn'])
    @include('emails.partials.infos', ['titre' => 'Votre compte', 'lignes' => [
        'Email' => $user->email,
        'Structure' => $user->structure?->nom ?? 'Non définie',
    ]])
@endsection
@section('action', "Contacter l'administrateur")
@section('action_url', 'mailto:'.config('mail.from.address'))
@section('action_note', "Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur.")
