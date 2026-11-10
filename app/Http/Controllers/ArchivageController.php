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
    // Archives de l'utilisateur, triées par structure émettrice (voir Dossier::scopeArchivesPour pour les règles de visibilité).
    public function index()
    {
        $user = Auth::user();

        $dossiers = Dossier::archivesPour($user)
            ->with(['documentType', 'structureEmettrice', 'lot', 'creePar', 'versions', 'circuitVisas'])
            ->leftJoin('structures', 'structures.id', '=', 'dossiers.structure_emettrice_id')
            ->select('dossiers.*')
            ->orderBy('structures.nom')
            ->latest('dossiers.updated_at')
            ->paginate(200);

        return view('dossiers.archives', compact('dossiers'));
    }

    public function archiver(Dossier $dossier)
    {
        $user = Auth::user();
        abort_if($dossier->isArchived(), 403, 'Ce document est déjà archivé.');

        abort_unless($dossier->droitArchiver($user), 403, 'Vous ne pouvez pas archiver ce document.');

        if (!$dossier->archivablePar($user)) {
            return redirect()->back()->with('error', $dossier->statut === 'valide'
                ? 'Ce document ne peut pas encore être archivé : toutes les structures sollicitées doivent avoir apposé leur visa et leur cachet.'
                : 'Seuls les documents transmis, ou validés avec tous les visas, peuvent être archivés.');
        }

        $dossier->update(['statut' => 'archive']);

        AuditLog::enregistrer('ARCHIVAGE', $dossier->id, ['par' => $user->id]);

        foreach ($this->partiesPrenantes($dossier) as $utilisateur) {
            $utilisateur->notify(new DocumentArchive($dossier));
        }

        return redirect()->route('archives.index')->with('success', 'Document archivé : il a quitté « Mes documents » et se trouve maintenant dans vos archives.');
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
                    ->orWhereIn('id', $controleurIds)
                    // Ceux à qui le document a été transmis (personnellement ou via leur structure) le retrouvent dans leurs archives.
                    ->orWhereIn('id', \App\Models\TransmissionDestinataire::whereIn('document_transmission_id', $dossier->transmissions()->pluck('id'))->whereNotNull('user_id')->pluck('user_id'))
                    ->orWhereIn('structure_id', \App\Models\TransmissionDestinataire::whereIn('document_transmission_id', $dossier->transmissions()->pluck('id'))->whereNotNull('structure_id')->pluck('structure_id'));

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
