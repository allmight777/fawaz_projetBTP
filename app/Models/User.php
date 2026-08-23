<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Rôles existants
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_CHEF_LOT = 'CHEF LOT';
    const ROLE_CONTROLEUR = 'CONTROLEUR';
    const ROLE_EN_ATTENTE = 'EN_ATTENTE';

    // Nouveaux rôles pour les structures
    const ROLE_ENTREPRISE_CHEF = 'ENTREPRISE_CHEF';
    const ROLE_ENTREPRISE_COLLABORATEUR = 'ENTREPRISE_COLLABORATEUR';
    const ROLE_BUREAU_CONTROLE_CHEF = 'BUREAU_CONTROLE_CHEF';
    const ROLE_BUREAU_CONTROLE_COLLABORATEUR = 'BUREAU_CONTROLE_COLLABORATEUR';
    const ROLE_BUREAU_ETUDES_CHEF = 'BUREAU_ETUDES_CHEF';
    const ROLE_BUREAU_ETUDES_COLLABORATEUR = 'BUREAU_ETUDES_COLLABORATEUR';
    const ROLE_MAITRE_OUVRAGE_CHEF = 'MAITRE_OUVRAGE_CHEF';
    const ROLE_MAITRE_OUVRAGE_COLLABORATEUR = 'MAITRE_OUVRAGE_COLLABORATEUR';

    // Catégories de rôle
    const CATEGORIE_RESPONSABLE = 'responsable_organisme';
    const CATEGORIE_COLLABORATEUR = 'collaborateur';

    // Statuts de compte (pour un compte déjà validé, hors EN_ATTENTE)
    const STATUT_ACTIF = 'actif';
    const STATUT_INACTIF = 'inactif';
    const STATUT_DESACTIVE = 'desactive';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'role',
        'lot_id',
        'actif',
        'statut',
        'structure_id',
        'categorie_role',
        'fonction',
        'specialite',
        'raison',
    ];

    protected static function booted(): void
    {
        // Le booléen `actif` reste la source de vérité pour tout le reste du code
        // (dashboards, requêtes de filtrage...) ; `statut` distingue simplement
        // pourquoi le compte n'est pas actif (inactif = suspension réactivable,
        // desactive = coupure définitive par l'admin).
        static::saving(function (User $user) {
            if ($user->isDirty('statut') && in_array($user->statut, [self::STATUT_ACTIF, self::STATUT_INACTIF, self::STATUT_DESACTIVE], true)) {
                $user->actif = $user->statut === self::STATUT_ACTIF;
            }
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
        ];
    }

    // Relations
    public function lot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function structure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function dossiersImportes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Dossier::class, 'cree_par');
    }

    public function affectations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentAssignment::class, 'controleur_id');
    }

    public function evenementsCrees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Event::class, 'createur_id');
    }

    public function evenements(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_user')
            ->withPivot('statut')
            ->withTimestamps();
    }

    // Accesseur
    public function getFullNameAttribute(): string
    {
        return $this->prenom ? $this->prenom . ' ' . $this->nom : $this->nom;
    }

    // Vérifications
    public function isActive(): bool
    {
        return $this->actif && $this->role !== self::ROLE_EN_ATTENTE;
    }

    public function isPending(): bool
    {
        return $this->role === self::ROLE_EN_ATTENTE;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isChefLot(): bool
    {
        return $this->role === self::ROLE_CHEF_LOT;
    }

    public function isControleur(): bool
    {
        return $this->role === self::ROLE_CONTROLEUR;
    }

    // Vérifications pour les nouveaux rôles
    public function isEntrepriseChef(): bool
    {
        return $this->role === self::ROLE_ENTREPRISE_CHEF;
    }

    public function isEntrepriseCollaborateur(): bool
    {
        return $this->role === self::ROLE_ENTREPRISE_COLLABORATEUR;
    }

    public function isBureauControleChef(): bool
    {
        return $this->role === self::ROLE_BUREAU_CONTROLE_CHEF;
    }

    public function isBureauControleCollaborateur(): bool
    {
        return $this->role === self::ROLE_BUREAU_CONTROLE_COLLABORATEUR;
    }

    public function isBureauEtudesChef(): bool
    {
        return $this->role === self::ROLE_BUREAU_ETUDES_CHEF;
    }

    public function isBureauEtudesCollaborateur(): bool
    {
        return $this->role === self::ROLE_BUREAU_ETUDES_COLLABORATEUR;
    }

    public function isMaitreOuvrageChef(): bool
    {
        return $this->role === self::ROLE_MAITRE_OUVRAGE_CHEF;
    }

    public function isMaitreOuvrageCollaborateur(): bool
    {
        return $this->role === self::ROLE_MAITRE_OUVRAGE_COLLABORATEUR;
    }

    // Vérifier si l'utilisateur est un responsable d'organisme
    public function isResponsableOrganisme(): bool
    {
        return $this->categorie_role === self::CATEGORIE_RESPONSABLE;
    }

    /**
     * Vrai pour tout "chef" quel que soit son organisme : admin, chef de lot (chef des
     * contrôleurs), ou responsable d'organisme (chef d'entreprise, de bureau de contrôle,
     * de bureau d'études, de maître d'ouvrage). Faux pour un simple collaborateur.
     */
    public function isChef(): bool
    {
        return $this->isAdmin() || $this->role === self::ROLE_CHEF_LOT || $this->isResponsableOrganisme();
    }

    public function isCollaborateur(): bool
    {
        return $this->categorie_role === self::CATEGORIE_COLLABORATEUR;
    }

    // Vérifier le type de structure
    public function isEntreprise(): bool
    {
        return $this->structure?->type === 'entreprise';
    }

    public function isBureauControle(): bool
    {
        return $this->structure?->type === 'bureau_controle';
    }

    public function isBureauEtudes(): bool
    {
        return $this->structure?->type === 'bureau_etudes';
    }

    public function isMaitreOuvrage(): bool
    {
        return $this->structure?->type === 'maitre_ouvrage';
    }

    // Layout Blade de l'espace visuel de l'utilisateur (sidebar/couleur d'accent)
    public function spaceLayout(): string
    {
        return match (true) {
            $this->role === self::ROLE_ADMIN => 'layouts.admin',
            $this->role === self::ROLE_CHEF_LOT => 'layouts.cheflot',
            $this->role === self::ROLE_CONTROLEUR => 'layouts.controleur',
            $this->isBureauControle() => 'layouts.controleur',
            $this->isMaitreOuvrage() => 'layouts.maitre-ouvrage',
            default => 'layouts.entreprise',
        };
    }

    // Récupérer le dashboard route en fonction du rôle
    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'admin.dashboard',
            self::ROLE_CHEF_LOT => 'cheflot.dashboard',
            self::ROLE_CONTROLEUR => 'controleur.dashboard',
            self::ROLE_ENTREPRISE_CHEF => 'entreprise.chef.dashboard',
            self::ROLE_ENTREPRISE_COLLABORATEUR => 'entreprise.collaborateur.dashboard',
            self::ROLE_BUREAU_CONTROLE_CHEF => 'bureau_controle.chef.dashboard',
            self::ROLE_BUREAU_CONTROLE_COLLABORATEUR => 'bureau_controle.collaborateur.dashboard',
            self::ROLE_BUREAU_ETUDES_CHEF => 'bureau_etudes.chef.dashboard',
            self::ROLE_BUREAU_ETUDES_COLLABORATEUR => 'bureau_etudes.collaborateur.dashboard',
            self::ROLE_MAITRE_OUVRAGE_CHEF => 'maitre_ouvrage.chef.dashboard',
            self::ROLE_MAITRE_OUVRAGE_COLLABORATEUR => 'maitre_ouvrage.collaborateur.dashboard',
            default => 'dashboard',
        };
    }
}
