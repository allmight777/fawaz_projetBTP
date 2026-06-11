<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueDemande extends Model
{
    use HasFactory;

    protected $table = 'historique_demandes';

    protected $fillable = [
        'demande_id',
        'user_id',
        'action',
        'details',
        'ancien_statut',
        'nouveau_statut',
        'ancienne_version',
        'nouvelle_version',
    ];

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
