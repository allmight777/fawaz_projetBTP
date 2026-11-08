{{-- Formulaire partagé : création et modification d'un type de document. $documentType (facultatif), $categories, $modes --}}
@php
    $t = $documentType ?? null;
    $modeActuel = old('mode_traitement', $t?->mode_traitement);
    $actifActuel = (string) old('actif', $t ? (int) $t->actif : 1);
    $descriptions = [
        'simple' => 'Les documents de ce type sont transmis tels quels, sans validation préalable.',
        'validation' => 'Les documents de ce type passent par le contrôle, avec une liste de critères à valider.',
    ];
@endphp

<div class="dt-grid">
    <div class="card">
        <h3 class="card-title">Identification</h3>

        <div class="dt-two">
            <div class="form-group">
                <label for="dt-code">Code <span class="required">*</span></label>
                <input id="dt-code" type="text" name="code" value="{{ old('code', $t?->code) }}" placeholder="Ex : PLN" maxlength="50" required>
                @error('code')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="dt-nom">Nom <span class="required">*</span></label>
                <input id="dt-nom" type="text" name="nom" value="{{ old('nom', $t?->nom) }}" placeholder="Ex : Plans d'exécution" maxlength="255" required>
                @error('nom')<div class="error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-group">
            <label for="dt-cat">Catégorie <span class="required">*</span></label>
            <select id="dt-cat" name="categorie" required>
                <option value="">Sélectionnez une catégorie</option>
                @foreach($categories as $cle => $libelle)
                    <option value="{{ $cle }}" {{ old('categorie', $t?->categorie) === $cle ? 'selected' : '' }}>{{ $libelle }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.document-categories.index') }}" class="dt-link">Gérer les catégories</a>
            @error('categorie')<div class="error">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="card">
        <h3 class="card-title">Traitement</h3>

        <div class="form-group">
            <label>Mode de traitement <span class="required">*</span></label>
            <div class="dt-modes">
                @foreach($modes as $cle => $libelle)
                    <label class="dt-mode">
                        <input type="radio" name="mode_traitement" value="{{ $cle }}" {{ $modeActuel === $cle ? 'checked' : '' }} required>
                        <span class="dt-mode-box">
                            <strong>{{ $cle === 'simple' ? 'Informatif' : 'Circuit de visa' }}</strong>
                            <small>{{ $descriptions[$cle] ?? $libelle }}</small>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('mode_traitement')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label>Statut</label>
            <label class="dt-switch">
                <input type="hidden" name="actif" value="0">
                <input type="checkbox" name="actif" value="1" {{ $actifActuel === '1' ? 'checked' : '' }}>
                <span class="dt-track"><span class="dt-knob"></span></span>
                <span class="dt-switch-label"><span class="dt-st dt-on">Actif</span><span class="dt-st dt-off">Inactif</span><small>Un type inactif n'est plus proposé à l'envoi.</small></span>
            </label>
            @error('actif')<div class="error">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
