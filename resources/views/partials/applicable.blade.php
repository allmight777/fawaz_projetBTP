{{-- Statut « applicable » : bouton pour le chef de l'entreprise émettrice, bandeau pour tous. Attend $dossier. --}}
@if($dossier->estApplicable())
    <div class="applicable-banner">
        <span class="ab-ico"><i class="fas fa-circle-check"></i></span>
        <div>
            <strong>Document applicable</strong>
            <span>Rendu applicable le {{ $dossier->applicable_le->format('d/m/Y à H:i') }} — il sert de référence à toutes les structures du chantier.</span>
        </div>
    </div>
@elseif($dossier->applicablePar(auth()->user()))
    <div class="applicable-banner a-todo">
        <span class="ab-ico"><i class="fas fa-stamp"></i></span>
        <div>
            <strong>Tous les visas sont obtenus</strong>
            <span>Vous pouvez rendre ce document applicable : il deviendra la référence de toutes les structures. S'il repasse « À corriger », il cessera d'être applicable.</span>
        </div>
        <form method="POST" action="{{ route('dossiers.applicable', $dossier) }}" data-confirm="Document : {{ $dossier->identifiant_affiche }} · {{ \Illuminate\Support\Str::limit($dossier->titre, 50) }}" data-confirm-title="Rendre ce document applicable ?" data-confirm-ok="Rendre applicable" data-confirm-icon="stamp" data-confirm-details="Il devient la référence de toutes les structures du chantier|Il apparaît dans « Documents applicables »|S'il repasse « À corriger », il cesse d'être applicable">
            @csrf
            <button type="submit" class="btn-applicable"><i class="fas fa-check-double"></i> Rendre applicable</button>
        </form>
    </div>
@endif
