<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentAssignment extends Model
{
    use HasFactory;

    protected $table = 'document_assignments';

    protected $fillable = [
        'document_version_id',
        'controleur_id',
        'specialite',
        'affecte_par',
        'date_affectation',
        'date_limite',
        'statut',
    ];

    protected $casts = [
        'date_affectation' => 'datetime',
        'date_limite' => 'datetime',
    ];

    /**
     * Pourcentage de temps écoulé entre l'affectation et le délai limite (0-100).
     * Retourne null si aucun délai n'est défini.
     */
    public function getPourcentageEcouleAttribute(): ?int
    {
        if (!$this->date_limite) {
            return null;
        }

        $debut = $this->date_affectation ?? $this->created_at;
        $totalSecondes = $debut->diffInSeconds($this->date_limite, false);

        if ($totalSecondes <= 0) {
            return 100;
        }

        $ecouleSecondes = $debut->diffInSeconds(now(), false);
        $pourcentage = (int) round(($ecouleSecondes / $totalSecondes) * 100);

        return max(0, min(100, $pourcentage));
    }

    /**
     * Vrai si le délai est dépassé ou proche de l'être (seuil d'alerte à 80% du temps écoulé,
     * ou moins de 24h restantes).
     */
    public function getDelaiCritiqueAttribute(): bool
    {
        if (!$this->date_limite) {
            return false;
        }

        if (now()->greaterThanOrEqualTo($this->date_limite)) {
            return true;
        }

        $heuresRestantes = now()->diffInHours($this->date_limite, false);

        return $this->pourcentage_ecoule >= 80 || $heuresRestantes <= 24;
    }

    // Relations
    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    public function controleur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'controleur_id');
    }

    public function affectePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affecte_par');
    }

     public function version()
    {
        return $this->belongsTo(DocumentVersion::class, 'document_version_id');
    }


    public function observations(): HasMany
    {
        return $this->hasMany(DocumentObservation::class);
    }

    // Scopes
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }
}
