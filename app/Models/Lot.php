<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // Relation avec les utilisateurs
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    // Accesseur
    public function getFullNameAttribute(): string
    {
        return $this->code . ' - ' . $this->nom;
    }
    // Relation avec les demandes
public function demandes(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Demande::class);
}
}
