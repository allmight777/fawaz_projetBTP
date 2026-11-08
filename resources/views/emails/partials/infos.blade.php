{{-- $titre (string), $lignes (label => valeur) --}}
<div class="info-box">
    <div class="info-title">{{ $titre ?? 'Informations' }}</div>
    @foreach($lignes as $label => $valeur)
        @if(filled($valeur))
            <div class="info-row">
                <span class="info-label">{{ $label }}</span>
                <span class="info-value">{{ $valeur }}</span>
            </div>
        @endif
    @endforeach
</div>
