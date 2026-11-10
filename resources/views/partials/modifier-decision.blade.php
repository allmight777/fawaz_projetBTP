{{-- Bouton jaune « Modifier la décision » + fenêtre de confirmation. Attend $version (dernière version du dossier). --}}
@php
    $circuitSvc = app(\App\Services\CircuitVisaService::class);
    $peutModifier = isset($version) && $version && $circuitSvc->peutModifierDecision(auth()->user(), $version);
@endphp
@if($peutModifier)
    <button type="button" class="btn-decision-edit" onclick="document.getElementById('modifierDecisionModal').classList.add('open')">
        <i class="fas fa-pen-to-square"></i> Modifier la décision
    </button>

    <div class="modal" id="modifierDecisionModal" onclick="if(event.target===this)this.classList.remove('open')">
        <div class="modal-content md-modal">
            <form method="POST" action="{{ route('dossiers.decision.modifier', $version) }}">
                @csrf
                <div class="md-head">
                    <span class="md-ico"><i class="fas fa-triangle-exclamation"></i></span>
                    <div>
                        <h3>Modifier votre décision ?</h3>
                        <p>Vous avez déjà visé ce document ({{ $version->dossier->identifiant_affiche }}).</p>
                    </div>
                </div>

                <ul class="md-effets">
                    <li><i class="fas fa-rotate-left"></i> Le document repasse en <strong>« À corriger »</strong> chez l'émetteur.</li>
                    <li><i class="fas fa-file-circle-plus"></i> L'émetteur pourra envoyer une nouvelle version, qui vous reviendra pour visa.</li>
                    <li><i class="fas fa-envelope"></i> L'émetteur et son chef sont prévenus par email et par alerte.</li>
                    <li><i class="fas fa-clock-rotate-left"></i> La date, l'heure et votre motif sont enregistrés dans l'historique du document.</li>
                </ul>

                <label class="md-label" for="md-motif">Motif de la modification <span>*</span></label>
                <textarea id="md-motif" name="motif" rows="4" required minlength="5" maxlength="2000" placeholder="Expliquez ce qui doit être corrigé…">{{ old('motif') }}</textarea>
                @error('motif')<div class="md-err">{{ $message }}</div>@enderror

                <div class="modal-actions">
                    <button type="button" class="btn-modal-close" onclick="document.getElementById('modifierDecisionModal').classList.remove('open')">Annuler</button>
                    <button type="submit" class="btn-decision-edit"><i class="fas fa-check"></i> Confirmer la modification</button>
                </div>
            </form>
        </div>
    </div>
    @if($errors->has('motif'))<script>document.getElementById('modifierDecisionModal').classList.add('open');</script>@endif
@endif
