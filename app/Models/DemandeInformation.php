<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeInformation extends Model
{
    protected $table = 'demandes_information';

    protected $fillable = [
        'demandeur_id', 'destinataire_id', 'portee', 'objet', 'message', 'statut',
        'dossier_id', 'reponse', 'repondu_le', 'cloture_le',
    ];

    protected $casts = ['repondu_le' => 'datetime', 'cloture_le' => 'datetime'];

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'accordee' => 'Accès accordé',
            'refusee' => 'Refusée',
            'cloturee' => 'Clôturée',
            default => 'Inconnu',
        };
    }
}
