<?php

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaitreOuvrageController extends Controller
{
    /**
     * Requête de base des dossiers visibles par le Maître d'Ouvrage connecté :
     * ceux de son lot, et/ou ceux transmis à sa structure ou à lui directement.
     * Aucune action de modification n'est exposée sur cet espace, uniquement
     * la consultation.
     */
    private function baseQuery()
    {
        $user = Auth::user();
        abort_unless($user->isMaitreOuvrage(), 403, 'Réservé au Maître d\'Ouvrage.');

        $structureId = $user->structure_id;
        $lotId = $user->lot_id;

        return Dossier::where(function ($query) use ($structureId, $lotId) {
            if ($lotId) {
                $query->orWhere('lot_id', $lotId);
            }
            $query->orWhereHas('transmissions.destinataires', function ($q) use ($structureId) {
                $q->where('structure_id', $structureId)
                    ->orWhere('user_id', Auth::id());
            });
        });
    }

    private function dossiersVisibles()
    {
        return $this->baseQuery()
            ->with(['documentType', 'structureEmettrice', 'lot'])
            ->latest()
            ->paginate(15);
    }

    /**
     * Statistiques pour les graphiques du dashboard : répartition des statuts,
     * documents par type, évolution mensuelle et validés/à corriger/en cours.
     */
    private function statistiques(): array
    {
        $statutsAffiches = [
            'soumis' => 'Soumis',
            'en_analyse' => 'En analyse',
            'valide' => 'Validé',
            'a_corriger' => 'À corriger',
            'archive' => 'Archivé',
        ];
        $repartitionStatutsLabels = [];
        $repartitionStatutsData = [];
        foreach ($statutsAffiches as $statut => $label) {
            $repartitionStatutsLabels[] = $label;
            $repartitionStatutsData[] = $this->baseQuery()->where('statut', $statut)->count();
        }

        $parType = $this->baseQuery()
            ->join('document_types', 'document_types.id', '=', 'dossiers.document_type_id')
            ->selectRaw('document_types.nom as nom, count(*) as total')
            ->groupBy('document_types.nom')
            ->orderByDesc('total')
            ->get();

        $months = [];
        $evolutionData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->translatedFormat('M Y');
            $evolutionData[] = $this->baseQuery()
                ->whereYear('dossiers.created_at', $month->year)
                ->whereMonth('dossiers.created_at', $month->month)
                ->count();
        }

        return [
            'repartitionStatutsLabels' => $repartitionStatutsLabels,
            'repartitionStatutsData' => $repartitionStatutsData,
            'parTypeLabels' => $parType->pluck('nom'),
            'parTypeData' => $parType->pluck('total'),
            'evolutionLabels' => $months,
            'evolutionData' => $evolutionData,
            'validesCount' => $this->baseQuery()->where('statut', 'valide')->count(),
            'aCorrigerCount' => $this->baseQuery()->where('statut', 'a_corriger')->count(),
            'enAnalyseCount' => $this->baseQuery()->where('statut', 'en_analyse')->count(),
        ];
    }

    /**
     * Dashboard pour le chef de projet MOA
     */
    public function chefDashboard()
    {
        $dossiers = $this->dossiersVisibles();
        $stats = $this->statistiques();
        return view('maitre_ouvrage.chef.dashboard', compact('dossiers', 'stats'));
    }

    /**
     * Dashboard pour le collaborateur MOA
     */
    public function collaborateurDashboard()
    {
        $dossiers = $this->dossiersVisibles();
        $stats = $this->statistiques();
        return view('maitre_ouvrage.collaborateur.dashboard', compact('dossiers', 'stats'));
    }

    /**
     * Documents archivés visibles par ce Maître d'Ouvrage : consultation seule.
     */
    public function archives()
    {
        $dossiers = $this->baseQuery()
            ->where('statut', 'archive')
            ->with(['documentType', 'structureEmettrice', 'lot'])
            ->latest()
            ->paginate(15);

        return view('maitre_ouvrage.archives', compact('dossiers'));
    }

    /**
     * Liste des projets suivis
     */
    public function projets()
    {
        return view('maitre_ouvrage.projets');
    }

    /**
     * Suivi des chantiers
     */
    public function chantiers()
    {
        return view('maitre_ouvrage.chantiers');
    }

    /**
     * Rapport d'avancement
     */
    public function rapports()
    {
        return view('maitre_ouvrage.rapports');
    }
}
