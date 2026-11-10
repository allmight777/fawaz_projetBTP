<?php

namespace App\Notifications;

use App\Models\DocumentAssignment;
use Illuminate\Bus\Queueable;
use App\Support\MailGabarit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ControleurAffecte extends Notification
{
    use Queueable;

    public function __construct(public DocumentAssignment $documentAssignment)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $dossier = $this->documentAssignment->documentVersion->dossier;
        $limite = $this->documentAssignment->date_limite;

        return MailGabarit::message('Document affecté pour analyse - ' . $dossier->identifiant_affiche, [
            'titre' => 'Document affecté pour analyse',
            'sous_titre' => 'Une analyse vous est confiée',
            'intro' => ['Bonjour <strong>' . MailGabarit::e($notifiable->full_name) . '</strong>, vous avez été affecté(e) à l\'analyse du document ci-dessous.'],
            'lignes' => MailGabarit::infosDossier($dossier) + ['Délai' => $limite ? 'Avant le ' . $limite->translatedFormat('d/m/Y à H:i') : null],
            'ton' => $limite ? 'warn' : null,
            'action' => 'Analyser le document',
            'url' => route(\App\Support\Espaces::nomRoute('controleur.affectations.show', $notifiable), $this->documentAssignment),
            'action_note' => 'Merci de compléter votre analyse dans les meilleurs délais.',
        ]);
    }
}
