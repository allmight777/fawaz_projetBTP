<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentVersionRequest;
use App\Http\Requests\StoreDossierRequest;
use App\Models\AuditLog;
use App\Models\Dossier;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\DocumentAssignment;
use App\Models\Lot;
use App\Models\Structure;
use App\Models\User;
use App\Notifications\DocumentRecu;
use App\Notifications\NouvelleVersionImportee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DossierController extends Controller
{
    public function index()
    {
        $query = Dossier::visiblePar(Auth::user())
            ->with(['documentType', 'structureEmettrice', 'lot', 'creePar']);

        if (request()->filled('statut')) {
            $query->where('statut', request('statut'));
        }
        if (request()->filled('structure_emettrice_id')) {
            $query->where('structure_emettrice_id', request('structure_emettrice_id'));
        }
        if (request()->filled('document_type_id')) {
            $query->where('document_type_id', request('document_type_id'));
        }

        $dossiers = $query->latest()->paginate(15)->withQueryString();
        $structures = Structure::actives()->get();
        $documentTypes = DocumentType::actifs()->get();

        return view('dossiers.index', compact('dossiers', 'structures', 'documentTypes'));
    }

    public function create()
    {
        $documentTypes = DocumentType::actifs()->with('checklistItems')->get();
        $structures = Structure::actives()->get();
        $lots = Lot::where('actif', true)->get();

        return view('dossiers.create', compact('documentTypes', 'structures', 'lots'));
    }

    public function store(StoreDossierRequest $request)
    {
        $documentType = DocumentType::findOrFail($request->document_type_id);

        $fichierChemin = null;
        $fichierNom = null;
        $fichierUrl = $request->fichier_url;

        if ($request->hasFile('fichier')) {
            $file = $request->file('fichier');
            $fichierNom = $file->getClientOriginalName();
            $fichierChemin = $file->store('dossiers/' . date('Y/m'), 'public');
            $fichierUrl = Storage::url($fichierChemin);
        }

        $modeTraitement = $documentType->mode_traitement;

        $dossier = Dossier::create([
            'identifiant' => Dossier::genererIdentifiant($documentType),
            'document_type_id' => $documentType->id,
            // La structure émettrice est toujours celle de l'utilisateur connecté,
            // jamais une valeur choisie librement dans le formulaire.
            'structure_emettrice_id' => Auth::user()->structure_id,
            'lot_id' => $request->lot_id,
            'titre' => $request->titre,
            'description' => $request->description,
            'mode_traitement' => $modeTraitement,
            'statut' => $modeTraitement === 'simple' ? 'transmis' : 'soumis',
            'version_courante' => 1,
            'cree_par' => Auth::id(),
        ]);

        DocumentVersion::create([
            'dossier_id' => $dossier->id,
            'numero_version' => 1,
            'fichier_nom' => $fichierNom,
            'fichier_chemin' => $fichierChemin,
            'fichier_url' => $fichierUrl,
            'importe_par' => Auth::id(),
            'date_import' => now(),
            'statut' => $modeTraitement === 'simple' ? 'soumis' : 'en_attente_checklist',
        ]);

        AuditLog::enregistrer('IMPORT', $dossier->id, ['identifiant' => $dossier->identifiant]);

        if ($modeTraitement === 'simple') {
            return redirect()->route('dossiers.transmettre.form', $dossier)
                ->with('success', 'Document importé. Choisissez les destinataires pour le transmettre.');
        }

        if ($documentType->checklistItems()->count() > 0) {
            return redirect()->route('document-versions.checklist', $dossier->derniereVersion)
                ->with('success', 'Document importé. Complétez la checklist avant soumission.');
        }

        foreach (User::where('role', 'CHEF LOT')->where('actif', true)->get() as $responsable) {
            $responsable->notify(new DocumentRecu($dossier));
        }

        return redirect()->route('dossiers.show', $dossier)
            ->with('success', 'Document importé et soumis au Bureau de Contrôle.');
    }

    public function show(Dossier $dossier)
    {
        $this->authorize('view', $dossier);

        $dossier->load([
            'documentType.checklistItems',
            'structureEmettrice',
            'lot',
            'creePar',
            'versions.importePar',
            'versions.checklistReponses.checklistItem',
            'versions.affectations.controleur',
            'versions.affectations.observations.auteur',
            'versions.decisions.validateur',
            'transmissions.emetteur',
            'transmissions.destinataires.structure',
            'transmissions.destinataires.user',
        ]);

        // La section observations/décisions (travail interne du Bureau de Contrôle) n'est
        // visible que pour le Chef de Mission/Chef Lot ou un contrôleur affecté à ce dossier —
        // pas pour n'importe quel utilisateur authentifié ni même l'émetteur.
        $peutVoirAnalyse = Auth::user()->role === 'CHEF LOT'
            || DocumentAssignment::whereIn('document_version_id', $dossier->versions->pluck('id'))
                ->where('controleur_id', Auth::id())
                ->exists();

        // Trace la première consultation par un destinataire réel de la transmission
        // (droits d'accès déjà ouverts par la transmission, on ne fait que la constater).
        foreach ($dossier->transmissions as $transmission) {
            foreach ($transmission->destinataires as $destinataire) {
                $estDestinataire = $destinataire->user_id === Auth::id()
                    || ($destinataire->structure_id !== null && $destinataire->structure_id === Auth::user()->structure_id);

                if ($estDestinataire && !$destinataire->consulte_le) {
                    $destinataire->marquerConsulte();
                    AuditLog::enregistrer('CONSULTATION', $dossier->id, ['transmission_id' => $transmission->id]);
                }
            }
        }

        return view('dossiers.show', compact('dossier', 'peutVoirAnalyse'));
    }

    public function telecharger(Dossier $dossier, DocumentVersion $version)
    {
        $this->authorize('view', $dossier);

        if ($version->dossier_id !== $dossier->id) {
            abort(404);
        }

        if ($version->chemin_a_servir && Storage::disk('public')->exists($version->chemin_a_servir)) {
            return Storage::disk('public')->download($version->chemin_a_servir, $version->nom_affiche);
        }

        return redirect()->back()->with('error', 'Fichier non trouvé.');
    }

    public function nouvelleVersionForm(Dossier $dossier)
    {
        abort_unless($dossier->cree_par === Auth::id(), 403, 'Seul l\'émetteur du dossier peut en importer une nouvelle version.');

        if ($dossier->statut !== 'a_corriger') {
            abort(403, 'Ce dossier ne peut pas recevoir de nouvelle version pour le moment.');
        }

        return view('dossiers.nouvelle-version', compact('dossier'));
    }

    public function storeVersion(StoreDocumentVersionRequest $request, Dossier $dossier)
    {
        abort_unless($dossier->cree_par === Auth::id(), 403, 'Seul l\'émetteur du dossier peut en importer une nouvelle version.');

        if ($dossier->statut !== 'a_corriger') {
            abort(403, 'Ce dossier ne peut pas recevoir de nouvelle version pour le moment.');
        }

        $fichierChemin = null;
        $fichierNom = null;
        $fichierUrl = $request->fichier_url;

        if ($request->hasFile('fichier')) {
            $file = $request->file('fichier');
            $fichierNom = $file->getClientOriginalName();
            $fichierChemin = $file->store('dossiers/' . date('Y/m'), 'public');
            $fichierUrl = Storage::url($fichierChemin);
        }

        $nouveauNumero = $dossier->version_courante + 1;

        DocumentVersion::create([
            'dossier_id' => $dossier->id,
            'numero_version' => $nouveauNumero,
            'fichier_nom' => $fichierNom,
            'fichier_chemin' => $fichierChemin,
            'fichier_url' => $fichierUrl,
            'importe_par' => Auth::id(),
            'date_import' => now(),
            'statut' => 'en_attente_checklist',
            'commentaire' => $request->commentaire,
        ]);

        $dossier->update([
            'version_courante' => $nouveauNumero,
            'statut' => 'soumis',
        ]);

        AuditLog::enregistrer('CHANGEMENT_VERSION', $dossier->id, ['nouvelle_version' => $nouveauNumero]);

        foreach (User::where('role', 'CHEF LOT')->where('actif', true)->get() as $responsable) {
            $responsable->notify(new NouvelleVersionImportee($dossier->derniereVersion));
        }

        return redirect()->route('document-versions.checklist', $dossier->derniereVersion)
            ->with('success', 'Nouvelle version importée (V' . $nouveauNumero . '). Complétez la checklist avant soumission.');
    }
}
