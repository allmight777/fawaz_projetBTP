@extends('emails.layout')
@section('title', 'Document corrigé et resoumis')
@section('subtitle', 'Une nouvelle version est disponible pour contrôle')
@section('body')
    <p><strong>{{ $emetteur->full_name }}</strong> ({{ $emetteur->structure?->nom ?? 'N/A' }}), qui avait précédemment transmis ce document, l'a corrigé à la suite de votre refus et vous le resoumet pour contrôle.</p>
    @include('emails.partials.infos', ['titre' => 'Informations du document', 'lignes' => [
        'Titre' => $dossier->titre,
        'Type' => $dossier->documentType->nom ?? 'Non défini',
        'Nouvelle version' => 'V'.$version->numero_version,
        'Entreprise' => $emetteur->structure?->nom ?? 'Non définie',
    ]])
    @include('emails.partials.note', ['titre' => "Message de l'émetteur", 'texte' => $version->commentaire])
@endsection
@section('action', 'Se connecter')
@section('action_url', $url)
@section('action_note', 'Connectez-vous pour consulter le document et vérifier les corrections apportées.')
