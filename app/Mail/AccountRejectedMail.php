<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $raison;

    public function __construct(User $user, string $raison)
    {
        $this->user = $user;
        $this->raison = $raison;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre compte FawazBTP a été refusé',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-rejected',
            with: [
                'user' => $this->user,
                'raison' => $this->raison,
            ]
        );
    }
}
