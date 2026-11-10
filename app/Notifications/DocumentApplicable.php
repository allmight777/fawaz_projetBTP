<?php

namespace App\Notifications;

use App\Models\Dossier;
use App\Support\MailGabarit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Un document a reçu tous ses visas et a été rendu applicable : il sert de référence au chantier. */
class DocumentApplicable extends Notification
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
        return MailGabarit::message('Document applicable - ' . $this->dossier->identifiant_affiche, [
            'titre' => 'Document applicable',
            'sous_titre' => 'Ce document sert désormais de référence',
            'intro' => ['Bonjour <strong>' . MailGabarit::e($notifiable->full_name) . '</strong>, tous les visas du document ci-dessous ont été obtenus et il a été rendu applicable. Il sert de référence à toutes les structures du chantier.'],
            'lignes' => MailGabarit::infosDossier($this->dossier),
            'ton' => 'ok',
            'action' => 'Voir le document',
            'url' => route('dossiers.show', $this->dossier),
            'action_note' => 'S\'il repasse à corriger, il cessera d\'être applicable.',
        ]);
    }
}
