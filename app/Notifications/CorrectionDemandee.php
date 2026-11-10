<?php

namespace App\Notifications;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
use App\Support\MailGabarit;
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
        return MailGabarit::message('Correction demandée - ' . $this->dossier->identifiant_affiche, [
            'titre' => 'Correction demandée',
            'sous_titre' => 'Votre document doit être corrigé',
            'intro' => ['Bonjour <strong>' . MailGabarit::e($notifiable->full_name) . '</strong>, le document ci-dessous doit être corrigé avant d\'être validé.'],
            'lignes' => MailGabarit::infosDossier($this->dossier),
            'note_titre' => 'Observations',
            'note' => $this->commentaires,
            'ton' => 'bad',
            'action' => 'Voir le document',
            'url' => route('dossiers.show', $this->dossier),
            'action_note' => 'Consultez les observations puis soumettez une version corrigée.',
        ]);
    }
}
