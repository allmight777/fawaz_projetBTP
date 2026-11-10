<?php

namespace App\Notifications;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
use App\Support\MailGabarit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentValide extends Notification
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
        return MailGabarit::message('Document validé - ' . $this->dossier->identifiant_affiche, [
            'titre' => 'Document validé',
            'sous_titre' => 'Un visa a été accordé',
            'intro' => ['Bonjour <strong>' . MailGabarit::e($notifiable->full_name) . '</strong>, le document ci-dessous a reçu un visa et est validé.'],
            'lignes' => MailGabarit::infosDossier($this->dossier),
            'ton' => 'ok',
            'action' => 'Voir le document',
            'url' => route('dossiers.show', $this->dossier),
        ]);
    }
}
