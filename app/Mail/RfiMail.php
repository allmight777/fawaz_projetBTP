<?php

namespace App\Mail;

use App\Models\DemandeInformation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mails du cycle d'une demande d'information : demandee → accordee | refusee → recue. */
class RfiMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeInformation $demande, public string $etape)
    {
    }

    public function envelope(): Envelope
    {
        $objet = $this->demande->objet;

        return new Envelope(subject: match ($this->etape) {
            'demandee' => 'Demande d\'information - ' . $objet,
            'accordee' => 'Accès accordé - ' . $objet,
            'refusee' => 'Demande d\'information refusée - ' . $objet,
            'recue' => 'Réception confirmée - ' . $objet,
        });
    }

    public function content(): Content
    {
        return new Content(view: 'emails.rfi', with: [
            'demande' => $this->demande,
            'etape' => $this->etape,
            'url' => route('rfi.show', $this->demande),
        ]);
    }
}
