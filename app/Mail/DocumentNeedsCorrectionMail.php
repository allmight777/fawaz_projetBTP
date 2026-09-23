<?php

namespace App\Mail;

use App\Models\DocumentVersion;
use App\Models\Dossier;
use App\Models\User;
use App\Models\DocumentDecision;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentNeedsCorrectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public DocumentVersion $version;
    public Dossier $dossier;
    public User $controleur;   // le chef du Bureau de Contrôle qui a décidé
    public ?DocumentDecision $decision;

    public function __construct(DocumentVersion $version, Dossier $dossier, User $controleur, ?DocumentDecision $decision = null)
    {
        $this->version   = $version;
        $this->dossier   = $dossier;
        $this->controleur = $controleur;
        $this->decision  = $decision;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Correction demandée - ' . $this->dossier->titre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-needs-correction',
            with: [
                'version'    => $this->version,
                'dossier'    => $this->dossier,
                'controleur' => $this->controleur,
                'decision'   => $this->decision,
                'url'        => route('login'),
            ]
        );
    }
}
