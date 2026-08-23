<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistReponse extends Model
{
    protected $fillable = ['document_version_id', 'checklist_item_id', 'valeur', 'repondu_par'];

    protected $casts = ['valeur' => 'boolean'];

    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class);
    }

    public function documentVersion()
    {
        return $this->belongsTo(DocumentVersion::class);
    }
}
