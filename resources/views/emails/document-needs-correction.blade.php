@extends('emails.layout')
@section('title', 'Correction demandée')
@section('subtitle', 'Le Bureau de Contrôle demande une correction de votre document')
@section('body')
    <p>Le Bureau de Contrôle a examiné le document suivant et demande une <strong>correction</strong> avant validation finale.</p>
    @include('emails.partials.infos', ['titre' => 'Informations du document', 'lignes' => [
        'Titre' => $dossier->titre,
        'Type' => $dossier->documentType->nom ?? 'Non défini',
        'Version analysée' => 'V'.$version->numero_version,
        'Structure émettrice' => $dossier->creePar->structure->nom ?? 'Non définie',
    ]])
    @include('emails.partials.note', ['titre' => 'Motif de la correction', 'texte' => $decision?->commentaires, 'ton' => 'bad'])
@endsection
@section('action', 'Se connecter')
@section('action_url', $url)
@section('action_note', 'Consultez les observations puis soumettez une version corrigée.')
