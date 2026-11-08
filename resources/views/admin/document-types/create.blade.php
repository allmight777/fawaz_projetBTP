@extends('layouts.admin')

@section('title', 'Ajouter un type de document')

@section('content')
<div class="page-header">
    <h1>Nouveau type de document</h1>
    <a href="{{ route('admin.document-types.index') }}" class="btn-back">Retour aux types</a>
</div>

<form action="{{ route('admin.document-types.store') }}" method="POST">
    @csrf
    @include('admin.document-types._form')

    <div class="dt-actions">
        <button type="submit" class="btn-submit" style="width:auto;">Créer le type</button>
        <a href="{{ route('admin.document-types.index') }}" class="btn-back">Annuler</a>
    </div>
</form>
@endsection
