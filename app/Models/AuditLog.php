<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    // Journal immuable : pas de colonne updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'dossier_id',
        'metadonnees',
        'ip_address',
    ];

    protected $casts = [
        'metadonnees' => 'array',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    // Enregistrement rapide d'une entrée d'audit
    public static function enregistrer(string $action, ?int $dossierId = null, ?array $metadonnees = null): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'dossier_id' => $dossierId,
            'metadonnees' => $metadonnees,
            'ip_address' => request()?->ip(),
        ]);
    }
}
