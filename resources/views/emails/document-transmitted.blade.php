@extends('emails.layout')
@section('title', 'Document transmis')
@section('subtitle', 'Un nouveau document vous a été transmis')
@section('body')
    <p>Vous avez reçu un document de la part de <strong>{{ $emetteur->full_name }}</strong>.</p>
    @include('emails.partials.infos', ['titre' => 'Informations du document', 'lignes' => [
        'Titre' => $dossier->titre,
        'Type' => $dossier->documentType->nom ?? 'Non défini',
        'Version' => 'V'.$version->numero_version,
        'Mode' => $mode === 'validation' ? 'À valider' : 'Informatif',
    ]])
    @include('emails.partials.note', ['titre' => 'Commentaire', 'texte' => $version->commentaire])
@endsection
@section('action', 'Voir le document')
@section('action_url', $url)
@section('action_note', 'Connectez-vous pour consulter le document.')
