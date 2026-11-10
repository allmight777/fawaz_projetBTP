<?php

namespace App\Notifications;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
use App\Support\MailGabarit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentArchive extends Notification
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
        return MailGabarit::message('Document archivé - ' . $this->dossier->identifiant_affiche, [
            'titre' => 'Document archivé',
            'sous_titre' => 'Ce document est désormais figé',
            'intro' => ['Bonjour <strong>' . MailGabarit::e($notifiable->full_name) . '</strong>, le document ci-dessous a été archivé. Il reste consultable et téléchargeable depuis vos archives, mais il n\'est plus modifiable.'],
            'lignes' => MailGabarit::infosDossier($this->dossier),
            'action' => 'Voir le document',
            'url' => route('dossiers.show', $this->dossier),
        ]);
    }
}
