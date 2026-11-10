<?php

namespace App\Notifications;

use App\Models\DocumentTransmission;
use Illuminate\Bus\Queueable;
use App\Support\MailGabarit;
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

        return MailGabarit::message('Document transmis - ' . $dossier->identifiant_affiche, [
            'titre' => 'Document transmis',
            'sous_titre' => 'Un document a été partagé avec vous',
            'intro' => ['Bonjour <strong>' . MailGabarit::e($notifiable->full_name) . '</strong>, <strong>' . MailGabarit::e($this->transmission->emetteur?->full_name) . '</strong> vous a transmis le document ci-dessous. Vous pouvez le consulter et, à votre tour, le transmettre.'],
            'lignes' => MailGabarit::infosDossier($dossier),
            'note_titre' => 'Message de l\'émetteur',
            'note' => $this->transmission->commentaire,
            'action' => 'Consulter le document',
            'url' => route('dossiers.show', $dossier),
        ]);
    }
}
