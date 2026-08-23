<?php

namespace App\Notifications;

use App\Models\DocumentAssignment;
use Illuminate\Bus\Queueable;
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

        $message = (new MailMessage)
            ->subject('Document affecté pour analyse — ' . $dossier->identifiant_affiche)
            ->greeting('Bonjour ' . $notifiable->full_name . ',')
            ->line('Vous avez été affecté(e) à l\'analyse du document "' . $dossier->titre . '" (' . $dossier->identifiant_affiche . ').');

        if ($this->documentAssignment->date_limite) {
            $message->line('**Délai imparti : vous devez rendre votre analyse avant le ' . $this->documentAssignment->date_limite->translatedFormat('d/m/Y à H:i') . '.**');
        }

        return $message
            ->action('Analyser le document', route('controleur.affectations.show', $this->documentAssignment))
            ->line('Merci de compléter votre analyse dans les meilleurs délais.');
    }
}
