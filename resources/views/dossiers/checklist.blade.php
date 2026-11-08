@extends(auth()->user()->spaceLayout())

@section('title', 'Checklist')

@section('content')
<div class="page-header"><h1>Checklist · {{ $documentVersion->dossier->identifiant }} (V{{ $documentVersion->numero_version }})</h1></div>

@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div class="card">
    @if($items->isEmpty())
        <p>Ce type de document n'a pas de checklist paramétrée.</p>
        <form method="POST" action="{{ route('document-versions.checklist.store', $documentVersion) }}" style="margin-top:16px;">
            @csrf
            <button type="submit" class="btn-submit">Soumettre au Bureau de Contrôle</button>
        </form>
    @else
        <form method="POST" action="{{ route('document-versions.checklist.store', $documentVersion) }}">
            @csrf
            <div style="display:grid; gap:12px;">
                @foreach($items as $item)
                    @php $reponse = $reponses->get($item->id); @endphp
                    <div style="background:var(--bg); border-radius:18px; padding:14px 16px;">
                        <label style="display:flex; gap:12px; align-items:flex-start; cursor:pointer;">
                            <input type="checkbox" name="reponses[{{ $item->id }}][coche]" value="1" {{ $reponse?->coche ? 'checked' : '' }} style="margin-top:4px; width:18px; height:18px; accent-color:var(--accent);">
                            <span style="font-weight:700;">{{ $item->libelle }} @if($item->obligatoire)<span style="color:var(--alert)">*</span>@endif</span>
                        </label>
                        <input type="text" name="reponses[{{ $item->id }}][commentaire]" value="{{ $reponse?->commentaire }}" placeholder="Commentaire (optionnel)" style="margin-top:10px;">
                    </div>
                @endforeach
            </div>
            <div class="form-actions" style="justify-content:flex-start;">
                <button type="submit" class="btn-submit">Valider la checklist et soumettre</button>
            </div>
        </form>
    @endif
</div>
@endsection
