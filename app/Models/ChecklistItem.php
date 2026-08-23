<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type_id',
        'libelle',
        'ordre',
        'obligatoire',
    ];

    protected $casts = [
        'obligatoire' => 'boolean',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }
    public function reponses()
{
    return $this->hasMany(ChecklistReponse::class);
}
}
