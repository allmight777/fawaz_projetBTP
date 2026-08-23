<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTransmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'document_version_id',
        'emetteur_id',
        'mode',
        'commentaire',
        'date_transmission',
    ];

    protected $casts = [
        'date_transmission' => 'datetime',
    ];

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    public function emetteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emetteur_id');
    }

    public function destinataires(): HasMany
    {
        return $this->hasMany(TransmissionDestinataire::class);
    }
    
}
