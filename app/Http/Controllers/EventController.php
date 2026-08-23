<?php

namespace App\Http\Controllers;

use App\Mail\EventInvitationMail;
use App\Models\Event;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EventController extends Controller
{
    // Calendrier + liste "mes événements" à venir
    public function index()
    {
        $user = Auth::user();

        $evenements = $this->evenementsVisibles($user)
            ->with(['createur', 'structures', 'participants'])
            ->orderBy('date_debut')
            ->get();

        $aVenir = $evenements->filter(fn ($e) => $e->date_debut->isFuture() || $e->date_debut->isToday());
        $passes = $evenements->filter(fn ($e) => $e->date_debut->isPast() && !$e->date_debut->isToday());

        return view('events.index', [
            'layout' => $user->spaceLayout(),
            'aVenir' => $aVenir->sortBy('date_debut'),
            'passes' => $passes->sortByDesc('date_debut'),
        ]);
    }

    // Flux JSON consommé par FullCalendar
    public function data(Request $request)
    {
        $user = Auth::user();

        $evenements = $this->evenementsVisibles($user)->get();

        return response()->json($evenements->map(function (Event $event) use ($user) {
            $estCreateur = $event->createur_id === $user->id;

            return [
                'id' => $event->id,
                'title' => $event->titre,
                'start' => $event->date_debut->toIso8601String(),
                'end' => $event->date_fin?->toIso8601String(),
                'url' => route('events.show', $event),
                'color' => $estCreateur ? 'var(--accent-dark)' : 'var(--accent)',
            ];
        }));
    }

    public function create()
    {
        $user = Auth::user();

        return view('events.create', [
            'layout' => $user->spaceLayout(),
            'peutViserStructure' => $user->isChef(),
            'structures' => Structure::actives()->orderBy('nom')->get(),
            'utilisateurs' => User::where('id', '!=', $user->id)
                ->where('actif', true)
                ->with('structure')
                ->orderBy('nom')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'lieu' => 'nullable|string|max:255',
            'destinataires' => 'nullable|array',
            'destinataires.*' => 'exists:users,id',
            'structures' => 'nullable|array',
            'structures.*' => 'exists:structures,id',
        ]);

        $structureIds = $validated['structures'] ?? [];

        if (empty($validated['destinataires']) && empty($structureIds)) {
            return back()->withInput()->withErrors([
                'destinataires' => 'Sélectionnez au moins une personne ou une structure à inviter.',
            ]);
        }

        if (!empty($structureIds)) {
            abort_unless($user->isChef(), 403, 'Seul un chef peut viser une structure entière.');
        }

        $event = Event::create([
            'titre' => $validated['titre'],
            'description' => $validated['description'] ?? null,
            'date_debut' => $validated['date_debut'],
            'date_fin' => $validated['date_fin'] ?? null,
            'lieu' => $validated['lieu'] ?? null,
            'createur_id' => $user->id,
        ]);

        $event->structures()->sync($structureIds);

        $individuIds = collect($validated['destinataires'] ?? [])->map(fn ($id) => (int) $id);
        $membresStructures = empty($structureIds)
            ? collect()
            : User::whereIn('structure_id', $structureIds)->where('actif', true)->pluck('id');

        $destinataireIds = $individuIds->merge($membresStructures)
            ->unique()
            ->reject(fn ($id) => $id === $user->id);

        $statuts = $destinataireIds->mapWithKeys(fn ($id) => [$id => ['statut' => 'en_attente']]);
        $event->participants()->sync($statuts);

        $event->load('participants');

        foreach ($event->participants as $destinataire) {
            try {
                Mail::to($destinataire->email)->send(new EventInvitationMail($event, $destinataire));
            } catch (\Exception $e) {
                report($e);
            }
        }

        return redirect()->route('events.show', $event)->with('success', 'Événement créé et invitations envoyées.');
    }

    public function show(Event $event)
    {
        $user = Auth::user();
        abort_unless($this->peutVoir($event, $user), 403);

        $event->load(['createur', 'structures', 'participants']);

        $monInvitation = $event->participants->firstWhere('id', $user->id);

        return view('events.show', [
            'layout' => $user->spaceLayout(),
            'event' => $event,
            'monInvitation' => $monInvitation,
            'estCreateur' => $event->createur_id === $user->id,
        ]);
    }

    public function edit(Event $event)
    {
        $user = Auth::user();
        abort_unless($event->createur_id === $user->id || $user->isAdmin(), 403);

        return view('events.edit', [
            'layout' => $user->spaceLayout(),
            'event' => $event,
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $user = Auth::user();
        abort_unless($event->createur_id === $user->id || $user->isAdmin(), 403);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'lieu' => 'nullable|string|max:255',
        ]);

        $event->update($validated);

        return redirect()->route('events.show', $event)->with('success', 'Événement mis à jour.');
    }

    public function destroy(Event $event)
    {
        $user = Auth::user();
        abort_unless($event->createur_id === $user->id || $user->isAdmin(), 403);

        $event->delete();

        return redirect()->route('events.index')->with('success', 'Événement supprimé.');
    }

    // Un invité confirme ou décline sa présence
    public function updateStatut(Request $request, Event $event)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'statut' => 'required|in:confirme,decline',
        ]);

        abort_unless($event->participants->contains($user->id) || $event->participants()->where('user_id', $user->id)->exists(), 403);

        $event->participants()->updateExistingPivot($user->id, ['statut' => $validated['statut']]);

        return back()->with('success', 'Votre réponse a été enregistrée.');
    }

    private function evenementsVisibles(User $user)
    {
        return Event::where('createur_id', $user->id)
            ->orWhereHas('participants', fn ($q) => $q->where('users.id', $user->id));
    }

    private function peutVoir(Event $event, User $user): bool
    {
        if ($event->createur_id === $user->id || $user->isAdmin()) {
            return true;
        }

        return $event->participants()->where('user_id', $user->id)->exists();
    }
}
