<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Structure extends Model
{
    use HasFactory;

    protected $table = 'structures';

    protected $fillable = [
        'nom',
        'type',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // Relations
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class, 'structure_emettrice_id');
    }

    public function transmissionsRecues(): HasMany
    {
        return $this->hasMany(TransmissionDestinataire::class);
    }

    // Scopes
    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }

    // Libellé lisible du type de structure
    public function getLibelleTypeAttribute(): string
    {
        return match ($this->type) {
            'entreprise' => 'Entreprise',
            'bureau_controle' => 'Bureau de Contrôle',
            'bureau_etudes' => "Bureau d'Études",
            'maitre_ouvrage' => "Maître d'Ouvrage Délégué",
            default => $this->type,
        };
    }
}
