<?php

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\DocumentType;
use App\Models\Dossier;
use App\Mail\DocumentForValidationMail;
use App\Models\DocumentVersion;
use App\Models\DocumentTransmission;
use App\Models\TransmissionDestinataire;
use App\Models\ChecklistItem;
use App\Models\User;
use App\Models\Structure;
use App\Mail\DocumentTransmittedMail;
use App\Mail\DocumentForControlMail;
use Illuminate\Support\Facades\Storage;

class EntrepriseController extends Controller
{
    /** Tableau de bord du chef : tous les documents de son entreprise (pas seulement les siens). */
    public function chefDashboard()
    {
        $user = Auth::user();
        $base = fn () => Dossier::where('structure_emettrice_id', $user->structure_id);

        $stats = \App\Support\StatsDossiers::compter($base);
        [$months, $projetsData] = \App\Support\StatsDossiers::parMois($base);

        $documentsSoumis = $stats['au_visa'];
        $documentsValides = $stats['valides'];
        $documentsEnAttente = $stats['en_attente'];
        $documentsATransferer = $stats['a_transferer'];
        $attenteAnciennes = $stats['en_attente_anciens'];
        $nonConformites = $stats['a_corriger'];
        $informatifs = $stats['informatifs'];
        $projetsEnCours = $stats['en_attente'] + $stats['en_analyse'];
        $projetsTermines = $stats['valides'];

        $repartition = [$stats['valides'], $stats['en_attente'], $stats['en_analyse'], $stats['a_corriger']];

        $documentsRecents = $base()->with(['documentType', 'lot', 'circuitVisas'])->latest()->limit(5)->get();

        return view('entreprise.chef.dashboard', compact(
            'user',
            'projetsEnCours',
            'projetsTermines',
            'documentsSoumis',
            'documentsValides',
            'documentsEnAttente',
            'documentsATransferer',
            'informatifs',
            'nonConformites',
            'documentsRecents',
            'months',
            'projetsData',
            'repartition',
            'attenteAnciennes'
        ));
    }

    /** Tableau de bord du collaborateur : ses propres documents. */
    public function collaborateurDashboard()
    {
        $user = Auth::user();
        $base = fn () => Dossier::where('cree_par', $user->id);

        $stats = \App\Support\StatsDossiers::compter($base);

        $mesDocuments = $stats['total'];
        $documentsEnAttente = $stats['en_attente'];
        $documentsEnAnalyse = $stats['en_analyse'];
        $documentsValides = $stats['valides'];
        $documentsACorriger = $stats['a_corriger'];
        $documentsATransferer = $stats['a_transferer'];
        $informatifs = $stats['informatifs'];

        $mesDocumentsRecents = $base()->with(['documentType', 'circuitVisas'])->latest()->limit(5)->get();

        return view('entreprise.collaborateur.dashboard', compact(
            'user',
            'mesDocuments',
            'documentsEnAttente',
            'documentsEnAnalyse',
            'documentsValides',
            'documentsACorriger',
            'documentsATransferer',
            'informatifs',
            'mesDocumentsRecents'
        ));
    }

    public function documents()
    {
        $user = Auth::user();
        $isChef = $user->peutViser();

        // Un document archivé quitte « Mes documents » : il est dans « Archives ».
        $documents = DocumentVersion::whereHas('dossier', function($query) use ($user) {
            $query->where('cree_par', $user->id)->where('statut', '!=', 'archive');
        })->with(['dossier', 'dossier.documentType', 'dossier.circuitVisas', 'circuitVisas'])
        ->orderBy('created_at', 'desc')
        ->paginate(200);

        $documentTypes = DocumentType::where('actif', true)->get();
        $users = User::where('structure_id', $user->structure_id)->get();
        $structures = Structure::where('actif', true)->get();

        $roles = [
            'conducteur_travaux' => 'Conducteur de travaux',
            'tout_le_monde' => 'Tout le monde',
            'collaborateur' => 'Collaborateur',
            'specifique' => 'Spécifique (individus)',
        ];

        return view('entreprise.documents', compact(
            'documents',
            'documentTypes',
            'users',
            'structures',
            'roles',
            'isChef'
        ));
    }

    public function dossiers()
    {
        $user = Auth::user();
        // Les documents de sa structure, et ceux qu'on lui a transmis (qu'il peut à son tour transmettre).
        $dossiers = Dossier::where('statut', '!=', 'archive')
            ->where(fn ($q) => $q->where('structure_emettrice_id', $user->structure_id)->orWhere(fn ($t) => $t->transmisA($user)))
            ->with(['documentType', 'versions', 'circuitVisas'])
            ->orderBy('created_at', 'desc')
            ->paginate(200);

        return view('entreprise.dossiers', compact('dossiers'));
    }

    /** Ancienne adresse : les archives de tous les espaces sont sur une page commune. */
    public function archives()
    {
        return redirect()->route('archives.index');
    }

    public function createDossier()
    {
        $documentTypes = DocumentType::where('actif', true)->get();
        return view('entreprise.dossiers-create', compact('documentTypes'));
    }

    public function storeDossier(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'document_type_id' => 'required|exists:document_types,id',
            'description' => 'nullable|string',
            'fichier' => 'required|file|max:10240',
        ]);

        $user = Auth::user();
        $documentType = DocumentType::findOrFail($request->document_type_id);

        $dossier = Dossier::creerAvecIdentifiant($documentType, [
            'titre' => $request->titre,
            'description' => $request->description,
            'document_type_id' => $documentType->id,
            'structure_emettrice_id' => $user->structure_id,
            'mode_traitement' => $documentType->mode_traitement,
            'version_courante' => 1,
            'cree_par' => $user->id,
            'statut' => 'brouillon',
        ]);

        $file = $request->file('fichier');
        $path = $file->store('documents/entreprise/' . $dossier->id, 'public');

        DocumentVersion::create([
            'dossier_id' => $dossier->id,
            'numero_version' => 1,
            'fichier_nom' => $file->getClientOriginalName(),
            'fichier_chemin' => $path,
            'fichier_url' => Storage::url($path),
            'importe_par' => $user->id,
            'date_import' => now(),
            'statut' => 'en_attente_checklist',
        ]);

        return redirect()->eroute('entreprise.dossiers')
            ->with('success', 'Dossier créé avec succès.');
    }

    public function showDossier($id)
    {
        $user = Auth::user();
        $dossier = Dossier::with(['documentType', 'versions', 'versions.importePar', 'circuitVisas.decideur'])
            ->visiblePar($user)
            ->findOrFail($id);

        return view('entreprise.dossiers-show', compact('dossier'));
    }

    public function transmettre(Request $request)
    {
        Log::info('=== DEBUT transmettre() ===', [
            'has_files' => $request->hasFile('fichiers'),
            'all_keys' => array_keys($request->all()),
            'files_count' => $request->hasFile('fichiers') ? count($request->file('fichiers')) : 0,
        ]);

        try {
            $validated = $request->validate([
                'fichiers' => 'required|array|min:1|max:5',
                'fichiers.*' => 'required|file',
                'document_type_id' => 'required|array|min:1',
                'document_type_id.*' => 'exists:document_types,id',
                'titre' => 'required|array|min:1',
                'titre.*' => 'required|string|max:255',
                'description' => 'nullable|array',
                'description.*' => 'nullable|string',
                'mode' => 'required|array|min:1',
                'mode.*' => 'required|in:simple,validation',
                'destinataires' => 'nullable|array',
                'destinataires.*' => 'nullable|array',
                'destinataires.*.*' => 'exists:users,id',
                'circuit' => 'nullable|array',
                'circuit.*' => 'nullable|array',
                'circuit.*.*' => 'in:' . implode(',', \App\Support\Espaces::TYPES_VISA),
                'commentaire' => 'nullable|array',
                'commentaire.*' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('VALIDATION ECHOUEE', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        }

        // Vérification de la taille totale (7 Mo max cumulés)
        $totalSize = 0;
        foreach ($request->file('fichiers') as $file) {
            $totalSize += $file->getSize();
        }

        $maxTotalSize = 7 * 1024 * 1024;

        if ($totalSize > $maxTotalSize) {
            Log::warning('Taille totale dépassée', ['total_size' => $totalSize, 'max' => $maxTotalSize]);
            return back()->withErrors([
                'fichiers' => 'La taille totale des documents (' . round($totalSize / 1024 / 1024, 2) . ' Mo) dépasse la limite de 7 Mo.'
            ])->withInput();
        }

        Log::info('Validation OK', ['validated_keys' => array_keys($validated), 'total_size' => $totalSize]);

        $user = Auth::user();
        $nbDocuments = 0;
        $nbValidation = 0;
        $nbInformatif = 0;

        // Un responsable envoie directement chez les destinataires, en visant chaque document
        // (cachet + signature). Un collaborateur, lui, soumet à son chef, qui visera au transfert.
        $estChef = $user->peutViser();
        $visaService = app(\App\Services\VisaService::class);
        $circuitService = app(\App\Services\CircuitVisaService::class);

        if ($estChef) {
            $request->validate(\App\Services\VisaService::regles($user), \App\Services\VisaService::messages());
        }
        $visasCrees = 0;

        // Validation manuelle destinataires
        foreach ($request->file('fichiers') as $index => $file) {
            $mode = $request->mode[$index];
            $destinatairesDoc = $request->destinataires[$index] ?? [];

            if ($mode === 'validation' && empty(\App\Services\CircuitVisaService::normaliser($request->circuit[$index] ?? []))) {
                return back()->withErrors([
                    'circuit' => "Document " . ($index + 1) . " : veuillez choisir au moins une structure dans le circuit de visa."
                ])->withInput();
            }

            if ($mode === 'simple' && empty($destinatairesDoc)) {
                Log::warning("Document $index sans destinataire en mode simple");
                return back()->withErrors([
                    'destinataires' => "Document " . ($index + 1) . " : veuillez sélectionner au moins un destinataire pour une transmission informative."
                ])->withInput();
            }
        }

        try {
            DB::transaction(function () use ($request, $user, $estChef, $visaService, $circuitService, &$visasCrees, &$nbDocuments, &$nbValidation, &$nbInformatif) {
                foreach ($request->file('fichiers') as $index => $file) {
                    Log::info("Traitement document index=$index", [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'is_valid' => $file->isValid(),
                    ]);

                    $mode = $request->mode[$index];
                    $destinatairesDoc = $request->destinataires[$index] ?? [];
                    $commentaireDoc = $request->commentaire[$index] ?? null;
                    $documentTypeId = $request->document_type_id[$index];
                    $typesVisa = \App\Services\CircuitVisaService::normaliser($request->circuit[$index] ?? []);

                    // ✅ FIX : Stockage du fichier avant toute autre opération
                    $path = $file->store('documents/entreprise', 'public');
                    Log::info("Fichier stocké index=$index", ['path' => $path]);

                    if (!$path) {
                        throw new \Exception("Échec du stockage du fichier pour le document " . ($index + 1));
                    }

                    $documentType = DocumentType::findOrFail($documentTypeId);

                    $dossier = Dossier::creerAvecIdentifiant($documentType, [
                        'titre' => $request->titre[$index] ?? $file->getClientOriginalName(),
                        'description' => $request->description[$index] ?? null,
                        'document_type_id' => $documentTypeId,
                        'structure_emettrice_id' => $user->structure_id,
                        'mode_traitement' => $mode,
                        'version_courante' => 1,
                        'cree_par' => $user->id,
                        'statut' => 'brouillon',
                    ]);

                    Log::info("Dossier créé index=$index", ['dossier_id' => $dossier->id]);

                    // ✅ FIX : Le statut est TOUJOURS 'soumis' à la création
                    // La checklist est déjà validée par l'utilisateur dans le formulaire
                    // Le statut 'en_attente_checklist' ne sera plus utilisé ici
                    $version = DocumentVersion::create([
                        'dossier_id' => $dossier->id,
                        'numero_version' => 1,
                        'fichier_nom' => $file->getClientOriginalName(),
                        'fichier_chemin' => $path,
                        'fichier_url' => Storage::url($path),
                        'importe_par' => $user->id,
                        'date_import' => now(),
                        'statut' => 'soumis', // TOUJOURS soumis
                        'commentaire' => $commentaireDoc,
                    ]);

                    Log::info("Version créée index=$index", ['version_id' => $version->id, 'statut' => 'soumis']);

                    if ($estChef) {
                        // Visa du responsable : enregistré sur le document avant tout envoi
                        $visaService->enregistrer($request, $user, $version, 'envoi', $mode === 'validation' ? implode(',', $typesVisa) : 'destinataires', $visasCrees === 0);
                        $visasCrees++;
                        app(\App\Services\DocumentStampingService::class)->viser($version);
                    }

                    if ($mode === 'validation' && $estChef) {
                        // Circuit de visa : le responsable envoie directement, et en même temps, à toutes
                        // les structures choisies ; chacune vise à son niveau.
                        $transmission = $circuitService->ouvrir($dossier, $version, $user, $typesVisa, $commentaireDoc);

                        foreach ($destinatairesDoc as $destinataireId) {
                            TransmissionDestinataire::create([
                                'document_transmission_id' => $transmission->id,
                                'user_id' => $destinataireId,
                            ]);
                        }

                        $nbValidation++;
                    } elseif ($mode === 'validation') {
                        // Le document ne part JAMAIS directement à un bureau externe.
                        // Il va toujours d'abord au chef de sa propre entreprise, qui décidera
                        // ensuite (via "Documents à transférer") vers quelle structure l'envoyer.
                        $chefEntreprise = User::where('structure_id', $user->structure_id)
                            ->where('categorie_role', 'responsable_organisme')
                            ->first();

                        $transmission = DocumentTransmission::create([
                            'dossier_id' => $dossier->id,
                            'document_version_id' => $version->id,
                            'emetteur_id' => $user->id,
                            'mode' => $mode,
                            'commentaire' => $commentaireDoc,
                            'date_transmission' => now(),
                        ]);

                        foreach ($destinatairesDoc as $destinataireId) {
                            TransmissionDestinataire::create([
                                'document_transmission_id' => $transmission->id,
                                'user_id' => $destinataireId,
                            ]);
                        }

                        if ($chefEntreprise) {
                            TransmissionDestinataire::create([
                                'document_transmission_id' => $transmission->id,
                                'user_id' => $chefEntreprise->id,
                            ]);

                            // Notifie le chef seulement si ce n'est pas lui-même qui a soumis
                            if ($chefEntreprise->id !== $user->id) {
                                try {
                                    Mail::to($chefEntreprise->email)->send(new DocumentForValidationMail($version, $dossier, $user));
                                    Log::info('Email de validation envoyé au chef entreprise ' . $chefEntreprise->email);
                                } catch (\Exception $e) {
                                    Log::error('Erreur envoi email vers chef entreprise: ' . $e->getMessage());
                                }
                                \App\Notifications\Alerte::envoyer($chefEntreprise, $user->full_name . ' a soumis un document à transférer : ' . $dossier->titre, eroute('entreprise.documents.a-transferer'));
                            }
                        }

                        $dossier->update([
                            'statut' => 'en_cours',
                            'necessite_transfert_chef' => true,
                            // Structures souhaitées par le collaborateur : proposées au chef, qui décide du circuit.
                            'structure_cible' => implode(',', array_map([\App\Support\Espaces::class, 'sigle'], $typesVisa)),
                        ]);

                        $nbValidation++;
                    } else {
                        $transmission = DocumentTransmission::create([
                            'dossier_id' => $dossier->id,
                            'document_version_id' => $version->id,
                            'emetteur_id' => $user->id,
                            'mode' => $mode,
                            'commentaire' => $commentaireDoc,
                            'date_transmission' => now(),
                        ]);

                        foreach ($destinatairesDoc as $destinataireId) {
                            $destinataire = User::find($destinataireId);
                            if ($destinataire) {
                                TransmissionDestinataire::create([
                                    'document_transmission_id' => $transmission->id,
                                    'user_id' => $destinataireId,
                                ]);
                                try {
                                    Mail::to($destinataire->email)->send(new DocumentTransmittedMail($version, $dossier, $user, 'simple'));
                                    Log::info('Email de transmission envoyé à ' . $destinataire->email);
                                } catch (\Exception $e) {
                                    Log::error('Erreur envoi email transmission: ' . $e->getMessage());
                                }
                                \App\Notifications\Alerte::envoyer($destinataire, 'Document reçu de ' . $user->full_name . ' : ' . $dossier->titre, route('dossiers.show', $dossier));
                            }
                        }

                        $dossier->update(['statut' => 'soumis']);
                        $nbInformatif++;
                    }

                    $nbDocuments++;
                    Log::info("Document $index traité avec succès");
                }
            });
        } catch (\Exception $e) {
            Log::error('ECHEC TRANSACTION transmettre()', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'error' => 'Erreur lors de la création des documents : ' . $e->getMessage()
            ])->withInput();
        }

        Log::info('=== FIN transmettre() SUCCES ===', ['nbDocuments' => $nbDocuments]);

        $message = "$nbDocuments document(s) transmis avec succès";
        if ($nbValidation > 0) {
            $message .= " ($nbValidation pour contrôle";
            $message .= $nbInformatif > 0 ? ", $nbInformatif informatif(s))" : ")";
        } elseif ($nbInformatif > 0) {
            $message .= " ($nbInformatif informatif(s))";
        }

        return redirect()->eroute('entreprise.documents')->with('success', $message);
    }

    public function getChecklist($dossierId)
    {
        $dossier = Dossier::with(['documentType.checklistItems'])->findOrFail($dossierId);

        $items = $dossier->documentType->checklistItems->map(function($item) {
            return [
                'id' => $item->id,
                'libelle' => $item->libelle,
                'obligatoire' => $item->obligatoire,
            ];
        });

        return response()->json($items);
    }

    public function telechargerDossier($id)
    {
        $user = Auth::user();
        $dossier = Dossier::visiblePar($user)->findOrFail($id);

        $version = $dossier->versions()->orderByDesc('numero_version')->first();

        if (!$version || !Storage::disk('public')->exists($version->chemin_a_servir)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        return Storage::disk('public')->download($version->chemin_a_servir, $version->fichier_nom);
    }

    public function destroyDossier($id)
    {
        $user = Auth::user();
        $dossier = Dossier::where('cree_par', $user->id)->with('versions')->findOrFail($id);

        abort_if($dossier->isArchived(), 403, 'Ce document est archivé et ne peut plus être supprimé.');
        abort_if($dossier->aEteVise(), 403, 'Ce document a déjà été validé ou visé : il ne peut plus être supprimé.');

        foreach ($dossier->versions as $version) {
            if ($version->fichier_chemin && Storage::disk('public')->exists($version->fichier_chemin)) {
                Storage::disk('public')->delete($version->fichier_chemin);
            }
            $version->delete();
        }

        $dossier->transmissions()->delete();
        $dossier->delete();

        return redirect()->back()->with('success', 'Document supprimé avec succès.');
    }

    public function getChecklistByType($id)
    {
        $documentType = DocumentType::with('checklistItems')->findOrFail($id);

        $items = $documentType->checklistItems->map(function($item) {
            return [
                'id' => $item->id,
                'libelle' => $item->libelle,
                'obligatoire' => (bool) $item->obligatoire,
            ];
        });

        return response()->json($items);
    }

    public function telecharger($dossierId, $versionId)
    {
        $user = Auth::user();
        $dossier = Dossier::visiblePar($user)->findOrFail($dossierId);
        $version = $dossier->versions()->findOrFail($versionId);

        if ($version->chemin_a_servir && Storage::disk('public')->exists($version->chemin_a_servir)) {
            return Storage::disk('public')->download($version->chemin_a_servir, $version->fichier_nom);
        }

        return back()->with('error', 'Fichier introuvable.');
    }

    /**
     * Affiche le formulaire de correction pour un dossier en attente de correction
     */
  public function corrigerForm($id)
{
    $user = Auth::user();

    $dossier = Dossier::with([
            'documentType',
            'versions' => function ($q) {
                $q->orderByDesc('numero_version');
            },
            'versions.importePar',
            'versions.affectations.controleur',
            'versions.affectations.observations.auteur',
        ])
        ->where('cree_par', $user->id)
        ->findOrFail($id);

    if ($dossier->statut !== 'a_corriger') {
        return redirect()->eroute('entreprise.dossiers.show', $dossier->id)
            ->with('error', 'Ce dossier n\'est pas en attente de correction.');
    }

    $derniereVersion = $dossier->versions->first();
    $decision = null;

    if ($derniereVersion) {
        $decision = \App\Models\DocumentDecision::where('document_version_id', $derniereVersion->id)
            ->orderByDesc('created_at')
            ->first();
    }

    // Récupérer toutes les affectations + observations des collaborateurs du BC
    $affectations = $dossier->versions
        ->flatMap(fn($v) => $v->affectations)
        ->groupBy('controleur_id')
        ->map(function ($groupe) {
            // Privilégier l'affectation terminée, sinon la plus récente
            return $groupe->firstWhere('statut', 'termine')
                ?? $groupe->sortByDesc('created_at')->first();
        })
        ->values();

    // Toutes les observations de tous les collaborateurs sur ce dossier
    $observations = $dossier->versions
        ->flatMap(fn($v) => $v->affectations)
        ->flatMap(fn($a) => $a->observations)
        ->sortByDesc('created_at')
        ->values();

    // Pour chaque observation, on associe son auteur pour l'affichage
    return view('entreprise.dossiers-corriger', compact(
        'dossier',
        'decision',
        'derniereVersion',
        'affectations',
        'observations'
    ));
}

    /**
     * Enregistre la correction d'un dossier
     */
    public function storeCorrection(Request $request, $id)
    {
        $user = Auth::user();
        $dossier = Dossier::where('cree_par', $user->id)->findOrFail($id);

        if ($dossier->statut !== 'a_corriger') {
            return redirect()->eroute('entreprise.dossiers.show', $dossier->id)
                ->with('error', 'Ce dossier n\'est pas en attente de correction.');
        }

        $validated = $request->validate([
            'fichier' => 'required|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar',
            'commentaire' => 'nullable|string|max:2000',
        ], [
            'fichier.required' => 'Vous devez sélectionner un fichier corrigé.',
            'fichier.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
            'fichier.mimes' => 'Format de fichier non accepté.',
        ]);

        DB::transaction(function () use ($request, $user, $dossier) {
            $file = $request->file('fichier');
            $path = $file->store('documents/entreprise', 'public');

            $dernierNumero = $dossier->versions()->max('numero_version') ?? 0;

            $nouvelleVersion = DocumentVersion::create([
                'dossier_id' => $dossier->id,
                'numero_version' => $dernierNumero + 1,
                'fichier_nom' => $file->getClientOriginalName(),
                'fichier_chemin' => $path,
                'fichier_url' => Storage::url($path),
                'importe_par' => $user->id,
                'date_import' => now(),
                'statut' => 'soumis',
                'commentaire' => $request->commentaire,
            ]);

            // Retransmet automatiquement le nouveau fichier aux mêmes structures que le circuit de visa précédent
            // (le Bureau de Contrôle seul pour un ancien document).
            $circuitService = app(\App\Services\CircuitVisaService::class);
            $circuitService->rouvrir($dossier, $nouvelleVersion, $user, $request->commentaire);

            // Notifie aussi les collaborateurs qui avaient analysé une version précédente
            $collaborateurs = User::whereIn('id', \App\Models\DocumentAssignment::whereIn(
                    'document_version_id',
                    $dossier->versions()->where('id', '!=', $nouvelleVersion->id)->pluck('id')
                )->pluck('controleur_id'))
                ->whereNotNull('email')
                ->get();

            foreach ($collaborateurs as $collaborateur) {
                try {
                    Mail::to($collaborateur->email)->send(new \App\Mail\DocumentResubmittedMail($nouvelleVersion, $dossier, $user));
                    Log::info('Email de correction envoyé au collaborateur ' . $collaborateur->email);
                } catch (\Exception $e) {
                    Log::error('Erreur envoi email correction collaborateur: ' . $e->getMessage());
                }
            }

            $dossier->update(['statut' => 'en_cours']);

            Log::info('Correction soumise', [
                'dossier_id' => $dossier->id,
                'nouvelle_version' => $nouvelleVersion->numero_version,
            ]);
        });

        return redirect()->eroute('entreprise.dossiers.show', $dossier->id)
            ->with('success', 'Correction envoyée avec succès pour nouvelle validation.');
    }

    /**
     * Télécharge une version spécifique d'un dossier
     */
    public function telechargerVersion($dossierId, $versionId)
    {
        $user = Auth::user();
        $dossier = Dossier::visiblePar($user)->findOrFail($dossierId);
        $version = $dossier->versions()->findOrFail($versionId);

        if (!$version->chemin_a_servir || !Storage::disk('public')->exists($version->chemin_a_servir)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        return Storage::disk('public')->download($version->chemin_a_servir, $version->nom_affiche);
    }

    /**
     * Affiche la liste des documents à transférer par le chef d'entreprise
     */
    public function documentsATransferer()
    {
        $user = Auth::user();

        if (!$user->peutViser()) {
            abort(403, 'Accès réservé au chef d\'entreprise.');
        }

        $dossiers = Dossier::whereHas('creePar', function ($q) use ($user) {
                $q->where('structure_id', $user->structure_id);
            })
            ->where('necessite_transfert_chef', true)
            ->with(['documentType', 'versions', 'creePar'])
            ->orderByDesc('created_at')
            ->paginate(200);

        return view('entreprise.documents-a-transferer', compact('dossiers'));
    }

    /**
     * Ouvre le circuit de visa d'un dossier soumis par un collaborateur : le chef d'entreprise vise
     * le document puis l'envoie, en même temps, à toutes les structures dont le visa est demandé.
     */
    public function transfererDossier(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->peutViser()) {
            abort(403);
        }

        $dossier = Dossier::whereHas('creePar', function ($q) use ($user) {
                $q->where('structure_id', $user->structure_id);
            })
            ->where('necessite_transfert_chef', true)
            ->findOrFail($id);

        abort_if($dossier->isArchived(), 403, 'Ce document est archivé et ne peut plus être modifié.');

        $request->validate([
            'structures' => 'required|array|min:1',
            'structures.*' => 'in:' . implode(',', \App\Support\Espaces::TYPES_VISA),
        ] + \App\Services\VisaService::regles($user), [
            'structures.required' => 'Veuillez choisir au moins une structure dans le circuit de visa.',
            'structures.min' => 'Veuillez choisir au moins une structure dans le circuit de visa.',
        ] + \App\Services\VisaService::messages());

        $types = \App\Services\CircuitVisaService::normaliser($request->structures);

        $circuitService = app(\App\Services\CircuitVisaService::class);
        $manquantes = array_filter($types, fn ($type) => !$circuitService->chefDe($type));
        if ($manquantes) {
            return back()->with('error', 'Aucun responsable trouvé pour : ' . implode(', ', array_map([\App\Support\Espaces::class, 'nom'], $manquantes)) . '. Contactez l\'administrateur.');
        }

        $derniereVersion = $dossier->versions()->orderByDesc('numero_version')->first();
        abort_unless($derniereVersion, 422, 'Ce dossier ne contient aucune version à transférer.');

        // Visa du chef émetteur : cachet + signature enregistrés sur le document avant son envoi.
        // Un échec (cachet/signature invalide) lève une ValidationException : rien n'est transféré.
        $visaService = app(\App\Services\VisaService::class);
        $visaService->enregistrer($request, $user, $derniereVersion, 'envoi', implode(',', $types));
        $visaApplique = app(\App\Services\DocumentStampingService::class)->viser($derniereVersion);

        DB::transaction(function () use ($circuitService, $dossier, $derniereVersion, $user, $types) {
            $circuitService->ouvrir($dossier, $derniereVersion, $user, $types);
        });

        $noms = implode(', ', array_map([\App\Support\Espaces::class, 'nom'], $types));

        $message = "Document visé et envoyé pour visa à : $noms.";
        if (!$visaApplique) {
            $message .= ' Le visa est enregistré, mais il n\'a pas pu être inscrit sur le fichier (format non pris en charge ou PDF illisible).';
        }

        return redirect()->eroute('entreprise.documents.a-transferer')->with('success', $message);
    }
}
