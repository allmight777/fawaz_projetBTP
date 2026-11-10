@extends(auth()->user()->spaceLayout())

@section('title', 'Mes cachets')

@section('content')
<h1>Mes cachets</h1>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="split" style="display:grid; grid-template-columns:1fr 1fr; gap:14px; align-items:start;">
    <div class="card">
        <h3>Votre cachet</h3>
        <p style="color:var(--muted); margin-bottom:18px;">Il est apposé sur chaque document que vous envoyez ou validez, avec votre signature. La signature, elle, se dessine à chaque envoi et n'est jamais mémorisée.</p>

        @if($user->cachet_chemin)
            <div style="background:var(--bg); border-radius:20px; padding:22px; display:grid; place-items:center; margin-bottom:16px;">
                <img src="{{ Storage::url($user->cachet_chemin) }}" alt="Votre cachet" style="max-width:240px; max-height:200px; object-fit:contain; background:#fff; border-radius:14px; padding:10px;">
            </div>
        @else
            <div class="empty-state" style="margin-bottom:16px;">Aucun cachet enregistré pour l'instant.</div>
        @endif

        <form method="POST" action="{{ route('cachets.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>{{ $user->cachet_chemin ? 'Remplacer le cachet' : 'Téléverser votre cachet' }}</label>
                <input type="file" name="cachet" accept="image/png,image/jpeg" required>
                @error('cachet')<div class="visa-err">{{ $message }}</div>@enderror
                <small style="color:var(--muted)">PNG ou JPG, 2 Mo maximum. Un fond transparent (PNG) donne le meilleur rendu.</small>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                <button type="submit" class="btn-submit">{{ $user->cachet_chemin ? 'Remplacer' : 'Enregistrer' }}</button>
                @if($user->cachet_chemin)
                    <button type="submit" class="btn-danger" form="cachetSuppression" data-confirm="Vous devrez en téléverser un nouveau avant de pouvoir viser un document." data-confirm-title="Supprimer votre cachet ?" data-confirm-ok="Supprimer" data-confirm-tone="danger" data-confirm-icon="stamp">Supprimer mon cachet</button>
                @endif
            </div>
        </form>

        @if($user->cachet_chemin)
            <form id="cachetSuppression" method="POST" action="{{ route('cachets.destroy') }}">
                @csrf @method('DELETE')
            </form>
        @endif
    </div>

    <div class="card">
        <h3>Comment ça marche</h3>
        <div style="display:grid; gap:12px;">
            <div style="display:flex; gap:14px; align-items:flex-start; background:var(--bg); border-radius:20px; padding:14px 16px;">
                <span style="width:30px; height:30px; border-radius:50%; background:var(--accent); color:#fff; display:grid; place-items:center; font-weight:800; flex-shrink:0;">1</span>
                <div><strong>Vous envoyez un document</strong><div style="color:var(--muted); font-size: var(--fs-m);">à une autre structure : un cadre vous demande votre cachet (celui-ci, ou un nouveau si vous le souhaitez).</div></div>
            </div>
            <div style="display:flex; gap:14px; align-items:flex-start; background:var(--bg); border-radius:20px; padding:14px 16px;">
                <span style="width:30px; height:30px; border-radius:50%; background:var(--accent); color:#fff; display:grid; place-items:center; font-weight:800; flex-shrink:0;">2</span>
                <div><strong>Vous signez</strong><div style="color:var(--muted); font-size: var(--fs-m);">directement dans la fenêtre, à la souris, au doigt ou au stylet.</div></div>
            </div>
            <div style="display:flex; gap:14px; align-items:flex-start; background:var(--bg); border-radius:20px; padding:14px 16px;">
                <span style="width:30px; height:30px; border-radius:50%; background:var(--accent); color:#fff; display:grid; place-items:center; font-weight:800; flex-shrink:0;">3</span>
                <div><strong>Tout est enregistré sur le document</strong><div style="color:var(--muted); font-size: var(--fs-m);">cachet et signature sont posés sur les pages du document (début, quart, milieu, trois quarts et fin), avant l'envoi.</div></div>
            </div>
            <div style="display:flex; gap:14px; align-items:flex-start; background:var(--bg); border-radius:20px; padding:14px 16px;">
                <span style="width:30px; height:30px; border-radius:50%; background:var(--accent); color:#fff; display:grid; place-items:center; font-weight:800; flex-shrink:0;">4</span>
                <div><strong>Le Bureau de Contrôle valide</strong><div style="color:var(--muted); font-size: var(--fs-m);">il ajoute son cachet et sa signature : le document validé porte tous les visas et le tampon de validation.</div></div>
            </div>
        </div>
    </div>
</div>
@endsection
