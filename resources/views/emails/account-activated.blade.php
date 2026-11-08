@extends('emails.layout')
@section('title', 'Compte activé')
@section('subtitle', 'Votre compte FawazBTP est désormais actif')
@section('body')
    <p>Bonjour <strong>{{ $user->full_name }}</strong>, votre compte a été <strong>activé</strong> par l'administrateur.</p>
    @include('emails.partials.infos', ['titre' => 'Votre compte', 'lignes' => [
        'Email' => $user->email,
        'Structure' => $user->structure?->nom ?? 'Non définie',
        'Rôle' => $user->role,
    ]])
    @include('emails.partials.note', ['titre' => "Message de l'administrateur", 'texte' => $raison ?? null, 'ton' => 'warn'])
@endsection
@section('action', 'Se connecter')
@section('action_url', route('login'))
@section('action_note', "Vous pouvez dès maintenant accéder à l'ensemble des fonctionnalités.")
