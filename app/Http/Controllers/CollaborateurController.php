<?php

namespace App\Http\Controllers;

use App\Models\DocumentAssignment;
use App\Models\Dossier;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/** « Mes collaborateurs » : le chef consulte l'équipe de sa structure (lecture seule). */
class CollaborateurController extends Controller
{
    public function index()
    {
        $chef = $this->chef();

        $collaborateurs = $this->equipe($chef)
            ->with(['lot', 'structure'])
            ->withCount([
                'dossiersImportes',
                'affectations as affectations_en_cours_count' => fn ($q) => $q->where('statut', '!=', 'termine'),
                'affectations as affectations_terminees_count' => fn ($q) => $q->where('statut', 'termine'),
            ])
            ->orderBy('prenom')->orderBy('nom')
            ->get();

        $stats = [
            'total' => $collaborateurs->count(),
            'actifs' => $collaborateurs->where('actif', true)->count(),
            'inactifs' => $collaborateurs->where('actif', false)->count(),
            'en_cours' => $collaborateurs->sum('affectations_en_cours_count'),
        ];

        return view('collaborateurs.index', compact('collaborateurs', 'stats', 'chef'));
    }

    public function show(User $user)
    {
        $chef = $this->chef();
        // Un chef ne voit que les collaborateurs de sa propre structure.
        $collaborateur = $this->equipe($chef)->with(['lot', 'structure'])->findOrFail($user->id);

        $dossiers = Dossier::where('cree_par', $collaborateur->id)->with('documentType')->latest()->limit(8)->get();
        $affectations = DocumentAssignment::where('controleur_id', $collaborateur->id)
            ->with('documentVersion.dossier')->latest()->limit(8)->get();
        $visibles = Dossier::visiblePar($chef)
            ->whereIn('id', $dossiers->pluck('id')->merge($affectations->pluck('documentVersion.dossier_id'))->filter()->unique())
            ->pluck('id')->flip();

        $compteurs = [
            'documents' => Dossier::where('cree_par', $collaborateur->id)->count(),
            'en_cours' => DocumentAssignment::where('controleur_id', $collaborateur->id)->where('statut', '!=', 'termine')->count(),
            'terminees' => DocumentAssignment::where('controleur_id', $collaborateur->id)->where('statut', 'termine')->count(),
        ];

        return view('collaborateurs.show', compact('collaborateur', 'dossiers', 'affectations', 'visibles', 'compteurs', 'chef'));
    }

    private function chef(): User
    {
        $chef = Auth::user();
        abort_unless($chef->peutViser() && !$chef->isAdmin(), 403, 'Réservé aux chefs de structure.');

        return $chef;
    }

    /** Collaborateurs de la structure du chef (ou contrôleurs, pour un chef de lot sans structure). */
    private function equipe(User $chef)
    {
        $requete = User::query()->where('id', '!=', $chef->id)->where('role', '!=', User::ROLE_EN_ATTENTE);

        if ($chef->structure_id) {
            return $requete->where('structure_id', $chef->structure_id)->where('categorie_role', User::CATEGORIE_COLLABORATEUR);
        }

        return $requete->controleurs();
    }
}
