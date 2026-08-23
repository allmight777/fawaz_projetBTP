<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dossier;
use App\Models\DocumentAssignment;
use App\Models\User;
use App\Notifications\DocumentArchive;
use Illuminate\Support\Facades\Auth;

class ArchivageController extends Controller
{
    // Liste des dossiers validés (archivables ou déjà archivés) pour le chef de mission
    public function index()
    {
        $dossiers = Dossier::whereIn('statut', ['valide', 'archive'])
            ->with(['documentType', 'structureEmettrice', 'lot', 'creePar', 'versions'])
            ->latest()
            ->paginate(15);

        return view('cheflot.archives', compact('dossiers'));
    }

    public function archiver(Dossier $dossier)
    {
        abort_unless(
            Auth::user()->role === 'CHEF LOT' || $dossier->cree_par === Auth::id(),
            403,
            'Seul le Bureau de Contrôle ou l\'émetteur du dossier peut archiver ce document.'
        );

        if (!in_array($dossier->statut, ['valide', 'transmis'], true)) {
            return redirect()->back()->with('error', 'Seuls les documents validés ou transmis peuvent être archivés.');
        }

        $dossier->update(['statut' => 'archive']);

        AuditLog::enregistrer('ARCHIVAGE', $dossier->id);

        foreach ($this->partiesPrenantes($dossier) as $utilisateur) {
            $utilisateur->notify(new DocumentArchive($dossier));
        }

        return redirect()->route('dossiers.show', $dossier)->with('success', 'Document archivé.');
    }

    /**
     * Toutes les parties prenantes à notifier lors de l'archivage : l'émetteur,
     * les contrôleurs qui ont été affectés à une version du dossier, et les
     * utilisateurs Maître d'Ouvrage du même lot.
     */
    private function partiesPrenantes(Dossier $dossier)
    {
        $controleurIds = DocumentAssignment::whereIn('document_version_id', $dossier->versions->pluck('id'))
            ->pluck('controleur_id');

        $utilisateurs = User::where('actif', true)
            ->where(function ($query) use ($dossier, $controleurIds) {
                $query->where('id', $dossier->cree_par)
                    ->orWhereIn('id', $controleurIds);

                if ($dossier->lot_id) {
                    $query->orWhere(function ($q) use ($dossier) {
                        $q->where('lot_id', $dossier->lot_id)
                            ->whereHas('structure', fn ($sq) => $sq->where('type', 'maitre_ouvrage'));
                    });
                }
            })
            ->get();

        return $utilisateurs->unique('id');
    }
}
