<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentDecision extends Model
{
    use HasFactory;

    protected $table = 'document_decisions';

    protected $fillable = [
        'document_version_id',
        'decision',
        'validateur_id',
        'date_decision',
        'commentaires',
    ];

    protected $casts = [
        'date_decision' => 'datetime',
    ];

    // Relations
    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }



    public function estValidee(): bool
    {
        return $this->decision === 'valide';
    }

    public function validateur()
{
    return $this->belongsTo(User::class, 'validateur_id');
}

public function version()
{
    return $this->belongsTo(DocumentVersion::class, 'document_version_id');
}
}
