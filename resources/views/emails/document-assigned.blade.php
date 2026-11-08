@extends('emails.layout')
@section('title', 'Document assigné')
@section('subtitle', 'Un document vous a été assigné pour contrôle')
@section('body')
    <p><strong>{{ $affectePar->full_name }}</strong> vous a assigné un nouveau document pour contrôle et observation.</p>
    @include('emails.partials.infos', ['titre' => 'Informations du document', 'lignes' => [
        'Titre' => $dossier->titre,
        'Fichier' => $version->nom_affiche,
        'Identifiant' => $dossier->identifiant ?? '#'.$dossier->id,
        'Délai' => (isset($affectation) && $affectation && $affectation->date_limite) ? $affectation->date_limite->translatedFormat('d/m/Y à H:i') : null,
    ]])
@endsection
@section('action', 'Se connecter')
@section('action_url', route('login'))
@section('action_note', 'Connectez-vous à votre espace pour consulter et traiter ce document.')
