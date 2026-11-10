<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dossier extends Model
{
    use HasFactory;

   protected $fillable = [
    'titre',
    'description',
    'document_type_id',
    'structure_emettrice_id',
    'lot_id',
    'mode_traitement',
    'version_courante',
    'cree_par',
    'statut',
    'identifiant',
    'necessite_transfert_chef',
    'structure_cible',
    'applicable_le',
    'applicable_par',
];

    protected $casts = ['applicable_le' => 'datetime'];

    protected static function booted(): void
    {
        // Filet de sécurité : un dossier archivé est figé pour tout le monde (émetteur,
        // Bureau de Contrôle, chefs...). Les contrôleurs HTTP refusent déjà l'action avec
        // un message clair ; ceci garantit qu'aucun chemin oublié ne puisse le modifier,
        // le désarchiver ou le supprimer.
        static::updating(function (Dossier $dossier) {
            abort_if(
                $dossier->isDirty() && $dossier->getOriginal('statut') === 'archive',
                403,
                'Ce document est archivé et ne peut plus être modifié.'
            );
        });

        static::deleting(function (Dossier $dossier) {
            abort_if($dossier->getOriginal('statut') === 'archive', 403, 'Ce document est archivé et ne peut plus être supprimé.');
        });
    }

    /**
     * Génère un identifiant unique et immuable : {CODE_TYPE}-{DDMMYYYY},
     * avec un suffixe numérique en cas de collision (pas un numéro de version).
     */
    public static function genererIdentifiant(DocumentType $documentType): string
    {
        $base = $documentType->code . '-' . now()->format('dmY');
        $identifiant = $base;
        $suffixe = 1;

        while (static::where('identifiant', $identifiant)->exists()) {
            $identifiant = $base . '-' . ++$suffixe;
        }

        return $identifiant;
    }

    /**
     * Crée un dossier avec un identifiant unique, même si deux envois arrivent
     * en même temps : la vérification « existe déjà ? » ne suffit pas, c'est la
     * contrainte d'unicité qui tranche, et on réessaie avec le suffixe suivant.
     * Chaque tentative est dans un point de sauvegarde (indispensable sous
     * PostgreSQL, où une violation fait sinon échouer toute la transaction).
     */
    public static function creerAvecIdentifiant(DocumentType $documentType, array $attributs): static
    {
        for ($essai = 0; ; $essai++) {
            $identifiant = static::genererIdentifiant($documentType);
            try {
                return \Illuminate\Support\Facades\DB::transaction(
                    fn () => static::create(['identifiant' => $identifiant] + $attributs)
                );
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                if ($essai >= 20) {
                    throw $e;
                }
                usleep(random_int(5_000, 40_000));
            }
        }
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function creePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function structureEmettrice(): BelongsTo
    {
        return $this->belongsTo(Structure::class, 'structure_emettrice_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function transmissions(): HasMany
    {
        return $this->hasMany(DocumentTransmission::class);
    }

    /**
     * Un document déjà validé (ou visé) par quelqu'un n'est plus supprimable : il engage la structure qui a visé.
     * Vrai dès qu'une version est validée, qu'une structure a visé, ou que le dossier est validé/archivé.
     */
    public function aEteVise(): bool
    {
        return in_array($this->statut, ['valide', 'archive'], true)
            || $this->versions()->where('statut', 'valide')->exists()
            || \App\Models\DocumentVisa::where('dossier_id', $this->id)->where('type', 'validation')->exists()
            || $this->circuitVisas()->where('statut', 'valide')->exists();
    }

    public function estSupprimable(): bool
    {
        return !$this->aEteVise();
    }

    public function evenements(): HasMany
    {
        return $this->hasMany(CircuitEvenement::class)->orderBy('created_at')->orderBy('id');
    }

    public function estApplicable(): bool
    {
        return $this->applicable_le !== null && $this->statut === 'valide';
    }

    /** Tous les visas demandés sur la dernière version sont obtenus. */
    public function visasComplets(): bool
    {
        $derniere = $this->circuitVisas()->max('document_version_id');
        if (!$derniere) {
            return $this->statut === 'valide';
        }

        return !$this->circuitVisas()->where('document_version_id', $derniere)->where('statut', '!=', 'valide')->exists();
    }

    /** Seul le chef de l'entreprise émettrice rend un document applicable, une fois tous les visas obtenus. */
    public function applicablePar(User $user): bool
    {
        return $this->statut === 'valide'
            && $this->applicable_le === null
            && $user->isResponsableOrganisme()
            && $user->structure_id !== null
            && $user->structure_id === $this->structure_emettrice_id
            && $this->visasComplets();
    }

    /**
     * Qui a reçu un document peut le transmettre à son tour : l'émetteur, ou tout utilisateur à qui il a été
     * transmis (personnellement ou via sa structure). La retransmission donne la consultation, jamais un droit de visa.
     */
    public function retransmissiblePar(User $user): bool
    {
        // Partageable à tout moment (en cours de visa, validé ou archivé) : le partage informatif ne modifie rien.
        if ($this->statut === 'brouillon') {
            return false;
        }

        return $this->cree_par === $user->id
            || static::transmisA($user)->whereKey($this->id)->exists();
    }

    /** Le document appartient-il à l'utilisateur (émis par lui) ou à sa structure ? Sinon on le lui a partagé ou adressé. */
    public function appartientA(User $user): bool
    {
        return $this->cree_par === $user->id
            || ($user->structure_id !== null && $user->structure_id === $this->structure_emettrice_id);
    }

    /**
     * Identifiants des documents qu'on a partagés avec l'utilisateur de façon informative (transmission simple,
     * nominativement ou à sa structure), hors ses propres documents. Sert aux titres « Partagés avec moi ».
     *
     * @return array<int, int>
     */
    public static function idsPartagesAvec(User $user): array
    {
        return DocumentTransmission::where('mode', 'simple')
            ->whereHas('destinataires', function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->structure_id) {
                    $q->orWhere('structure_id', $user->structure_id);
                }
            })
            ->whereHas('dossier', fn ($d) => $d->where('cree_par', '!=', $user->id)
                ->when($user->structure_id, fn ($x) => $x->where(fn ($y) => $y->whereNull('structure_emettrice_id')->orWhere('structure_emettrice_id', '!=', $user->structure_id))))
            ->pluck('dossier_id')->unique()->values()->all();
    }

    public function scopeApplicables($query)
    {
        return $query->where('statut', 'valide')->whereNotNull('applicable_le');
    }

    public function circuitVisas(): HasMany
    {
        return $this->hasMany(CircuitVisa::class);
    }

    /** Structures souhaitées par l'émetteur (stockées sous forme de sigles dans structure_cible) : ['bureau_controle', ...]. */
    public function getStructuresSouhaiteesAttribute(): array
    {
        $sigles = array_filter(explode(',', (string) $this->structure_cible));

        return array_values(array_filter(\App\Support\Espaces::TYPES_VISA, fn ($type) => in_array(\App\Support\Espaces::sigle($type), $sigles, true)));
    }

    /** Visas par structure pour la colonne « Visas » : [['sigle' => 'BC', 'statut' => 'valide'], ...]. */
    public function getVisasResumeAttribute(): array
    {
        return \App\Services\CircuitVisaService::resume($this);
    }


    public function getLatestVersion()
    {
        return $this->versions()->latest('numero_version')->first();
    }

    public function derniereVersion()
    {
        return $this->getLatestVersion();
    }

    // Permet aussi l'accès en propriété ($dossier->derniereVersion), utilisé par plusieurs contrôleurs.
    public function getDerniereVersionAttribute()
    {
        return $this->getLatestVersion();
    }

    /**
     * Identifiant affiché aux utilisateurs : ajoute le suffixe -Vn quand
     * le dossier en est à sa version courante > 1. L'identifiant stocké
     * en base, lui, ne change jamais après création.
     */
    public function getIdentifiantAfficheAttribute(): string
    {
        $identifiant = $this->identifiant ?? 'N/A';

        if ($this->version_courante > 1) {
            $identifiant .= '-V' . $this->version_courante;
        }

        return $identifiant;
    }

    public function getStatutLabelAttribute(): string
    {
        if ($this->necessite_transfert_chef && $this->statut === 'en_cours') {
            return 'À transférer';
        }

        return match ($this->statut) {
            'brouillon' => 'Brouillon',
            'soumis' => 'Soumis',
            'transmis' => 'Transmis',
            'en_analyse' => 'En analyse',
            'en_cours' => 'En attente de visa',
            'valide' => $this->applicable_le ? 'Applicable' : 'Validé',
            'a_corriger' => 'À corriger',
            'rejete' => 'Rejeté',
            'archive' => 'Archivé',
            default => 'Inconnu',
        };
    }

    // Alias utilisé par les vues Tailwind (dossiers/index, dossiers/show)
    public function getLibelleStatutAttribute(): string
    {
        return $this->statut_label;
    }

    /**
     * Accesseur pour obtenir l'identifiant formaté
     */
    public function getIdentifiantFormattedAttribute(): string
    {
        return $this->identifiant ?? 'N/A';
    }

    /**
     * Scope pour rechercher par identifiant
     */
    public function scopeSearchByIdentifiant($query, $identifiant)
    {
        return $query->where('identifiant', 'LIKE', "%{$identifiant}%");
    }

    /**
     * Dossiers transmis à cet utilisateur : soit à lui personnellement, soit à
     * toute sa structure (un dossier envoyé "au Bureau de Contrôle" doit être
     * visible par tous les membres de ce bureau, pas seulement le destinataire
     * d'origine).
     */
    public function scopeTransmisA($query, User $user)
    {
        return $query->whereHas('transmissions.destinataires', function ($tq) use ($user) {
            $tq->where('user_id', $user->id);
            if ($user->structure_id) {
                $tq->orWhere('structure_id', $user->structure_id);
            }
        });
    }

    /**
     * Dossiers réellement visibles par cet utilisateur : les dossiers de sa
     * propre structure (créés par n'importe quel collègue), un dossier où il
     * est contrôleur affecté, ou un dossier transmis à lui ou à sa structure.
     * Aucun rôle n'a de visibilité globale par défaut — la visibilité suit
     * toujours une émission ou une transmission réelle, jamais l'appartenance
     * seule à un rôle.
     */
    public function scopeVisiblePar($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('cree_par', $user->id)
                ->orWhereHas('versions.affectations', function ($aq) use ($user) {
                    $aq->where('controleur_id', $user->id);
                });

            if ($user->structure_id) {
                $q->orWhere('structure_emettrice_id', $user->structure_id);
            }

            $q->orWhere(fn ($tq) => $tq->transmisA($user));

            // Un document « applicable » sert de référence à toutes les structures du chantier.
            if ($user->structure_id) {
                $q->orWhere(fn ($aq) => $aq->applicables());
            }
        });
    }

    /**
     * Vérifie si le dossier est modifiable
     */
    public function isEditable(): bool
    {
        return in_array($this->statut, ['brouillon', 'soumis']);
    }

    /**
     * Vérifie si le dossier est validé
     */
    public function isValidated(): bool
    {
        return $this->statut === 'valide';
    }

    /**
     * Vérifie si le dossier est en cours
     */
    public function isInProgress(): bool
    {
        return $this->statut === 'en_cours';
    }

    /**
     * Vérifie si le dossier est rejeté
     */
    public function isRejected(): bool
    {
        return $this->statut === 'rejete';
    }

    /**
     * Vérifie si le dossier est archivé
     */
    public function isArchived(): bool
    {
        return $this->statut === 'archive';
    }

    /**
     * Qui peut archiver : l'émetteur du document (quel que soit son rôle), le chef de sa structure, ou le chef
     * d'une structure à qui le document a été transmis (le Bureau de Contrôle archive ce qu'il a visé).
     * Seulement un document transmis, ou validé avec tous les visas obtenus (voir archivable()).
     */
    public function archivablePar(User $user): bool
    {
        return $this->archivable() && $this->droitArchiver($user);
    }

    /**
     * Un document n'est archivable que s'il est transmis (informatif) ou validé AVEC les visas de toutes les
     * structures sollicitées : « Validé » dès le premier visa ne suffit pas.
     */
    public function archivable(): bool
    {
        return $this->statut === 'transmis' || ($this->statut === 'valide' && $this->visasComplets());
    }

    /** Droit d'archiver ce document, indépendamment de son statut (voir archivablePar). */
    public function droitArchiver(User $user): bool
    {
        if ($this->cree_par === $user->id) {
            return true;
        }

        if (!$user->peutViser()) {
            return false;
        }

        return ($user->structure_id !== null && $user->structure_id === $this->structure_emettrice_id)
            || static::transmisA($user)->whereKey($this->id)->exists();
    }

    /**
     * Archives visibles par l'utilisateur : celles qu'il a émises, celles de sa propre structure (tout le monde) et
     * celles partagées avec lui nommément ; le chef voit en plus celles partagées avec sa structure.
     */
    public function scopeArchivesPour($query, User $user)
    {
        return $query->where('statut', 'archive')->where(function ($q) use ($user) {
            $q->where('cree_par', $user->id);

            if ($user->structure_id) {
                $q->orWhere('structure_emettrice_id', $user->structure_id);
            }
            // Partagé nommément avec cet utilisateur : il le voit, quel que soit son rôle.
            $q->orWhereHas('transmissions.destinataires', fn ($t) => $t->where('user_id', $user->id));

            // Partagé avec sa structure : seul le chef le voit, pas les collaborateurs.
            if ($user->peutViser() && $user->structure_id) {
                $q->orWhereHas('transmissions.destinataires', fn ($t) => $t->where('structure_id', $user->structure_id));
            }
        });
    }
}
