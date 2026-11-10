{{-- Mail générique au gabarit commun. Variables : titre, sous_titre?, intro (array de phrases), lignes? (label => valeur), note?, note_titre?, ton?, action?, url?, action_note? --}}
@extends('emails.layout')
@section('title', $titre)
@if(!empty($sous_titre))@section('subtitle', $sous_titre)@endif
@section('body')
    @foreach($intro ?? [] as $phrase)
        <p>{!! $phrase !!}</p>
    @endforeach
    @if(!empty($lignes))
        @include('emails.partials.infos', ['titre' => $lignes_titre ?? 'Informations du document', 'lignes' => $lignes])
    @endif
    @if(!empty($note))
        @include('emails.partials.note', ['titre' => $note_titre ?? 'Message', 'texte' => $note, 'ton' => $ton ?? null])
    @endif
@endsection
@if(!empty($action) && !empty($url))
    @section('action', $action)
    @section('action_url', $url)
    @if(!empty($action_note))@section('action_note', $action_note)@endif
@endif
