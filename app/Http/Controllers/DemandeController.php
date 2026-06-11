<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\Lot;
use App\Models\HistoriqueDemande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DemandeController extends Controller
{
    public function create()
    {
        $lots = Lot::where('actif', true)->get();
        return view('cheflot.demandes.create', compact('lots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entreprise' => 'required|string|max:255',
            'lot_id' => 'nullable|exists:lots,id',
            'type_document' => 'required|string|in:Plan,Rapport,Contrat,Devis,Facture,Autre',
            'titre_document' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priorite' => 'required|in:Basse,Moyenne,Haute,Urgente',
            'fichier_url' => 'nullable|url',
            'fichier' => 'nullable|file|max:10240',
        ]);

        $fichierChemin = null;
        $fichierNom = null;
        $fichierUrl = $request->fichier_url;

        if ($request->hasFile('fichier')) {
            $file = $request->file('fichier');
            $fichierNom = $file->getClientOriginalName();
            $fichierChemin = $file->store('documents/' . date('Y/m'), 'public');
            $fichierUrl = Storage::url($fichierChemin);
        }

        $demande = Demande::create([
            'numero_demande' => $this->genererNumeroDemande(),
            'date_soumission' => now(),
            'soumis_par' => Auth::id(),
            'entreprise' => $request->entreprise,
            'lot_id' => $request->lot_id,
            'type_document' => $request->type_document,
            'titre_document' => $request->titre_document,
            'description' => $request->description,
            'version' => 'V1',
            'version_number' => 1,
            'priorite' => $request->priorite,
            'fichier_url' => $fichierUrl,
            'fichier_chemin' => $fichierChemin,
            'fichier_nom' => $fichierNom,
            'statut' => 'EN ATTENTE',
        ]);

        // CRÉATION DE L'HISTORIQUE
        HistoriqueDemande::create([
            'demande_id' => $demande->id,
            'user_id' => Auth::id(),
            'action' => 'CREATION',
            'details' => 'Demande créée : ' . $request->titre_document,
            'ancien_statut' => null,
            'nouveau_statut' => 'EN ATTENTE',
            'ancienne_version' => null,
            'nouvelle_version' => 'V1',
        ]);

        return redirect()->route('cheflot.demandes.show', $demande->id)
            ->with('success', 'Demande créée avec succès.');
    }

    public function telechargerFichier(Demande $demande)
    {
        if ($demande->fichier_chemin && Storage::disk('public')->exists($demande->fichier_chemin)) {
            return Storage::disk('public')->download($demande->fichier_chemin, $demande->fichier_nom ?? 'document');
        }

        return redirect()->back()->with('error', 'Fichier non trouvé.');
    }

    private function genererNumeroDemande()
    {
        $year = date('Y');
        $lastDemande = Demande::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastDemande) {
            $lastNum = intval(substr($lastDemande->numero_demande, -4));
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return 'DEM-' . $year . '-' . $newNum;
    }
}
