<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Configuration visuelle et menu de chaque espace (entreprise, bureau de contrôle,
 * chef de lot, bureau d'études, maître d'ouvrage, admin).
 * La couleur d'accent de chaque espace reprend celle qui existait déjà.
 */
class SpaceMenu
{
    private const SPACES = [
        'entreprise'         => ['sub' => 'Espace Entreprise',        'accent' => '#2563eb'],
        'cheflot'            => ['sub' => 'Espace Chef de Lot',       'accent' => '#047857'],
        'controleur'         => ['sub' => 'Espace Contrôle',          'accent' => '#047857'],
        'bureau_etudes_chef' => ['sub' => 'Bureau d\'Études',         'accent' => '#047857'],
        'bureau_etudes_collab' => ['sub' => 'Bureau d\'Études',       'accent' => '#047857'],
        'maitre_oeuvre_chef' => ['sub' => 'Maître d\'Œuvre',          'accent' => '#8B6FD6'],
        'maitre_oeuvre_collab' => ['sub' => 'Maître d\'Œuvre',        'accent' => '#8B6FD6'],
        'maitre-ouvrage'     => ['sub' => 'Espace Maître d\'Ouvrage', 'accent' => '#E91E8C'],
        'admin'              => ['sub' => 'Administration',           'accent' => '#8b1e3f'],
    ];

    private const ROLES = [
        'ADMIN' => 'Administrateur',
        'CHEF_LOT' => 'Chef de lot',
        'CONTROLEUR' => 'Contrôleur',
        'ENTREPRISE_CHEF' => 'Chef d\'entreprise',
        'ENTREPRISE_COLLABORATEUR' => 'Collaborateur',
        'BUREAU_CONTROLE_CHEF' => 'Chef du bureau de contrôle',
        'BUREAU_CONTROLE_COLLABORATEUR' => 'Collaborateur',
        'BUREAU_ETUDES_CHEF' => 'Chef du bureau d\'études',
        'BUREAU_ETUDES_COLLABORATEUR' => 'Collaborateur',
        'MAITRE_OUVRAGE_CHEF' => 'Chef maître d\'ouvrage',
        'MAITRE_OUVRAGE_COLLABORATEUR' => 'Collaborateur',
        'MAITRE_OEUVRE_CHEF' => 'Chef maître d\'œuvre',
        'MAITRE_OEUVRE_COLLABORATEUR' => 'Collaborateur',
    ];

    /** @return array{sub: string, accent: string, items: array<int, array{label: string, url: string, active: bool}>} */
    public static function pour(string $espace, User $user): array
    {
        $cfg = self::SPACES[$espace] ?? self::SPACES['entreprise'];
        // Une entrée dont la route n'existe pas (encore) n'est tout simplement pas affichée.
        $entrees = array_values(array_filter(self::entrees($espace, $user), fn (array $i) => Route::has($i[1])));

        // Mes collaborateurs : lien des chefs de structure, dans chaque espace sauf l'admin.
        if ($user->peutViser() && $espace !== 'admin' && Route::has('collaborateurs.index')) {
            $position = $espace === 'cheflot' ? 2 : max(0, count($entrees) - 1);
            array_splice($entrees, $position, 0, [['Mes collaborateurs', 'collaborateurs.index', ['collaborateurs.*']]]);
        }

        // Canevas (types de documents et listes de contrôle, en consultation) : chaque espace sauf l'admin,
        // qui les gère déjà dans « Types de documents ».
        if ($espace !== 'admin' && Route::has('canevas.index')) {
            array_splice($entrees, max(0, count($entrees) - 1), 0, [['Canevas', 'canevas.index', ['canevas.*']]]);
        }

        // Demandes d'information : dans la navigation de chaque chef de structure (pas des collaborateurs).
        if ($user->peutViser() && Route::has('rfi.index')) {
            array_splice($entrees, max(0, count($entrees) - 1), 0, [["Demandes d'info", 'rfi.index', ['rfi.*']]]);
        }

        $items = array_map(fn (array $i) => [
            'label' => $i[0],
            'url' => route($i[1]),
            'active' => request()->routeIs($i[2]),
            'badge' => $i[1] === 'rfi.index' ? $user->demandesInformationRecues()->where('statut', 'en_attente')->count() : 0,
        ], $entrees);

        return $cfg + ['items' => $items];
    }

    public static function roleLabel(User $user): string
    {
        return self::ROLES[$user->role] ?? ucfirst(strtolower(str_replace('_', ' ', (string) $user->role)));
    }

    /** @return array<int, array{0: string, 1: string, 2: array<int, string>}> [libellé, route, motifs actifs] */
    private static function entrees(string $espace, User $user): array
    {
        $chef = $user->isResponsableOrganisme();
        $events = ['Événements', 'events.index', ['events.*']];

        return match ($espace) {
            'entreprise' => array_values(array_filter([
                ['Tableau de bord', $chef ? 'entreprise.chef.dashboard' : 'entreprise.collaborateur.dashboard', ['entreprise.chef.dashboard', 'entreprise.collaborateur.dashboard']],
                ['Mes documents', 'entreprise.documents', ['entreprise.documents']],
                ['Dossiers', 'entreprise.dossiers', ['entreprise.dossiers', 'entreprise.dossiers.*']],
                $chef ? ['À transférer', 'entreprise.documents.a-transferer', ['entreprise.documents.a-transferer']] : null,
                $chef ? ['Archives', 'entreprise.archives', ['entreprise.archives']] : null,
                $events,
            ])),
            'cheflot' => [
                ['Tableau de bord', 'cheflot.dashboard', ['cheflot.dashboard']],
                ['Documents reçus', 'cheflot.documents.recus', ['cheflot.documents.recus*']],
                ['Statistiques', 'cheflot.statistiques', ['cheflot.statistiques']],
                ['Historique', 'cheflot.historique.global', ['cheflot.historique.global']],
                ['Archives', 'cheflot.archives.index', ['cheflot.archives.*']],
                $events,
            ],
            'controleur' => [
                ['Tableau de bord', 'controleur.dashboard', ['controleur.dashboard']],
                ['Mes affectations', 'controleur.affectations.index', ['controleur.affectations*']],
                ['Tous les dossiers', 'dossiers.index', ['dossiers.*']],
                $events,
            ],
            'bureau_etudes_chef', 'maitre_oeuvre_chef' => self::entreesChefVisa($espace === 'bureau_etudes_chef' ? 'bureau_etudes.chef.' : 'maitre_oeuvre.chef.', $events),
            'bureau_etudes_collab', 'maitre_oeuvre_collab' => self::entreesCollaborateurVisa($espace === 'bureau_etudes_collab' ? 'bureau_etudes.collaborateur.' : 'maitre_oeuvre.collaborateur.', $events),
            'maitre-ouvrage' => [
                ['Documents de mon lot', $chef ? 'maitre_ouvrage.chef.dashboard' : 'maitre_ouvrage.collaborateur.dashboard', ['maitre_ouvrage.chef.dashboard', 'maitre_ouvrage.collaborateur.dashboard']],
                ['Archives', 'maitre_ouvrage.archives', ['maitre_ouvrage.archives']],
                $events,
            ],
            'admin' => [
                ['Tableau de bord', 'admin.dashboard', ['admin.dashboard']],
                ['Utilisateurs', 'admin.users', ['admin.users*']],
                ['Lots', 'admin.lots', ['admin.lots*']],
                ['Types de documents', 'admin.document-types.index', ['admin.document-types*']],
                $events,
            ],
            default => [],
        };
    }

    /** Menu du responsable d'une structure visante (BE, MOE) : même navigation que le chef du Bureau de Contrôle. */
    private static function entreesChefVisa(string $p, array $events): array
    {
        return [
            ['Tableau de bord', $p . 'dashboard', [$p . 'dashboard']],
            ['Documents reçus', $p . 'documents.recus', [$p . 'documents.recus*']],
            ['Statistiques', $p . 'statistiques', [$p . 'statistiques*']],
            ['Archives', $p . 'archives.index', [$p . 'archives.*']],
            $events,
        ];
    }

    /** Menu du collaborateur d'une structure visante (BE, MOE) : même navigation que le contrôleur du Bureau de Contrôle. */
    private static function entreesCollaborateurVisa(string $p, array $events): array
    {
        return [
            ['Tableau de bord', $p . 'dashboard', [$p . 'dashboard']],
            ['Mes affectations', $p . 'affectations.index', [$p . 'affectations*']],
            ['Tous les dossiers', 'dossiers.index', ['dossiers.*']],
            $events,
        ];
    }
}
