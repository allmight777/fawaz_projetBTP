<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'numero_version',
        'fichier_nom',
        'fichier_chemin',
        'fichier_valide_chemin',
        'fichier_url',
        'importe_par',
        'date_import',
        'statut',
        'commentaire',
    ];

    protected $casts = [
        'date_import' => 'datetime',
    ];

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function importePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'importe_par');
    }

    public function checklistResponses(): HasMany
    {
        return $this->hasMany(DocumentChecklistResponse::class);
    }


    public function affectations()
{
    return $this->hasMany(DocumentAssignment::class, 'document_version_id');
}

    public function decisions(): HasMany
    {
        return $this->hasMany(DocumentDecision::class);
    }

    public function checklistReponses()
{
    return $this->hasMany(ChecklistReponse::class);
}

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'brouillon' => 'Brouillon',
            'soumis' => 'Soumis',
            'en_attente_checklist' => 'En attente de checklist',
            'en_analyse' => 'En analyse',
            'valide' => 'Validé',
            'a_corriger' => 'À corriger',
            'rejete' => 'Rejeté',
            default => 'Inconnu',
        };
    }

    public function hasFichier(): bool
    {
        return !empty($this->fichier_chemin) || !empty($this->fichier_url);
    }

    // Chemin (disque "public") à servir en priorité : la version tamponnée si elle
    // existe, sinon l'original. Ne remplace jamais le fichier source.
    public function getCheminAServirAttribute(): ?string
    {
        return $this->fichier_valide_chemin ?: $this->fichier_chemin;
    }

    /**
     * Nom de fichier normalisé pour cette version précise : {identifiant}.{ext}
     * pour la V1, {identifiant}-Vn.{ext} au-delà. S'appuie sur le numéro de
     * CETTE version (pas sur le numéro courant du dossier), pour rester correct
     * même en téléchargeant une ancienne version d'un dossier déjà plus avancé.
     */
    public function getNomAfficheAttribute(): string
    {
        $extension = pathinfo($this->fichier_nom ?? '', PATHINFO_EXTENSION);
        $suffixe = $this->numero_version > 1 ? '-V' . $this->numero_version : '';
        $nom = ($this->dossier->identifiant ?? 'document') . $suffixe;

        return $extension ? $nom . '.' . $extension : $nom;
    }
}
