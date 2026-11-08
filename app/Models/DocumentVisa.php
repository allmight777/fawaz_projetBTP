<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVisa extends Model
{
    protected $fillable = [
        'dossier_id', 'document_version_id', 'user_id', 'structure_id', 'type',
        'fonction', 'destinataire', 'cachet_chemin', 'signature_chemin', 'signe_le',
    ];

    protected $casts = ['signe_le' => 'datetime'];

    public function version(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'document_version_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
}
