@extends('emails.layout')
@section('title', 'Demande de contrôle')
@section('subtitle', 'Un document nécessite votre validation')
@section('body')
    <p><strong>{{ $emetteur->structure?->nom ?? 'N/A' }}</strong> vous transmet un document pour contrôle et validation. Transmis par <strong>{{ $emetteur->full_name }}</strong>, qui l'a visé (cachet et signature).</p>
    @include('emails.partials.infos', ['titre' => 'Informations du document', 'lignes' => [
        'Titre' => $dossier->titre,
        'Type' => $dossier->documentType->nom ?? 'Non défini',
        'Version' => 'V'.$version->numero_version,
        'Structure' => $emetteur->structure?->nom ?? 'Non définie',
    ]])
    @include('emails.partials.note', ['titre' => "Message de l'émetteur", 'texte' => $version->commentaire, 'ton' => 'warn'])
@endsection
@section('action', 'Consulter le document')
@section('action_url', $url)
@section('action_note', 'Connectez-vous pour examiner le document et rendre votre décision.')
