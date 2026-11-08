@extends(auth()->user()->spaceLayout())

@section('title', 'Transmettre')

@section('content')
<div class="page-header"><h1>Transmettre · {{ $dossier->identifiant }}</h1></div>

<div class="card" style="max-width:760px;">
    <form method="POST" action="{{ route('dossiers.transmettre', $dossier) }}">
        @csrf
        <div class="form-group">
            <label>Structures destinataires</label>
            <div style="display:grid; gap:8px;">
                @foreach($structures as $structure)
                    <label style="display:flex; gap:10px; align-items:center; font-weight:600;">
                        <input type="checkbox" name="structures[]" value="{{ $structure->id }}" style="accent-color:var(--accent);"> {{ $structure->nom }} <small style="color:var(--muted)">({{ $structure->libelle_type }})</small>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label>Utilisateurs destinataires</label>
            <div style="display:grid; gap:8px; max-height:220px; overflow-y:auto;">
                @foreach($users as $destinataire)
                    <label style="display:flex; gap:10px; align-items:center; font-weight:600;">
                        <input type="checkbox" name="users[]" value="{{ $destinataire->id }}" style="accent-color:var(--accent);"> {{ $destinataire->full_name }}
                    </label>
                @endforeach
            </div>
        </div>
        @error('destinataires')<div class="visa-err">{{ $message }}</div>@enderror
        <div class="form-group">
            <label for="commentaire">Commentaire</label>
            <textarea id="commentaire" name="commentaire" rows="3"></textarea>
        </div>
        <button type="submit" class="btn-submit">Transmettre</button>
    </form>
</div>
@endsection
