@extends('emails.layout')
@section('title', 'Demande de validation interne')
@section('subtitle', 'Un document nécessite votre validation avant transmission')
@section('body')
    <p>Votre collaborateur <strong>{{ $emetteur->full_name }}</strong> a soumis un document nécessitant votre validation avant sa transmission.</p>
    @include('emails.partials.infos', ['titre' => 'Informations du document', 'lignes' => [
        'Titre' => $dossier->titre,
        'Type' => $dossier->documentType->nom ?? 'Non défini',
        'Version' => 'V'.$version->numero_version,
        'Entreprise' => $emetteur->structure?->nom ?? 'Non définie',
    ]])
    @include('emails.partials.note', ['titre' => 'Message du collaborateur', 'texte' => $version->commentaire])
@endsection
@section('action', 'Examiner et transférer')
@section('action_url', $url)
@section('action_note', 'Consultez le document puis transférez-le en le visant (cachet et signature).')
