<?php

namespace App\Notifications;

use App\Models\DocumentTransmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentTransmis extends Notification
{
    use Queueable;

    public function __construct(public DocumentTransmission $transmission)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $dossier = $this->transmission->dossier;

        return (new MailMessage)
            ->subject('Document transmis — ' . $dossier->identifiant_affiche)
            ->greeting('Bonjour ' . $notifiable->full_name . ',')
            ->line('Le document "' . $dossier->titre . '" (' . $dossier->identifiant_affiche . ') vous a été transmis par ' . $this->transmission->emetteur->full_name . '.')
            ->action('Voir le dossier', route('dossiers.show', $dossier));
    }
}
