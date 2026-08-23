<?php

namespace App\Notifications;

use App\Models\DocumentVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NouvelleVersionImportee extends Notification
{
    use Queueable;

    public function __construct(public DocumentVersion $documentVersion)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $dossier = $this->documentVersion->dossier;

        return (new MailMessage)
            ->subject('Nouvelle version importée — ' . $dossier->identifiant_affiche)
            ->greeting('Bonjour ' . $notifiable->full_name . ',')
            ->line('Une nouvelle version (V' . $this->documentVersion->numero_version . ') du document "' . $dossier->titre . '" a été importée.')
            ->action('Voir le dossier', route('dossiers.show', $dossier));
    }
}
