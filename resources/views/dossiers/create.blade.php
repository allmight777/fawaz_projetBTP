@extends(auth()->user()->spaceLayout())

@section('title', 'Importer un document')

@section('content')
<div class="page-header"><h1>Importer un document</h1></div>

<div class="card" style="max-width:820px;">
    <form method="POST" action="{{ route('dossiers.store') }}" enctype="multipart/form-data"
          x-data="{
              typeId: '{{ old('document_type_id') }}',
              modeTraitement: null,
              checklist: [],
              async chargerChecklist() {
                  if (!this.typeId) { this.checklist = []; this.modeTraitement = null; return; }
                  const type = @js($documentTypes->keyBy('id'));
                  this.modeTraitement = type[this.typeId]?.mode_traitement ?? null;
                  const res = await fetch('/document-types/' + this.typeId + '/checklist');
                  this.checklist = await res.json();
              }
          }" x-init="chargerChecklist()">
        @csrf

        <div class="form-group">
            <label for="document_type_id">Type de document</label>
            <select id="document_type_id" name="document_type_id" x-model="typeId" @change="chargerChecklist()" required>
                <option value="">Sélectionner un type</option>
                @foreach($documentTypes as $type)
                    <option value="{{ $type->id }}" {{ old('document_type_id') == $type->id ? 'selected' : '' }}>[{{ $type->code }}] {{ $type->nom }} — {{ $type->libelle_categorie }}</option>
                @endforeach
            </select>
            @error('document_type_id')<div class="visa-err">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" x-show="modeTraitement" x-cloak>
            <span class="badge" :class="modeTraitement === 'validation' ? 'badge-controle' : 'badge-valide'"
                  x-text="modeTraitement === 'validation' ? 'Soumis à validation par le Bureau de Contrôle' : 'Transmission simple (sans validation)'"></span>
        </div>

        <div class="form-group" x-show="checklist.length > 0" x-cloak>
            <label>Checklist requise pour ce type de document</label>
            <ul style="padding-left:20px; color:var(--muted); display:grid; gap:4px;">
                <template x-for="item in checklist" :key="item.id">
                    <li x-text="item.libelle + (item.obligatoire ? ' (obligatoire)' : '')"></li>
                </template>
            </ul>
            <small style="color:var(--muted)">Elle sera à compléter juste après l'import.</small>
        </div>

        <div class="form-group">
            <label>Structure émettrice</label>
            <p>{{ Auth::user()->structure?->nom ?? 'Aucune structure associée à votre compte — le dossier sera importé sans structure émettrice.' }}</p>
        </div>

        <div class="form-group">
            <label for="lot_id">Lot</label>
            <select id="lot_id" name="lot_id">
                <option value="">Sélectionner</option>
                @foreach($lots as $lot)
                    <option value="{{ $lot->id }}" {{ old('lot_id') == $lot->id ? 'selected' : '' }}>{{ $lot->code }} - {{ $lot->nom }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="titre">Titre du document</label>
            <input id="titre" name="titre" type="text" value="{{ old('titre') }}" required>
            @error('titre')<div class="visa-err">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
        </div>

        <div class="form-group">
            <label for="fichier">Fichier</label>
            <input id="fichier" name="fichier" type="file">
            <input id="fichier_url" name="fichier_url" type="url" value="{{ old('fichier_url') }}" placeholder="ou lien externe : https://..." style="margin-top:10px;">
            @error('fichier')<div class="visa-err">{{ $message }}</div>@enderror
        </div>

        <div class="form-actions" style="justify-content:flex-start;">
            <button type="submit" class="btn-submit">Importer</button>
            <a href="{{ route('dossiers.index') }}" class="btn-back">Annuler</a>
        </div>
    </form>
</div>
@endsection
