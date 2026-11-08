<?php

namespace App\Services;

use App\Models\User;

class SpaceLabels
{
    /** Fonction affichée sur un visa quand le profil n'en renseigne pas. */
    public static function fonction(User $user): string
    {
        return match (true) {
            $user->isChefLot() => 'Responsable du Bureau de Contrôle',
            $user->isEntreprise() => "Responsable d'entreprise",
            $user->isBureauControle() => 'Responsable du Bureau de Contrôle',
            $user->isBureauEtudes() => "Responsable du Bureau d'Études",
            $user->isMaitreOeuvre() => "Responsable du Maître d'Œuvre",
            $user->isMaitreOuvrage() => "Responsable du Maître d'Ouvrage",
            default => 'Responsable',
        };
    }
}
