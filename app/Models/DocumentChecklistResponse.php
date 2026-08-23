<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentChecklistResponse extends Model
{
    use HasFactory;

    protected $table = 'document_checklist_responses';

    protected $fillable = [
        'document_version_id',
        'checklist_item_id',
        'coche',
        'commentaire',
    ];

    protected $casts = [
        'coche' => 'boolean',
    ];

    // Relations
    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class);
    }
}
