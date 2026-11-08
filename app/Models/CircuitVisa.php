<?php

namespace App\Models;

use App\Support\Espaces;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Visa demandé à une structure (BC, BE ou MOE) pour une version de document. */
class CircuitVisa extends Model
{
    protected $table = 'circuit_visas';

    protected $fillable = [
        'dossier_id', 'document_version_id', 'structure_type', 'statut',
        'decide_par', 'decide_le', 'commentaire',
    ];

    protected $casts = ['decide_le' => 'datetime'];

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'document_version_id');
    }

    public function decideur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decide_par');
    }

    public function getSigleAttribute(): string
    {
        return Espaces::sigle($this->structure_type);
    }

    public function getNomStructureAttribute(): string
    {
        return Espaces::nom($this->structure_type);
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'valide' => 'Visé',
            'a_corriger' => 'À corriger',
            'en_analyse' => 'En analyse',
            default => 'En attente',
        };
    }
}
