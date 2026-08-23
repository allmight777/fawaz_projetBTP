@extends($layout)

@section('title', $event->titre)

@section('content')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px; }
    .btn-back {
        background: #e5e7eb; color: #333; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; font-size: 13px;
        text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-back:hover { background: #d1d5db; }

    .card { background: white; border-radius: 20px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .card h3 { font-size: 16px; font-weight: 700; color: #1a1a1a; margin-bottom: 16px; display:flex; align-items:center; gap:8px; }
    .card h3 i { color: var(--accent, #2563eb); }

    .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-row .label { color: #6b7280; }
    .info-row .value { font-weight: 600; color: #1a1a1a; text-align: right; }

    .participant-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
    .participant-item:last-child { border-bottom: none; }

    .badge-statut { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-confirme { background: #d1fae5; color: #059669; }
    .badge-en_attente { background: #fef3c7; color: #d97706; }
    .badge-decline { background: #fee2e2; color: #dc2626; }

    .rsvp-buttons { display: flex; gap: 12px; }
    .btn-rsvp {
        flex: 1; border: none; padding: 12px; border-radius: 10px; font-weight: 600; font-size: 13px; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-confirm { background: #d1fae5; color: #059669; }
    .btn-confirm:hover { background: #a7f3d0; }
    .btn-decline { background: #fee2e2; color: #dc2626; }
    .btn-decline:hover { background: #fecaca; }

    .btn-danger-outline {
        background: none; border: 1px solid #fecaca; color: #dc2626; padding: 9px 16px; border-radius: 10px; font-weight: 600; font-size: 13px; cursor: pointer;
    }
    .btn-edit {
        background: var(--accent-bg, #eff6ff); color: var(--accent, #2563eb); border: none; padding: 9px 16px; border-radius: 10px;
        font-weight: 600; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    }
</style>

<div class="page-header">
    <h2 style="font-size:22px; font-weight:700;"><i class="fas fa-calendar-day" style="color:var(--accent,#2563eb);"></i> {{ $event->titre }}</h2>
    <a href="{{ route('events.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

@if(session('success'))
    <div style="background:#d4edda; color:#155724; padding:12px 20px; border-radius:10px; margin-bottom:20px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif

<div class="card">
    <h3><i class="fas fa-circle-info"></i> Détails</h3>
    <div class="info-row"><span class="label">Organisateur</span><span class="value">{{ $event->createur->full_name }}</span></div>
    <div class="info-row"><span class="label">Début</span><span class="value">{{ $event->date_debut->translatedFormat('d/m/Y à H:i') }}</span></div>
    @if($event->date_fin)
        <div class="info-row"><span class="label">Fin</span><span class="value">{{ $event->date_fin->translatedFormat('d/m/Y à H:i') }}</span></div>
    @endif
    @if($event->lieu)
        <div class="info-row"><span class="label">Lieu</span><span class="value">{{ $event->lieu }}</span></div>
    @endif
    @if($event->structures->isNotEmpty())
        <div class="info-row"><span class="label">Structure(s) visée(s)</span><span class="value">{{ $event->structures->pluck('nom')->join(', ') }}</span></div>
    @endif
    @if($event->description)
        <div class="info-row"><span class="label">Description</span><span class="value">{{ $event->description }}</span></div>
    @endif

    @if($estCreateur)
        <div style="margin-top:16px; display:flex; gap:10px;">
            <a href="{{ route('events.edit', $event) }}" class="btn-edit"><i class="fas fa-pen"></i> Modifier</a>
            <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Supprimer définitivement cet événement ?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger-outline"><i class="fas fa-trash"></i> Supprimer</button>
            </form>
        </div>
    @endif
</div>

@if($monInvitation && !$estCreateur)
<div class="card">
    <h3><i class="fas fa-reply"></i> Votre réponse</h3>
    <p style="font-size:13px; color:#6b7280; margin-bottom:14px;">Statut actuel :
        <span class="badge-statut badge-{{ $monInvitation->pivot->statut }}">
            @if($monInvitation->pivot->statut === 'confirme') Confirmé
            @elseif($monInvitation->pivot->statut === 'decline') Décliné
            @else En attente
            @endif
        </span>
    </p>
    <div class="rsvp-buttons">
        <form method="POST" action="{{ route('events.statut', $event) }}" style="flex:1;">
            @csrf
            <input type="hidden" name="statut" value="confirme">
            <button type="submit" class="btn-rsvp btn-confirm"><i class="fas fa-check"></i> Je serai présent(e)</button>
        </form>
        <form method="POST" action="{{ route('events.statut', $event) }}" style="flex:1;">
            @csrf
            <input type="hidden" name="statut" value="decline">
            <button type="submit" class="btn-rsvp btn-decline"><i class="fas fa-xmark"></i> Je ne pourrai pas venir</button>
        </form>
    </div>
</div>
@endif

<div class="card">
    <h3><i class="fas fa-users"></i> Participants ({{ $event->participants->count() }})</h3>
    @forelse($event->participants as $participant)
        <div class="participant-item">
            <span>{{ $participant->full_name }}</span>
            <span class="badge-statut badge-{{ $participant->pivot->statut }}">
                @if($participant->pivot->statut === 'confirme') Confirmé
                @elseif($participant->pivot->statut === 'decline') Décliné
                @else En attente
                @endif
            </span>
        </div>
    @empty
        <p style="color:#9ca3af; font-size:13px;">Aucun participant invité.</p>
    @endforelse
</div>
@endsection
