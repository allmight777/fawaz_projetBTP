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
    public function chefDashboard()
    {
        $user = Auth::user();
        $entrepriseId = $user->structure_id;

        $projetsEnCours = Dossier::where('cree_par', $user->id)->where('statut', 'en_cours')->count();
        $projetsTermines = Dossier::where('cree_par', $user->id)->where('statut', 'valide')->count();
        $documentsSoumis = DocumentVersion::where('importe_par', $user->id)->count();
        $documentsValides = DocumentVersion::where('importe_par', $user->id)->where('statut', 'valide')->count();
        $documentsEnAttente = DocumentVersion::where('importe_par', $user->id)->where('statut', 'en_attente_checklist')->count();
        $nonConformites = 0;

        $documentsRecents = DocumentVersion::where('importe_par', $user->id)
            ->with('dossier')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'];
        $projetsData = [4, 6, 8, 7, 10, 12];

        return view('entreprise.chef.dashboard', compact(
            'user',
            'projetsEnCours',
            'projetsTermines',
            'documentsSoumis',
            'documentsValides',
            'documentsEnAttente',
            'nonConformites',
            'documentsRecents',
            'months',
            'projetsData'
        ));
    }

    public function collaborateurDashboard()
    {
        $user = Auth::user();

        $mesDocuments = DocumentVersion::where('importe_par', $user->id)->count();
        $documentsEnAttente = DocumentVersion::where('importe_par', $user->id)->where('statut', 'en_attente_checklist')->count();
        $documentsValides = DocumentVersion::where('importe_par', $user->id)->where('statut', 'valide')->count();
        $documentsACorriger = DocumentVersion::where('importe_par', $user->id)->where('statut', 'a_corriger')->count();

        $mesDocumentsRecents = DocumentVersion::where('importe_par', $user->id)
            ->with('dossier')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('entreprise.collaborateur.dashboard', compact(
            'user',
            'mesDocuments',
            'documentsEnAttente',
            'documentsValides',
            'documentsACorriger',
            'mesDocumentsRecents'
        ));
    }

    public function documents()
    {
        $user = Auth::user();
        $isChef = $user->categorie_role === 'responsable_organisme';

        $documents = DocumentVersion::whereHas('dossier', function($query) use ($user) {
            $query->where('cree_par', $user->id);
        })->with(['dossier', 'dossier.documentType'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

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
        $dossiers = Dossier::where('structure_emettrice_id', $user->structure_id)
            ->with(['documentType', 'versions'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('entreprise.dossiers', compact('dossiers'));
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

        $dossier = Dossier::create([
            'identifiant' => Dossier::genererIdentifiant($documentType),
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

        return redirect()->route('entreprise.dossiers')
            ->with('success', 'Dossier créé avec succès.');
    }

    public function showDossier($id)
    {
        $user = Auth::user();
        $dossier = Dossier::with(['documentType', 'versions', 'versions.importePar'])
            ->where('cree_par', $user->id)
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

        // Validation manuelle destinataires
        foreach ($request->file('fichiers') as $index => $file) {
            $mode = $request->mode[$index];
            $destinatairesDoc = $request->destinataires[$index] ?? [];

            if ($mode === 'simple' && empty($destinatairesDoc)) {
                Log::warning("Document $index sans destinataire en mode simple");
                return back()->withErrors([
                    'destinataires' => "Document " . ($index + 1) . " : veuillez sélectionner au moins un destinataire pour une transmission informative."
                ])->withInput();
            }
        }

        try {
            DB::transaction(function () use ($request, $user, &$nbDocuments, &$nbValidation, &$nbInformatif) {
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

                    // ✅ FIX : Stockage du fichier avant toute autre opération
                    $path = $file->store('documents/entreprise', 'public');
                    Log::info("Fichier stocké index=$index", ['path' => $path]);

                    if (!$path) {
                        throw new \Exception("Échec du stockage du fichier pour le document " . ($index + 1));
                    }

                    $documentType = DocumentType::findOrFail($documentTypeId);

                    $dossier = Dossier::create([
                        'identifiant' => Dossier::genererIdentifiant($documentType),
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

                    if ($mode === 'validation') {
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
                                    Mail::to($chefEntreprise->email)->send(new DocumentForControlMail($version, $dossier, $user));
                                    Log::info('Email de validation envoyé au chef entreprise ' . $chefEntreprise->email);
                                } catch (\Exception $e) {
                                    Log::error('Erreur envoi email vers chef entreprise: ' . $e->getMessage());
                                }
                            }
                        }

                        $dossier->update([
                            'statut' => 'en_cours',
                            'necessite_transfert_chef' => true,
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

        return redirect()->route('entreprise.documents')->with('success', $message);
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
        $dossier = Dossier::where('cree_par', $user->id)->findOrFail($id);

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
        $dossier = Dossier::where('cree_par', $user->id)->findOrFail($dossierId);
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
        $dossier = Dossier::with(['documentType', 'versions' => function($q) {
                $q->orderByDesc('numero_version');
            }])
            ->where('cree_par', $user->id)
            ->findOrFail($id);

        if ($dossier->statut !== 'a_corriger') {
            return redirect()->route('entreprise.dossiers.show', $dossier->id)
                ->with('error', 'Ce dossier n\'est pas en attente de correction.');
        }

        $derniereVersion = $dossier->versions->first();
        $decision = null;

        if ($derniereVersion) {
            $decision = \App\Models\DocumentDecision::where('document_version_id', $derniereVersion->id)
                ->orderByDesc('created_at')
                ->first();
        }

        return view('entreprise.dossiers-corriger', compact('dossier', 'decision', 'derniereVersion'));
    }

    /**
     * Enregistre la correction d'un dossier
     */
    public function storeCorrection(Request $request, $id)
    {
        $user = Auth::user();
        $dossier = Dossier::where('cree_par', $user->id)->findOrFail($id);

        if ($dossier->statut !== 'a_corriger') {
            return redirect()->route('entreprise.dossiers.show', $dossier->id)
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

            // Retransmet automatiquement au Bureau de Contrôle
            $chefBC = User::whereHas('structure', function ($query) {
                $query->where('type', 'bureau_controle');
            })->where('categorie_role', 'responsable_organisme')->first();

            $transmission = DocumentTransmission::create([
                'dossier_id' => $dossier->id,
                'document_version_id' => $nouvelleVersion->id,
                'emetteur_id' => $user->id,
                'mode' => 'diffusion_validation',
                'commentaire' => $request->commentaire,
                'date_transmission' => now(),
            ]);

            if ($chefBC) {
                TransmissionDestinataire::create([
                    'document_transmission_id' => $transmission->id,
                    'user_id' => $chefBC->id,
                ]);

                try {
                    Mail::to($chefBC->email)->send(new DocumentForControlMail($nouvelleVersion, $dossier, $user));
                    Log::info('Email de correction envoyé au BC pour le dossier ' . $dossier->id);
                } catch (\Exception $e) {
                    Log::error('Erreur envoi email correction: ' . $e->getMessage());
                }
            }

            $dossier->update(['statut' => 'en_cours']);

            Log::info('Correction soumise', [
                'dossier_id' => $dossier->id,
                'nouvelle_version' => $nouvelleVersion->numero_version,
            ]);
        });

        return redirect()->route('entreprise.dossiers.show', $dossier->id)
            ->with('success', 'Correction envoyée avec succès pour nouvelle validation.');
    }

    /**
     * Télécharge une version spécifique d'un dossier
     */
    public function telechargerVersion($dossierId, $versionId)
    {
        $user = Auth::user();
        $dossier = Dossier::where('cree_par', $user->id)->findOrFail($dossierId);
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

        if (!$user->isResponsableOrganisme()) {
            abort(403, 'Accès réservé au chef d\'entreprise.');
        }

        $dossiers = Dossier::whereHas('creePar', function ($q) use ($user) {
                $q->where('structure_id', $user->structure_id);
            })
            ->where('necessite_transfert_chef', true)
            ->with(['documentType', 'versions', 'creePar'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('entreprise.documents-a-transferer', compact('dossiers'));
    }

    /**
     * Transfère un dossier vers une structure externe (Bureau de Contrôle ou Bureau d'Études)
     */
    public function transfererDossier(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isResponsableOrganisme()) {
            abort(403);
        }

        $dossier = Dossier::whereHas('creePar', function ($q) use ($user) {
                $q->where('structure_id', $user->structure_id);
            })
            ->where('necessite_transfert_chef', true)
            ->findOrFail($id);

        $request->validate([
            'structure_cible' => 'required|in:bureau_controle,bureau_etudes,maitre_ouvrage',
        ], [
            'structure_cible.required' => 'Veuillez choisir une structure de destination.',
        ]);

        $structureCible = $request->structure_cible;

        $chefCible = User::whereHas('structure', function ($q) use ($structureCible) {
                $q->where('type', $structureCible);
            })->where('categorie_role', 'responsable_organisme')->first();

        if (!$chefCible) {
            return back()->with('error', 'Aucun responsable trouvé pour cette structure. Contactez l\'administrateur.');
        }

        $derniereVersion = $dossier->versions()->orderByDesc('numero_version')->first();

        DB::transaction(function () use ($dossier, $derniereVersion, $chefCible, $user, $structureCible) {
            $transmission = DocumentTransmission::create([
                'dossier_id' => $dossier->id,
                'document_version_id' => $derniereVersion?->id,
                'emetteur_id' => $user->id,
                'mode' => 'validation',
                'commentaire' => null,
                'date_transmission' => now(),
            ]);

            TransmissionDestinataire::create([
                'document_transmission_id' => $transmission->id,
                'user_id' => $chefCible->id,
                // La transmission cible aussi la structure entière : tout membre
                // du Bureau de Contrôle (ou d'Études, ou MO) destinataire doit
                // pouvoir consulter le dossier, pas seulement son responsable.
                'structure_id' => $chefCible->structure_id,
            ]);

            try {
                Mail::to($chefCible->email)->send(new DocumentForControlMail($derniereVersion, $dossier, $user));
                Log::info('Email de transfert externe envoyé à ' . $chefCible->email);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email transfert externe: ' . $e->getMessage());
            }

            $dossier->update([
                'necessite_transfert_chef' => false,
                'structure_cible' => $structureCible,
                'statut' => 'en_cours',
            ]);
        });

        $label = $structureCible === 'bureau_controle' ? 'Bureau de Contrôle' : 'Bureau d\'Études';

        return redirect()->route('entreprise.documents.a-transferer')
            ->with('success', "Document transféré avec succès au $label.");
    }
}
