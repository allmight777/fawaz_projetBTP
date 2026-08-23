<?php

namespace App\Policies;

use App\Models\Dossier;
use App\Models\User;

class DossierPolicy
{
    // Qui peut consulter/télécharger un dossier : l'émetteur, un contrôleur affecté
    // à une version de ce dossier, ou un destinataire réel d'une transmission (par
    // utilisateur ou par structure — ce qui couvre Bureau de Contrôle et MO). Aucun
    // rôle n'a de visibilité par défaut : la règle suit Dossier::scopeVisiblePar(),
    // pour ne jamais diverger de ce qu'affiche la liste des dossiers.
    public function view(User $user, Dossier $dossier): bool
    {
        return Dossier::visiblePar($user)->whereKey($dossier->id)->exists();
    }
}
