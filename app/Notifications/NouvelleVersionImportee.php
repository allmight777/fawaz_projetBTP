<?php

namespace App\Notifications;

use App\Models\DocumentVersion;
use Illuminate\Bus\Queueable;
use App\Support\MailGabarit;
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

        return MailGabarit::message('Nouvelle version importée - ' . $dossier->identifiant_affiche, [
            'titre' => 'Nouvelle version importée',
            'sous_titre' => 'Le document a une nouvelle version',
            'intro' => ['Bonjour <strong>' . MailGabarit::e($notifiable->full_name) . '</strong>, une nouvelle version du document ci-dessous a été importée.'],
            'lignes' => MailGabarit::infosDossier($dossier, 'V' . $this->documentVersion->numero_version),
            'action' => 'Voir le document',
            'url' => route('dossiers.show', $dossier),
        ]);
    }
}
