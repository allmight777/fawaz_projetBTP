<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /** Marque comme lue puis ouvre la cible de la notification. */
    public function lire(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;
        // Uniquement des liens internes : jamais de redirection vers un domaine externe.
        $interne = $url && str_starts_with($url, url('/'));

        return $interne ? redirect($url) : redirect()->route('notifications.index');
    }

    public function toutLire()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }
}
