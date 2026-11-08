<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** Alerte interne (bulle de notifications non lues) : stockée en base, sans envoi de mail. */
class Alerte extends Notification
{
    use Queueable;

    public function __construct(public string $message, public ?string $url = null)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return ['message' => $this->message, 'url' => $this->url];
    }

    /** Ne casse jamais le flux métier si l'alerte échoue. */
    public static function envoyer(?\App\Models\User $user, string $message, ?string $url = null): void
    {
        if (!$user) {
            return;
        }
        try {
            $user->notify(new self($message, $url));
        } catch (\Throwable $e) {
            \Log::warning('Alerte non enregistrée : ' . $e->getMessage());
        }
    }
}
