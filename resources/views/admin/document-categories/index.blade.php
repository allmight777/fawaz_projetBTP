@extends('layouts.admin')

@section('title', 'Catégories')

@section('content')
<div class="page-header">
    <h1>Catégories de documents</h1>
    <a href="{{ route('admin.document-types.index') }}" class="btn-back">Retour aux types</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

<div class="card" style="margin-bottom:14px;">
    <h3 class="card-title">Nouvelle catégorie</h3>
    <form method="POST" action="{{ route('admin.document-categories.store') }}" class="cat-add">
        @csrf
        <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Nom de la catégorie (ex : Sécurité chantier)" maxlength="120" required>
        <button type="submit" class="btn-submit" style="width:auto;">Créer la catégorie</button>
    </form>
</div>

<div class="card">
    <h3 class="card-title">Catégories existantes</h3>
    <div class="cat-list">
        @forelse($categories as $categorie)
            @php $n = $comptes[$categorie->cle] ?? 0; @endphp
            <div class="cat-row">
                <form method="POST" action="{{ route('admin.document-categories.update', $categorie) }}" class="cat-edit">
                    @csrf @method('PUT')
                    <label class="cat-field cat-ordre"><small>Ordre</small><input type="number" name="ordre" value="{{ $categorie->ordre }}" min="0" max="9999"></label>
                    <label class="cat-field cat-nom"><small>Nom</small><input type="text" name="nom" value="{{ $categorie->nom }}" maxlength="120" required></label>
                    <span class="badge badge-controle">{{ $n }} type{{ $n > 1 ? 's' : '' }}</span>
                    <button type="submit" class="btn">Enregistrer</button>
                </form>
                <form method="POST" action="{{ route('admin.document-categories.destroy', $categorie) }}" data-confirm="La catégorie « {{ $categorie->nom }} » sera supprimée." data-confirm-title="Supprimer cette catégorie ?" data-confirm-ok="Supprimer" data-confirm-tone="danger">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger" @if($n > 0) disabled title="Catégorie non vide" @endif>Supprimer</button>
                </form>
            </div>
        @empty
            <div class="empty-state">Aucune catégorie.</div>
        @endforelse
    </div>
</div>
@endsection
