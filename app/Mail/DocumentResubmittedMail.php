<?php

namespace App\Mail;

use App\Models\DocumentVersion;
use App\Models\Dossier;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentResubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public DocumentVersion $version;
    public Dossier $dossier;
    public User $emetteur;

    public function __construct(DocumentVersion $version, Dossier $dossier, User $emetteur)
    {
        $this->version = $version;
        $this->dossier = $dossier;
        $this->emetteur = $emetteur;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔁 Document corrigé et resoumis (V' . $this->version->numero_version . ') - ' . $this->dossier->titre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-resubmitted',
            with: [
                'version' => $this->version,
                'dossier' => $this->dossier,
                'emetteur' => $this->emetteur,
                'url' => route('login'),
            ]
        );
    }
}
