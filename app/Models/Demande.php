<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Demande extends Model
{
    use HasFactory;

    protected $table = 'demandes';

    protected $fillable = [
        'numero_demande',
        'date_soumission',
        'soumis_par',
        'entreprise',
        'lot_id',
        'type_document',
        'titre_document',
        'description',
        'version',
        'version_number',
        'document_id',
        'fichier_url',
        'fichier_chemin',  // AJOUTÉ
        'fichier_nom',      // AJOUTÉ
        'statut',
        'controleur_id',
        'priorite',
        'echeance_controle',
        'statut_delai',
        'derniere_maj',
        'decision_controleur',
        'date_decision',
        'commentaire_controleur',
        'parent_demande_id',
        'est_actif',
    ];

    protected $casts = [
        'date_soumission' => 'datetime',
        'echeance_controle' => 'datetime',
        'date_decision' => 'datetime',
        'derniere_maj' => 'datetime',
        'est_actif' => 'boolean',
    ];

    // Relations
    public function soumisPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'soumis_par');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class, 'lot_id');
    }

    public function controleur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'controleur_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Demande::class, 'parent_demande_id');
    }

    public function versions()
    {
        return $this->hasMany(Demande::class, 'parent_demande_id');
    }

    public function historiques()
    {
        return $this->hasMany(HistoriqueDemande::class);
    }

    // Accesseur pour obtenir le fichier (url ou chemin)
    public function getFichierAttribute()
    {
        if ($this->fichier_chemin) {
            return asset('storage/' . $this->fichier_chemin);
        }
        return $this->fichier_url;
    }

    public function hasFichier(): bool
    {
        return !empty($this->fichier_chemin) || !empty($this->fichier_url);
    }

    // Génération automatique du numéro de demande
    public static function genererNumeroDemande(): string
    {
        $year = date('Y');
        $lastDemande = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastDemande) {
            $lastNum = intval(substr($lastDemande->numero_demande, -4));
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return 'DEM-' . $year . '-' . $newNum;
    }

    // Vérifier si la demande peut être validée
    public function peutEtreValidee(): bool
    {
        return $this->statut === 'EN CONTROLE';
    }

    // Valider la demande et créer une nouvelle version
    public function valider(int $userId, ?string $commentaire = null): void
    {
        if (!$this->peutEtreValidee()) {
            throw new \Exception('Cette demande ne peut pas être validée.');
        }

        // Créer une nouvelle version
        $nouvelleDemande = $this->replicate();
        $nouvelleDemande->numero_demande = self::genererNumeroDemande();
        $nouvelleDemande->version_number = $this->version_number + 1;
        $nouvelleDemande->version = 'V' . $nouvelleDemande->version_number;
        $nouvelleDemande->parent_demande_id = $this->id;
        $nouvelleDemande->statut = 'EN ATTENTE';
        $nouvelleDemande->decision_controleur = null;
        $nouvelleDemande->date_decision = null;
        $nouvelleDemande->commentaire_controleur = null;
        $nouvelleDemande->controleur_id = null;
        $nouvelleDemande->echeance_controle = null;
        $nouvelleDemande->date_soumission = now();
        $nouvelleDemande->created_at = now();
        $nouvelleDemande->save();

        // Mettre à jour la demande actuelle
        $this->statut = 'VALIDE';
        $this->decision_controleur = 'VALIDE';
        $this->date_decision = now();
        $this->commentaire_controleur = $commentaire;
        $this->save();

        // Créer l'historique
        HistoriqueDemande::create([
            'demande_id' => $this->id,
            'user_id' => $userId,
            'action' => 'VALIDATION',
            'details' => $commentaire,
            'ancien_statut' => 'EN CONTROLE',
            'nouveau_statut' => 'VALIDE',
            'ancienne_version' => $this->version,
            'nouvelle_version' => $nouvelleDemande->version,
        ]);
    }

    // Rejeter la demande
    public function rejeter(int $userId, string $commentaire): void
    {
        $this->statut = 'REJETE';
        $this->decision_controleur = 'REJETE';
        $this->date_decision = now();
        $this->commentaire_controleur = $commentaire;
        $this->save();

        HistoriqueDemande::create([
            'demande_id' => $this->id,
            'user_id' => $userId,
            'action' => 'REJET',
            'details' => $commentaire,
            'ancien_statut' => 'EN CONTROLE',
            'nouveau_statut' => 'REJETE',
        ]);
    }

    // Assigner un contrôleur
    public function assignerControleur(int $controleurId, int $assignePar): void
    {
        $this->controleur_id = $controleurId;
        $this->statut = 'EN CONTROLE';
        $this->echeance_controle = now()->addDays(7);
        $this->save();

        HistoriqueDemande::create([
            'demande_id' => $this->id,
            'user_id' => $assignePar,
            'action' => 'ASSIGNATION',
            'details' => 'Assigné à ' . User::find($controleurId)?->nom,
            'ancien_statut' => 'EN ATTENTE',
            'nouveau_statut' => 'EN CONTROLE',
        ]);
    }

    // Mettre à jour le statut du délai
    public function mettreAJourStatutDelai(): void
    {
        if ($this->echeance_controle && $this->echeance_controle < now() && $this->statut === 'EN CONTROLE') {
            $this->statut_delai = 'EN RETARD';
            $this->save();
        }
    }

    // Obtenir le badge CSS pour la priorité
    public function getBadgePriorite(): string
    {
        return match ($this->priorite) {
            'Basse' => 'badge-priorite-basse',
            'Moyenne' => 'badge-priorite-moyenne',
            'Haute' => 'badge-priorite-haute',
            'Urgente' => 'badge-priorite-urgente',
            default => 'badge-priorite-moyenne',
        };
    }

    // Obtenir le badge CSS pour le statut
    public function getBadgeStatut(): string
    {
        return match ($this->statut) {
            'EN ATTENTE' => 'badge-statut-attente',
            'EN CONTROLE' => 'badge-statut-cours',
            'VALIDE' => 'badge-statut-valide',
            'REJETE' => 'badge-statut-rejete',
            default => 'badge-statut-attente',
        };
    }
}
