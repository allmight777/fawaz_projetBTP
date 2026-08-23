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
];
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


    public function getLatestVersion()
    {
        return $this->versions()->latest('numero_version')->first();
    }

    public function derniereVersion()
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
        return match ($this->statut) {
            'brouillon' => 'Brouillon',
            'soumis' => 'Soumis',
            'transmis' => 'Transmis',
            'en_analyse' => 'En analyse',
            'en_cours' => 'En cours',
            'valide' => 'Validé',
            'a_corriger' => 'À corriger',
            'rejete' => 'Rejeté',
            'archive' => 'Archivé',
            default => 'Inconnu',
        };
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
}
