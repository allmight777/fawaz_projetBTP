<?php

namespace App\Support;

use App\Models\Dossier;
use App\Models\User;
use Closure;

/**
 * Chiffres des tableaux de bord : toujours calculés sur les dossiers réels, jamais saisis à la main.
 * Les documents « informatifs » (sans visa) sont comptés à part : ils ne sont ni en attente ni validés.
 */
class StatsDossiers
{
    /**
     * @param Closure $base fonction qui retourne une nouvelle requête Dossier à chaque appel
     * @return array{total:int, informatifs:int, au_visa:int, en_attente:int, a_transferer:int, en_analyse:int, valides:int, a_corriger:int, en_attente_anciens:int}
     */
    public static function compter(Closure $base): array
    {
        $visa = fn () => $base()->where('mode_traitement', 'validation');

        $enAttente = fn () => $visa()->whereIn('statut', ['brouillon', 'soumis', 'en_cours']);

        return [
            'total' => $base()->count(),
            'informatifs' => $base()->where('mode_traitement', 'simple')->count(),
            'au_visa' => $visa()->count(),
            'en_attente' => $enAttente()->count(),
            'a_transferer' => $base()->where('necessite_transfert_chef', true)->count(),
            'en_analyse' => $visa()->where('statut', 'en_analyse')->count(),
            'valides' => $visa()->whereIn('statut', ['valide', 'archive'])->count(),
            'a_corriger' => $visa()->where('statut', 'a_corriger')->count(),
            'en_attente_anciens' => $enAttente()->where('dossiers.created_at', '<', now()->subDays(5))->count(),
        ];
    }

    /** Documents par mois sur les 6 derniers mois : [libellés, valeurs]. */
    public static function parMois(Closure $base): array
    {
        $debuts = [];
        $libelles = [];
        for ($i = 5; $i >= 0; $i--) {
            $debut = now()->startOfMonth()->subMonths($i);
            $debuts[] = $debut;
            $libelles[] = ucfirst($debut->copy()->locale('fr')->translatedFormat('M'));
        }

        $valeurs = array_fill(0, 6, 0);
        foreach ($base()->where('dossiers.created_at', '>=', $debuts[0])->pluck('dossiers.created_at') as $date) {
            foreach ($debuts as $idx => $debut) {
                if ($date >= $debut && $date <= $debut->copy()->endOfMonth()) {
                    $valeurs[$idx]++;
                    break;
                }
            }
        }

        return [$libelles, $valeurs];
    }

    /**
     * Chiffres d'une structure qui donne son visa (BC, BE, MOE), du point de vue de SON visa :
     * en attente = son visa n'est pas encore donné ; validés = elle a visé ; à corriger = correction demandée.
     *
     * @return array{total:int, informatifs:int, en_attente:int, en_analyse:int, valides:int, a_corriger:int}
     */
    public static function visa(User $user): array
    {
        $type = $user->structureVisa();
        $compteurs = ['total' => 0, 'informatifs' => 0, 'en_attente' => 0, 'en_analyse' => 0, 'valides' => 0, 'a_corriger' => 0];

        $dossiers = Dossier::transmisA($user)->with('circuitVisas')->get(['dossiers.id', 'dossiers.statut', 'dossiers.mode_traitement']);

        foreach ($dossiers as $dossier) {
            $compteurs['total']++;

            if ($dossier->mode_traitement === 'simple') {
                $compteurs['informatifs']++;
                continue;
            }

            $lignes = $dossier->circuitVisas;
            $derniere = $lignes->max('document_version_id');
            $mienne = $derniere ? $lignes->where('document_version_id', $derniere)->firstWhere('structure_type', $type) : null;

            $etat = $mienne?->statut ?? match ($dossier->statut) {
                'valide', 'archive' => 'valide',
                'a_corriger' => 'a_corriger',
                'en_analyse' => 'en_analyse',
                default => 'en_attente',
            };

            // Une correction demandée par une autre structure fige le document pour tout le monde.
            if ($dossier->statut === 'a_corriger' && $etat !== 'valide') {
                $etat = 'a_corriger';
            }

            $compteurs[match ($etat) {
                'valide' => 'valides',
                'a_corriger' => 'a_corriger',
                'en_analyse' => 'en_analyse',
                default => 'en_attente',
            }]++;
        }

        return $compteurs;
    }
}
