<?php

namespace App\Mail;

use App\Models\DocumentVersion;
use App\Models\Dossier;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentTransmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public DocumentVersion $version;
    public Dossier $dossier;
    public User $emetteur;
    public string $mode;

    public function __construct(DocumentVersion $version, Dossier $dossier, User $emetteur, string $mode)
    {
        $this->version = $version;
        $this->dossier = $dossier;
        $this->emetteur = $emetteur;
        $this->mode = $mode;
    }

    public function envelope(): Envelope
    {
        $subject = $this->mode === 'validation'
            ? 'Nouveau document à valider - ' . $this->dossier->titre
            : 'Nouveau document transmis - ' . $this->dossier->titre;

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-transmitted',
            with: [
                'version' => $this->version,
                'dossier' => $this->dossier,
                'emetteur' => $this->emetteur,
                'mode' => $this->mode,
                'url' => route('entreprise.dossiers.show', $this->dossier->id),
            ]
        );
    }
}
