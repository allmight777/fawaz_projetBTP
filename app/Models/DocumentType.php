<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'nom',
        'categorie',
        'mode_traitement',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function getModeLabelAttribute(): string
    {
        return match ($this->mode_traitement) {
            'simple' => 'Informatif',
            'validation' => 'Contrôle',
            default => 'Inconnu',
        };
    }
}
