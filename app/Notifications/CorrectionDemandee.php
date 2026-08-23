<?php

namespace App\Notifications;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CorrectionDemandee extends Notification
{
    use Queueable;

    public function __construct(public Dossier $dossier, public ?string $commentaires = null)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Correction demandée — ' . $this->dossier->identifiant_affiche)
            ->greeting('Bonjour ' . $notifiable->full_name . ',')
            ->line('Le document "' . $this->dossier->titre . '" (' . $this->dossier->identifiant_affiche . ') doit être corrigé.');

        if ($this->commentaires) {
            $message->line('Observations du Bureau de Contrôle : ' . $this->commentaires);
        }

        return $message->action('Voir le dossier', route('dossiers.show', $this->dossier));
    }
}
