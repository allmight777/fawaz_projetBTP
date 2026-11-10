{{-- Icône « Partager » : ajoute une structure oubliée à un circuit de visa en cours. Attend $dossier. --}}
@php
    $circuitPartage = app(\App\Services\CircuitVisaService::class);
    $peutPartager = $circuitPartage->peutPartager(auth()->user(), $dossier);
    $deja = $peutPartager ? $dossier->circuitVisas->where('document_version_id', $dossier->circuitVisas->max('document_version_id'))->pluck('structure_type')->all() : [];
    $idPartage = 'partager-' . $dossier->id;
@endphp
@if($peutPartager)
    <button type="button" class="btn-link" title="Ajouter une structure au circuit de visa" aria-label="Ajouter une structure au circuit de visa" style="border:none; background:none; cursor:pointer;"
            onclick="document.getElementById('{{ $idPartage }}').classList.add('open')">
        <i class="fas fa-user-plus"></i>@if(!empty($libelle)) Ajouter une structure au circuit @endif
    </button>

    <div class="modal" id="{{ $idPartage }}" onclick="if(event.target===this)this.classList.remove('open')">
        <div class="modal-content md-modal">
            <form method="POST" action="{{ route('dossiers.partager', $dossier) }}">
                @csrf
                <div class="md-head">
                    <span class="md-ico" style="background:var(--accent-s); color:var(--accent-d);"><i class="fas fa-user-plus"></i></span>
                    <div>
                        <h3>Ajouter une structure au circuit</h3>
                        <p>{{ $dossier->identifiant_affiche }} · {{ \Illuminate\Support\Str::limit($dossier->titre, 50) }}</p>
                    </div>
                </div>
                <p class="card-hint" style="margin-bottom:12px;">Vous avez oublié une structure ? Elle recevra le document et visera à son niveau. Le circuit en cours et les visas déjà obtenus ne changent pas.</p>
                @include('partials.circuit-choix', ['nom' => 'structures[]', 'exclure' => $deja])
                <label class="md-label" style="margin-top:14px;" for="{{ $idPartage }}-c">Message (facultatif)</label>
                <textarea id="{{ $idPartage }}-c" name="commentaire" rows="2" maxlength="2000"></textarea>
                <div class="modal-actions">
                    <button type="button" class="btn-modal-close" onclick="document.getElementById('{{ $idPartage }}').classList.remove('open')">Annuler</button>
                    <button type="submit" class="btn-applicable"><i class="fas fa-paper-plane"></i> Ajouter au circuit</button>
                </div>
            </form>
        </div>
    </div>
@endif
