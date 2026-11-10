<?php

namespace App\Notifications;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
use App\Support\MailGabarit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentRecu extends Notification
{
    use Queueable;

    public function __construct(public Dossier $dossier)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return MailGabarit::message('Nouveau document soumis - ' . $this->dossier->identifiant_affiche, [
            'titre' => 'Nouveau document soumis',
            'sous_titre' => 'Un document attend votre analyse',
            'intro' => ['Bonjour <strong>' . MailGabarit::e($notifiable->full_name) . '</strong>, le document ci-dessous a été soumis pour analyse. Merci de le traiter dans les meilleurs délais.'],
            'lignes' => MailGabarit::infosDossier($this->dossier),
            'action' => 'Voir le document',
            'url' => route('dossiers.show', $this->dossier),
        ]);
    }
}
