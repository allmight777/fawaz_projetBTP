@extends($user->spaceLayout())

@section('title', "Nouvelle demande d'information")

@section('content')
<div class="page-header">
    <h1>Nouvelle demande d'information</h1>
    <a href="{{ route('rfi.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<div class="ev-grid rfi-create">
    <form method="POST" action="{{ route('rfi.store') }}" class="ev-card">
        @csrf
        <h3 class="ev-title"><span class="ev-ico"><i class="fas fa-pen-to-square"></i></span> Votre demande</h3>

        <div class="form-group">
            <label for="structure_id">Structure destinataire <span class="required">*</span></label>
            <select name="structure_id" id="structure_id" required>
                <option value="">Choisir une structure…</option>
                @foreach($structures as $structure)
                    <option value="{{ $structure->id }}" {{ (string) old('structure_id') === (string) $structure->id ? 'selected' : '' }}>{{ $structure->nom }}</option>
                @endforeach
            </select>
            @error('structure_id')<div class="visa-err">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="objet">Objet <span class="required">*</span></label>
            <input type="text" name="objet" id="objet" value="{{ old('objet') }}" maxlength="200" required placeholder="Ex. : Plan de ferraillage du niveau R+2">
            @error('objet')<div class="visa-err">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="message">Quelle information ou quel document souhaitez-vous ? <span class="required">*</span></label>
            <textarea name="message" id="message" rows="7" maxlength="3000" required placeholder="Précisez le document recherché, sa version, la raison de la demande…">{{ old('message') }}</textarea>
            @error('message')<div class="visa-err">{{ $message }}</div>@enderror
        </div>

        <div class="form-actions">
            <a href="{{ route('rfi.index') }}" class="btn-back">Annuler</a>
            <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Envoyer la demande</button>
        </div>
    </form>

    <aside class="ev-card rfi-steps">
        <h3 class="ev-title"><span class="ev-ico"><i class="fas fa-route"></i></span> Comment ça marche</h3>
        <ol>
            <li><b>1</b><div><strong>Vous envoyez la demande</strong><span>Le responsable de la structure choisie reçoit un mail.</span></div></li>
            <li><b>2</b><div><strong>Il vous donne l'accès</strong><span>S'il détient le document, il vous le partage ; sinon il vous indique pourquoi.</span></div></li>
            <li><b>3</b><div><strong>Vous confirmez la réception</strong><span>Vous recevez un mail pour consulter le document, puis vous confirmez l'avoir reçu.</span></div></li>
        </ol>
    </aside>
</div>
@endsection
