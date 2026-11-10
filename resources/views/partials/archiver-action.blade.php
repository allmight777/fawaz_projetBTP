{{-- Icône « Archiver » (dans les actions). Attend $dossier ; $libelle pour afficher le texte. --}}
@if($dossier->archivablePar(auth()->user()))
    <form method="POST" action="{{ route('dossiers.archiver', $dossier) }}" style="display:inline;"
          data-confirm="{{ $dossier->identifiant_affiche }} · {{ \Illuminate\Support\Str::limit($dossier->titre, 60) }}"
          data-confirm-title="Archiver ce document ?" data-confirm-ok="Archiver" data-confirm-icon="box-archive" data-confirm-tone="warn"
          data-confirm-details="Il quitte vos documents et passe dans les archives|Il est figé : plus aucune modification ni suppression|Les personnes à qui il a été transmis le retrouvent dans leurs archives">
        @csrf
        <button type="submit" class="btn-link" title="Archiver" style="border:none; background:none; cursor:pointer;">
            <i class="fas fa-box-archive"></i>@if(!empty($libelle)) Archiver @endif
        </button>
    </form>
@endif
