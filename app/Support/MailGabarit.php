<?php

namespace App\Support;

use App\Models\Dossier;
use Illuminate\Notifications\Messages\MailMessage;

/** Construit les mails des notifications au gabarit commun (resources/views/emails/layout), sans emoji. */
class MailGabarit
{
    /** Lignes « Informations du document » communes à tous les mails de dossier. */
    public static function infosDossier(Dossier $dossier, ?string $version = null): array
    {
        return [
            'Identifiant' => $dossier->identifiant_affiche,
            'Titre' => $dossier->titre,
            'Type' => $dossier->documentType->nom ?? null,
            'Version' => $version,
            'Structure émettrice' => $dossier->structureEmettrice?->nom ?? $dossier->creePar?->structure?->nom,
        ];
    }

    /**
     * @param array{titre: string, sous_titre?: string, intro?: array<int, string>, lignes?: array<string, mixed>,
     *              lignes_titre?: string, note?: ?string, note_titre?: string, ton?: string,
     *              action?: string, url?: string, action_note?: string} $contenu
     */
    public static function message(string $sujet, array $contenu): MailMessage
    {
        // Les phrases d'introduction sont du HTML court : tout ce qui vient des données est échappé par l'appelant.
        return (new MailMessage)->subject($sujet)->view('emails.notification', $contenu);
    }

    public static function e(?string $texte): string
    {
        return e((string) $texte);
    }
}
