@extends('layouts.admin')

@section('title', 'Modifier un type de document')

@section('content')
<div class="page-header">
    <h1>Modifier · {{ $documentType->code }}</h1>
    <a href="{{ route('admin.document-types.index') }}" class="btn-back">Retour aux types</a>
</div>

<form action="{{ route('admin.document-types.update', $documentType->id) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.document-types._form')

    <div class="dt-actions">
        <button type="submit" class="btn-submit" style="width:auto;">Enregistrer les modifications</button>
        <a href="{{ route('admin.document-types.index') }}" class="btn-back">Annuler</a>
    </div>
</form>
@endsection
