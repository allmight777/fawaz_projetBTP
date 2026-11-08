{{-- Cachet + signature. À placer dans un <form> (enctype="multipart/form-data"). --}}
@php $u = auth()->user(); $n = $debut ?? 1; @endphp
<div class="visa-box" data-visa>
    <div class="visa-step"><b>{{ $n }}</b> Cachet</div>
    @if($u->cachet_chemin)
        <div class="visa-cachet">
            <img src="{{ Storage::url($u->cachet_chemin) }}" alt="Votre cachet">
            <div>
                <strong>Votre cachet enregistré sera utilisé.</strong>
                <button type="button" class="visa-link" data-visa-change>Voulez-vous le changer ?</button>
            </div>
        </div>
        <div class="visa-upload" hidden>
            <input type="file" name="cachet" accept="image/png,image/jpeg" disabled>
            <small>Le nouveau cachet remplacera celui de « Mes cachets ».</small>
        </div>
    @else
        <div class="visa-upload">
            <p>Vous n'avez pas encore de cachet. Téléversez-le : il sera mémorisé dans « Mes cachets ».</p>
            <input type="file" name="cachet" accept="image/png,image/jpeg" required>
        </div>
    @endif
    @error('cachet')<div class="visa-err">{{ $message }}</div>@enderror

    <div class="visa-step"><b>{{ $n + 1 }}</b> Signature</div>
    <canvas data-visa-pad class="visa-pad" aria-label="Zone de signature"></canvas>
    <div class="visa-hint">
        <span>Signez avec le doigt, le stylet ou la souris. Elle n'est pas mémorisée : à refaire à chaque envoi.</span>
        <button type="button" class="visa-link" data-visa-clear>Effacer</button>
    </div>
    <input type="hidden" name="signature">
    @error('signature')<div class="visa-err">{{ $message }}</div>@enderror
    <div class="visa-err visa-need">Veuillez signer avant de continuer.</div>
</div>
@once
    <script src="{{ asset('js/visa.js') }}?v={{ @filemtime(public_path('js/visa.js')) }}" defer></script>
@endonce
