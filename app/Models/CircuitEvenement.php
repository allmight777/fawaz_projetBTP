<?php

namespace App\Models;

use App\Support\Espaces;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Ligne du journal du circuit de visa (visa, correction demandée, décision modifiée, applicable…). */
class CircuitEvenement extends Model
{
    protected $table = 'circuit_evenements';

    protected $fillable = ['dossier_id', 'document_version_id', 'structure_type', 'user_id', 'action', 'motif'];

    public const LIBELLES = [
        'envoi' => 'Envoi pour visa',
        'visa' => 'Visa accordé',
        'correction_demandee' => 'Correction demandée',
        'decision_modifiee' => 'Décision modifiée',
        'nouvelle_version' => 'Nouvelle version envoyée',
        'structure_ajoutee' => 'Structure ajoutée au circuit',
        'rendu_applicable' => 'Rendu applicable',
        'applicable_retire' => 'Statut applicable retiré',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'document_version_id');
    }

    public function getLibelleAttribute(): string
    {
        return self::LIBELLES[$this->action] ?? $this->action;
    }

    public function getSigleAttribute(): ?string
    {
        return $this->structure_type ? Espaces::sigle($this->structure_type) : null;
    }

    public static function noter(Dossier $dossier, string $action, ?User $user = null, ?string $structureType = null, ?DocumentVersion $version = null, ?string $motif = null): self
    {
        return static::create([
            'dossier_id' => $dossier->id,
            'document_version_id' => $version?->id,
            'structure_type' => $structureType,
            'user_id' => $user?->id,
            'action' => $action,
            'motif' => $motif,
        ]);
    }
}
