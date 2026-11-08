<?php

namespace App\Support;

use App\Models\User;

/**
 * Les trois structures qui donnent leur visa (Bureau de Contrôle, Bureau d'Études, Maître d'Œuvre)
 * partagent le même fonctionnement : un chef reçoit, affecte, consolide et vise ; un collaborateur analyse.
 * Chacune a ses propres routes (même suffixe, préfixe différent), son layout et sa couleur.
 *
 * Les vues sont écrites une seule fois avec les noms de route du Bureau de Contrôle
 * (« cheflot.* » pour le chef, « controleur.* » pour le collaborateur) ; sroute() les traduit
 * vers les routes de l'espace de l'utilisateur connecté.
 */
class Espaces
{
    /** Types de structure dont le visa peut être demandé, dans l'ordre d'affichage. */
    public const TYPES_VISA = ['bureau_controle', 'bureau_etudes', 'maitre_oeuvre'];

    public const STRUCTURES = [
        'bureau_controle' => [
            'sigle' => 'BC',
            'nom' => 'Bureau de Contrôle',
            'chef' => 'cheflot.',
            'collab' => 'controleur.',
            'espace_chef' => 'cheflot',
            'espace_collab' => 'controleur',
            'accent' => '#047857',
            'icone' => 'fa-clipboard-check',
        ],
        'bureau_etudes' => [
            'sigle' => 'BE',
            'nom' => "Bureau d'Études",
            'chef' => 'bureau_etudes.chef.',
            'collab' => 'bureau_etudes.collaborateur.',
            'espace_chef' => 'bureau_etudes_chef',
            'espace_collab' => 'bureau_etudes_collab',
            'accent' => '#047857',
            'icone' => 'fa-drafting-compass',
        ],
        'maitre_oeuvre' => [
            'sigle' => 'MOE',
            'nom' => "Maître d'Œuvre",
            'chef' => 'maitre_oeuvre.chef.',
            'collab' => 'maitre_oeuvre.collaborateur.',
            'espace_chef' => 'maitre_oeuvre_chef',
            'espace_collab' => 'maitre_oeuvre_collab',
            'accent' => '#8B6FD6', // violet lavande
            'icone' => 'fa-helmet-safety',
        ],
    ];

    public static function sigle(string $type): string
    {
        return self::STRUCTURES[$type]['sigle'] ?? strtoupper($type);
    }

    public static function nom(string $type): string
    {
        return self::STRUCTURES[$type]['nom'] ?? $type;
    }

    /** Traduit un nom de route « cheflot.* » / « controleur.* » vers l'espace de l'utilisateur. */
    public static function nomRoute(string $nom, ?User $user = null): string
    {
        $type = ($user ?? auth()->user())?->structureVisa() ?? 'bureau_controle';
        $cfg = self::STRUCTURES[$type];

        if (str_starts_with($nom, 'cheflot.')) {
            return $cfg['chef'] . substr($nom, strlen('cheflot.'));
        }
        if (str_starts_with($nom, 'controleur.')) {
            return $cfg['collab'] . substr($nom, strlen('controleur.'));
        }

        return $nom;
    }

    /** Layout de l'espace de l'utilisateur (couleur et menu). */
    public static function layout(?User $user = null): string
    {
        return ($user ?? auth()->user())->spaceLayout();
    }
}
