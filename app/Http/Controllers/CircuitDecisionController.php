<?php

namespace App\Http\Controllers;

use App\Mail\DocumentNeedsCorrectionMail;
use App\Models\CircuitEvenement;
use App\Models\DocumentVersion;
use App\Models\Dossier;
use App\Models\User;
use App\Notifications\Alerte;
use App\Services\CircuitVisaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Modifier une décision déjà rendue, rendre un document applicable, liste des documents applicables. */
class CircuitDecisionController extends Controller
{
    public function modifierDecision(Request $request, DocumentVersion $documentVersion, CircuitVisaService $circuit)
    {
        $user = Auth::user();
        $request->validate(
            ['motif' => 'required|string|min:5|max:2000'],
            ['motif.required' => 'Indiquez pourquoi vous revenez sur votre décision.', 'motif.min' => 'Le motif est trop court.']
        );

        abort_unless($circuit->peutModifierDecision($user, $documentVersion), 403, 'Vous ne pouvez pas modifier cette décision.');

        $dossier = $documentVersion->dossier;
        $decision = DB::transaction(fn () => $circuit->modifierDecision($documentVersion, $user, $request->motif));

        $destinataires = collect([$dossier->creePar])->filter();
        $chefEntreprise = User::where('structure_id', $dossier->structure_emettrice_id)
            ->where('categorie_role', User::CATEGORIE_RESPONSABLE)->where('actif', true)->first();
        if ($chefEntreprise) {
            $destinataires->push($chefEntreprise);
        }

        foreach ($destinataires->unique('id') as $destinataire) {
            Alerte::envoyer($destinataire, 'Décision modifiée, correction demandée : ' . $dossier->titre, route('dossiers.show', $dossier));
            try {
                Mail::to($destinataire->email)->send(new DocumentNeedsCorrectionMail($documentVersion, $dossier, $user, $decision));
            } catch (\Throwable $e) {
                Log::error('Erreur envoi email décision modifiée : ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Décision modifiée : le document repasse « À corriger » chez l\'émetteur, qui a été prévenu.');
    }

    /** Icône « Partager » : l'émetteur ajoute une structure oubliée à un circuit en cours. */
    public function partager(Request $request, Dossier $dossier, CircuitVisaService $circuit)
    {
        $request->validate([
            'structures' => 'required|array|min:1',
            'structures.*' => 'in:' . implode(',', \App\Support\Espaces::TYPES_VISA),
            'commentaire' => 'nullable|string|max:2000',
        ], ['structures.required' => 'Choisissez au moins une structure à ajouter.']);

        $ajoutees = $circuit->ajouterStructures($dossier, Auth::user(), $request->structures, $request->commentaire);

        return back()->with('success', 'Document partagé avec : ' . implode(', ', array_map([\App\Support\Espaces::class, 'nom'], $ajoutees)) . '. Le circuit en cours continue sans changement.');
    }

    public function rendreApplicable(Dossier $dossier)
    {
        $user = Auth::user();
        abort_unless($dossier->applicablePar($user), 403, 'Ce document ne peut pas être rendu applicable (tous les visas doivent être obtenus, et seul le chef de l\'entreprise émettrice peut le faire).');

        $dossier->update(['applicable_le' => now(), 'applicable_par' => $user->id]);
        CircuitEvenement::noter($dossier, 'rendu_applicable', $user, null, $dossier->derniereVersion());

        // L'émetteur et les responsables des structures visantes sont prévenus (alerte + email).
        $destinataires = User::whereHas('structure', fn ($q) => $q->whereIn('type', \App\Support\Espaces::TYPES_VISA))
            ->where('categorie_role', User::CATEGORIE_RESPONSABLE)->where('actif', true)->get();
        if ($dossier->creePar) {
            $destinataires->push($dossier->creePar);
        }
        foreach ($destinataires->unique('id')->reject(fn ($u) => $u->id === $user->id) as $destinataire) {
            Alerte::envoyer($destinataire, 'Document applicable : ' . $dossier->titre, route('dossiers.show', $dossier));
            try {
                $destinataire->notify(new \App\Notifications\DocumentApplicable($dossier));
            } catch (\Throwable $e) {
                Log::error('Erreur envoi email document applicable : ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Le document est maintenant applicable : il sert de référence à toutes les structures.');
    }

    public function applicables()
    {
        $user = Auth::user();
        abort_unless($user->structure_id, 403);

        $dossiers = Dossier::applicables()
            ->with(['documentType', 'structureEmettrice', 'circuitVisas'])
            ->latest('applicable_le')
            ->get();

        return view('dossiers.applicables', compact('dossiers'));
    }
}
