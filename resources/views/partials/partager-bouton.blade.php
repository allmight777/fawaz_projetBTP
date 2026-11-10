{{-- Icône « Partager » (informatif) : à placer dans les actions. Attend $dossier ; la fenêtre vient de partage-informatif. --}}
@if($dossier->retransmissiblePar(auth()->user()))
    <button type="button" class="btn-link btn-partager" title="Partager" aria-label="Partager"
            style="border:none; background:none; cursor:pointer;"
            data-action="{{ route('dossiers.retransmettre', $dossier) }}"
            data-doc="{{ $dossier->identifiant_affiche }} · {{ \Illuminate\Support\Str::limit($dossier->titre, 60) }}">
        <i class="fas fa-share-nodes"></i>
    </button>
@endif
