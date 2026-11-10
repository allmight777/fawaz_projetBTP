@extends('emails.layout')
@section('title', 'Document validé')
@section('subtitle', ($controleur->structure?->nom ?? 'Une structure').' a validé votre document')
@section('body')
    <p>Votre document a été validé par <strong>{{ $controleur->structure?->nom ?? 'une structure de visa' }}</strong>. La version validée porte les visas (cachets et signatures) et le tampon de validation.</p>
    @include('emails.partials.infos', ['titre' => 'Informations du document', 'lignes' => [
        'Titre' => $dossier->titre,
        'Type' => $dossier->documentType->nom ?? 'Non défini',
        'Version validée' => 'V'.$version->numero_version,
        'Structure émettrice' => $dossier->creePar->structure->nom ?? 'Non définie',
    ]])
    @include('emails.partials.note', ['titre' => 'Commentaire du validateur', 'texte' => $decision?->commentaires, 'ton' => 'ok'])
@endsection
@section('action', 'Se connecter')
@section('action_url', $url)
@section('action_note', 'Consultez le document validé depuis votre espace.')
