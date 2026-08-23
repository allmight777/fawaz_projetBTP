<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $destinataire;

    public function __construct(Event $event, User $destinataire)
    {
        $this->event = $event;
        $this->destinataire = $destinataire;
    }

    public function build()
    {
        $autresParticipants = $this->event->participants
            ->reject(fn ($p) => $p->id === $this->destinataire->id);

        return $this->subject('Invitation : ' . $this->event->titre)
            ->view('emails.event-invitation')
            ->with([
                'event' => $this->event,
                'destinataire' => $this->destinataire,
                'autresParticipants' => $autresParticipants,
            ]);
    }
}
