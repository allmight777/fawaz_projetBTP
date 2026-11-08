<?php

namespace App\Services;

use App\Mail\DocumentForControlMail;
use App\Models\CircuitVisa;
use App\Models\DocumentTransmission;
use App\Models\DocumentVersion;
use App\Models\Dossier;
use App\Models\TransmissionDestinataire;
use App\Models\User;
use App\Notifications\Alerte;
use App\Support\Espaces;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Circuit de visa : l'émetteur choisit les structures dont il a besoin du visa (BC, BE, MOE).
 * Le document part chez elles en même temps ; chacune vérifie et vise à son niveau, sans
 * attendre les autres. Le document n'est « Validé » que lorsque toutes ont visé ; dès qu'une
 * structure demande une correction, il passe « À corriger ».
 *
 * Un dossier sans circuit (documents antérieurs) garde l'ancien comportement : le Bureau de
 * Contrôle seul décide.
 */
class CircuitVisaService
{
    /** Garde uniquement les types valides, dans l'ordre d'affichage, sans doublon. */
    public static function normaliser(?array $types): array
    {
        return array_values(array_intersect(Espaces::TYPES_VISA, array_unique($types ?? [])));
    }

    /** Responsable actif d'une structure visante. */
    public function chefDe(string $type): ?User
    {
        $chef = User::whereHas('structure', fn ($q) => $q->where('type', $type))
            ->where('categorie_role', User::CATEGORIE_RESPONSABLE)
            ->where('actif', true)
            ->first();

        if (!$chef && $type === 'bureau_controle') {
            $chef = User::chefsBureauControle()->where('actif', true)->first();
        }

        return $chef;
    }

    /** Ouvre le circuit : une ligne par structure, une transmission, une alerte et un mail par responsable. */
    public function ouvrir(Dossier $dossier, DocumentVersion $version, User $emetteur, array $types, ?string $commentaire = null, string $modeTransmission = 'validation'): DocumentTransmission
    {
        $types = self::normaliser($types);
        abort_if(empty($types), 422, 'Aucune structure de visa sélectionnée.');

        $transmission = DocumentTransmission::create([
            'dossier_id' => $dossier->id,
            'document_version_id' => $version->id,
            'emetteur_id' => $emetteur->id,
            'mode' => $modeTransmission,
            'commentaire' => $commentaire,
            'date_transmission' => now(),
        ]);

        foreach ($types as $type) {
            CircuitVisa::updateOrCreate(
                ['document_version_id' => $version->id, 'structure_type' => $type],
                ['dossier_id' => $dossier->id, 'statut' => 'en_attente', 'decide_par' => null, 'decide_le' => null, 'commentaire' => null]
            );

            $chef = $this->chefDe($type);
            if (!$chef) {
                Log::warning("Circuit de visa : aucun responsable actif pour la structure $type (dossier {$dossier->id}).");
                continue;
            }

            TransmissionDestinataire::create([
                'document_transmission_id' => $transmission->id,
                'user_id' => $chef->id,
                // Toute la structure doit pouvoir consulter le dossier, pas seulement son responsable.
                'structure_id' => $chef->structure_id,
            ]);

            try {
                Mail::to($chef->email)->send(new DocumentForControlMail($version, $dossier, $emetteur));
            } catch (\Throwable $e) {
                Log::error('Erreur envoi email circuit de visa (' . $type . ') : ' . $e->getMessage());
            }
            Alerte::envoyer($chef, 'Nouveau document à viser : ' . $dossier->titre, route('dossiers.show', $dossier));
        }

        $dossier->update([
            'statut' => 'en_cours',
            'necessite_transfert_chef' => false,
            'structure_cible' => implode(',', array_map([Espaces::class, 'sigle'], $types)),
        ]);

        return $transmission;
    }

    /** Nouvelle version après correction : le même circuit est rouvert (BC par défaut pour un ancien dossier). */
    public function rouvrir(Dossier $dossier, DocumentVersion $nouvelleVersion, User $emetteur, ?string $commentaire = null): DocumentTransmission
    {
        $types = $this->typesDemandes($dossier, $nouvelleVersion->id);

        return $this->ouvrir($dossier, $nouvelleVersion, $emetteur, $types ?: ['bureau_controle'], $commentaire, 'diffusion_validation');
    }

    /** Structures dont le visa a été demandé sur la dernière version du dossier antérieure à $sauf. */
    private function typesDemandes(Dossier $dossier, ?int $sauf = null): array
    {
        $derniere = CircuitVisa::where('dossier_id', $dossier->id)
            ->when($sauf, fn ($q) => $q->where('document_version_id', '!=', $sauf))
            ->max('document_version_id');

        if (!$derniere) {
            return [];
        }

        return CircuitVisa::where('document_version_id', $derniere)->pluck('structure_type')->all();
    }

    public function lignes(DocumentVersion $version): Collection
    {
        return CircuitVisa::where('document_version_id', $version->id)->get();
    }

    public function aCircuit(DocumentVersion $version): bool
    {
        return CircuitVisa::where('document_version_id', $version->id)->exists();
    }

    public function ligneDe(DocumentVersion $version, User $user): ?CircuitVisa
    {
        $type = $user->structureVisa();

        return $type ? CircuitVisa::where('document_version_id', $version->id)->where('structure_type', $type)->first() : null;
    }

    private function figee(DocumentVersion $version): bool
    {
        return in_array($version->statut, ['valide', 'a_corriger', 'rejete'], true) || $version->dossier->isArchived();
    }

    /** Le responsable peut-il affecter des collaborateurs à cette version ? */
    public function peutAffecter(User $user, DocumentVersion $version): bool
    {
        if (!$user->estChefVisa() || $this->figee($version)) {
            return false;
        }

        if (!$this->aCircuit($version)) {
            return $user->structureVisa() === 'bureau_controle' && $version->statut === 'soumis';
        }

        return $this->ligneDe($version, $user)?->statut === 'en_attente';
    }

    /** Le responsable peut-il consolider et décider (après affectation) ? */
    public function peutDecider(User $user, DocumentVersion $version): bool
    {
        if (!$user->estChefVisa() || $this->figee($version)) {
            return false;
        }

        if (!$this->aCircuit($version)) {
            return $user->structureVisa() === 'bureau_controle' && $version->statut === 'en_analyse';
        }

        return $this->ligneDe($version, $user)?->statut === 'en_analyse';
    }

    /** Le responsable peut-il trancher directement, sans affecter (décision rapide depuis « Documents reçus ») ? */
    public function peutDeciderDirectement(User $user, DocumentVersion $version): bool
    {
        if (!$user->estChefVisa() || $this->figee($version)) {
            return false;
        }

        if (!$this->aCircuit($version)) {
            return $user->structureVisa() === 'bureau_controle';
        }

        return in_array($this->ligneDe($version, $user)?->statut, ['en_attente', 'en_analyse'], true);
    }

    /** Une structure commence son analyse (premier collaborateur affecté). */
    public function demarrerAnalyse(DocumentVersion $version, User $user): void
    {
        $ligne = $this->ligneDe($version, $user);
        if ($ligne && $ligne->statut === 'en_attente') {
            $ligne->update(['statut' => 'en_analyse']);
        }

        $version->update(['statut' => 'en_analyse']);
        $version->dossier->update(['statut' => 'en_analyse']);
    }

    /**
     * Enregistre la décision d'une structure et recalcule l'état global du document.
     *
     * @return string 'valide' (tous les visas obtenus), 'a_corriger' (au moins une correction demandée)
     *                ou 'partiel' (cette structure a visé, d'autres doivent encore le faire)
     */
    public function appliquerDecision(DocumentVersion $version, User $user, string $decision, ?string $commentaire = null): string
    {
        $ligne = $this->ligneDe($version, $user);

        if ($ligne) {
            $ligne->update([
                'statut' => $decision === 'valide' ? 'valide' : 'a_corriger',
                'decide_par' => $user->id,
                'decide_le' => now(),
                'commentaire' => $commentaire,
            ]);
        }

        $lignes = $this->lignes($version);
        $global = match (true) {
            $decision !== 'valide' => 'a_corriger',
            $lignes->isEmpty() || $lignes->every(fn (CircuitVisa $l) => $l->statut === 'valide') => 'valide',
            default => 'partiel',
        };

        if ($global === 'partiel') {
            // Les visas déjà obtenus apparaissent tout de suite sur le document servi aux autres structures.
            app(DocumentStampingService::class)->viser($version->fresh());

            if ($version->dossier->creePar) {
                Alerte::envoyer(
                    $version->dossier->creePar,
                    'Visa ' . Espaces::sigle($user->structureVisa() ?? '') . ' obtenu (' . $lignes->where('statut', 'valide')->count() . '/' . $lignes->count() . ') : ' . $version->dossier->titre,
                    route('dossiers.show', $version->dossier)
                );
            }

            return 'partiel';
        }

        $statut = $global === 'valide' ? 'valide' : 'a_corriger';
        $version->update(['statut' => $statut]);
        $version->dossier->update(['statut' => $statut]);

        return $global;
    }

    /**
     * Colonne « Visas » d'un dossier : état de chaque structure sollicitée sur sa dernière version.
     *
     * @return array<int, array{type: string, sigle: string, nom: string, statut: string}>
     */
    public static function resume(Dossier $dossier): array
    {
        $versions = $dossier->relationLoaded('versions') ? $dossier->versions : $dossier->versions()->get();
        $derniere = $versions->sortByDesc('numero_version')->first();

        return $derniere ? self::resumeVersion($derniere, $dossier) : [];
    }

    /**
     * Colonne « Visas » d'une version précise. Les statuts affichés sont ceux de CETTE version :
     * une V1 « À corriger » n'affiche jamais le visa obtenu par la V2.
     *
     * @return array<int, array{type: string, sigle: string, nom: string, statut: string}>
     */
    public static function resumeVersion(DocumentVersion $version, ?Dossier $dossier = null): array
    {
        $dossier ??= $version->dossier;

        $lignes = $version->relationLoaded('circuitVisas') ? $version->circuitVisas : $version->circuitVisas()->get();
        if ($lignes->isNotEmpty()) {
            return $lignes
                ->sortBy(fn (CircuitVisa $l) => array_search($l->structure_type, Espaces::TYPES_VISA, true))
                ->map(fn (CircuitVisa $l) => [
                    'type' => $l->structure_type,
                    'sigle' => $l->sigle,
                    'nom' => $l->nom_structure,
                    'statut' => $l->statut,
                ])->values()->all();
        }

        // Document sans circuit (antérieur) : seul le Bureau de Contrôle visait. Rien à afficher pour un
        // document informatif ou pas encore transmis à une structure.
        if ($dossier->mode_traitement !== 'validation' || $dossier->necessite_transfert_chef) {
            return [];
        }

        $statut = match ($version->statut) {
            'valide' => 'valide',
            'a_corriger', 'rejete' => 'a_corriger',
            'en_analyse' => 'en_analyse',
            default => 'en_attente',
        };

        return [['type' => 'bureau_controle', 'sigle' => 'BC', 'nom' => 'Bureau de Contrôle', 'statut' => $statut]];
    }
}
