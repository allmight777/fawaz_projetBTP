<?php

namespace App\Notifications;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
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
        return (new MailMessage)
            ->subject('Nouveau document soumis — ' . $this->dossier->identifiant_affiche)
            ->greeting('Bonjour ' . $notifiable->full_name . ',')
            ->line('Le document "' . $this->dossier->titre . '" (' . $this->dossier->identifiant_affiche . ') a été soumis pour analyse.')
            ->action('Voir le dossier', route('dossiers.show', $this->dossier))
            ->line('Merci de traiter cette demande dans les meilleurs délais.');
    }
}
