@extends(auth()->user()->spaceLayout())

@section('title', 'Importer une correction')

@section('content')
<div class="page-header"><h1>Importer une correction · {{ $dossier->identifiant }}</h1></div>

<div class="card" style="max-width:760px;">
    <p style="color:var(--muted); margin-bottom:18px;">Ce dossier a été renvoyé pour correction. Importer une nouvelle version relance un cycle d'analyse complet (V{{ $dossier->version_courante + 1 }}).</p>
    <form method="POST" action="{{ route('dossiers.versions.store', $dossier) }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="fichier">Fichier corrigé</label>
            <input id="fichier" name="fichier" type="file">
            <input id="fichier_url" name="fichier_url" type="url" value="{{ old('fichier_url') }}" placeholder="ou lien externe" style="margin-top:10px;">
            @error('fichier')<div class="visa-err">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="commentaire">Commentaire</label>
            <textarea id="commentaire" name="commentaire" rows="3">{{ old('commentaire') }}</textarea>
        </div>
        <button type="submit" class="btn-submit">Importer la nouvelle version</button>
    </form>
</div>
@endsection
