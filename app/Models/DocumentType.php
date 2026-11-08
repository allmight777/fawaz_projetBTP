<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    /** Catégories d'origine (utilisées si la table des catégories est vide). */
    public const CATEGORIES = [
        'etude_execution' => 'Étude d\'exécution',
        'gestion_projet' => 'Gestion de projet',
        'assurance_qualite' => 'Assurance qualité',
        'environnemental_social' => 'Environnemental et social',
        'bc_document' => 'Documents Bureau de Contrôle',
    ];

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

    /** Catégories gérées par l'admin, dans l'ordre : [clé => libellé]. */
    public static function categories(): array
    {
        try {
            $lignes = DocumentCategory::orderBy('ordre')->orderBy('nom')->pluck('nom', 'cle')->all();
        } catch (\Throwable $e) {
            $lignes = [];
        }

        return $lignes ?: self::CATEGORIES;
    }

    public function getLibelleCategorieAttribute(): string
    {
        return self::categories()[$this->categorie] ?? ucfirst(str_replace('_', ' ', (string) $this->categorie));
    }

    public function getModeLabelAttribute(): string
    {
        return match ($this->mode_traitement) {
            'simple' => 'Informatif',
            'validation' => 'Circuit de visa',
            default => 'Inconnu',
        };
    }
}
