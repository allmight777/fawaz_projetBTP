<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'role',
        'lot_id',
        'actif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
        ];
    }

    // Relation avec Lot
    public function lot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    // Accesseur pour obtenir le nom complet
    public function getFullNameAttribute(): string
    {
        return $this->prenom ? $this->prenom . ' ' . $this->nom : $this->nom;
    }

    // Vérifier si l'utilisateur est actif
    public function isActive(): bool
    {
        return $this->actif;
    }

    // Vérifier le rôle
    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isChefLot(): bool
    {
        return $this->role === 'CHEF LOT';
    }

    public function isControleur(): bool
    {
        return $this->role === 'CONTROLEUR';
    }
}
