<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransmissionDestinataire extends Model
{
    use HasFactory;

    protected $table = 'transmission_destinataires';

    protected $fillable = [
        'document_transmission_id',
        'structure_id',
        'user_id',
        'consulte_le',
    ];

    protected $casts = [
        'consulte_le' => 'datetime',
    ];

    public function transmission(): BelongsTo
    {
        return $this->belongsTo(DocumentTransmission::class, 'document_transmission_id');
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function marquerConsulte(): void
    {
        $this->update(['consulte_le' => now()]);
    }
}
