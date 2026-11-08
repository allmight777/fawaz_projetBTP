{{-- Choix des structures du circuit de visa : une carte par structure, cochable. Attend $nom (nom du champ) ; $coches, $id facultatifs. --}}
<div class="circuit-choix"@isset($id) id="{{ $id }}"@endisset>
    @foreach(\App\Support\Espaces::STRUCTURES as $typeVisa => $cfgVisa)
        <label class="circuit-option">
            <input type="checkbox" name="{{ $nom }}" value="{{ $typeVisa }}" {{ in_array($typeVisa, $coches ?? [], true) ? 'checked' : '' }}>
            <span class="co-ico"><i class="fas {{ $cfgVisa['icone'] }}"></i></span>
            <span class="co-txt"><strong>{{ $cfgVisa['nom'] }}</strong><em>Visa {{ $cfgVisa['sigle'] }}</em></span>
            <span class="co-check"><i class="fas fa-check"></i></span>
        </label>
    @endforeach
</div>
