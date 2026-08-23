<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentObservation extends Model
{
    use HasFactory;

    protected $table = 'document_observations';

    protected $fillable = [
        'document_assignment_id',
        'auteur_id',
        'type',
        'contenu',
        'retenue',
    ];

    protected $casts = [
        'retenue' => 'boolean',
    ];

    // Relations
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(DocumentAssignment::class, 'document_assignment_id');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    // Scopes
    public function scopeRetenues($query)
    {
        return $query->where('retenue', true);
    }

    public function scopeNonConformites($query)
    {
        return $query->where('type', 'non_conformite');
    }
}
