@extends($layout)

@section('title', 'Événements')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<style>
    .events-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 14px;
    }
    .events-header h2 { font-size: 22px; font-weight: 700; color: #1a1a1a; margin: 0; }
    .events-header h2 i { color: var(--accent, #2563eb); margin-right: 8px; }
    .btn-new-event {
        background: linear-gradient(135deg, var(--accent, #2563eb), var(--accent-dark, #1a4fc4));
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.3s;
    }
    .btn-new-event:hover { transform: translateY(-2px); }

    .events-layout {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 1100px) {
        .events-layout { grid-template-columns: 1fr; }
    }

    .calendar-card, .list-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .list-card h3 { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 14px; }
    .list-card h3 i { color: var(--accent, #2563eb); margin-right: 6px; }

    .event-card {
        border: 1px solid #eef0f3;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 12px;
        text-decoration: none;
        display: block;
        color: inherit;
        transition: all 0.2s ease;
    }
    .event-card:hover { border-color: var(--accent, #2563eb); background: var(--accent-bg, #eff6ff); }
    .event-card .event-title { font-weight: 700; font-size: 14px; color: #1a1a1a; margin-bottom: 4px; }
    .event-card .event-meta { font-size: 12px; color: #6b7280; display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 6px; }
    .event-card .event-meta i { color: var(--accent, #2563eb); margin-right: 4px; }

    .badge-statut {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-organisateur { background: var(--accent-bg, #eff6ff); color: var(--accent, #2563eb); }
    .badge-confirme { background: #d1fae5; color: #059669; }
    .badge-en_attente { background: #fef3c7; color: #d97706; }
    .badge-decline { background: #fee2e2; color: #dc2626; }

    .empty-state { text-align: center; padding: 30px 10px; color: #9ca3af; font-size: 13px; }
    .empty-state i { font-size: 30px; display: block; margin-bottom: 10px; opacity: 0.4; }

    #calendar { max-width: 100%; }
    .fc { font-family: 'Inter', sans-serif; font-size: 13px; }
    .fc .fc-toolbar-title { font-size: 17px; font-weight: 700; color: #1a1a1a; }
    .fc .fc-button-primary {
        background: var(--accent, #2563eb);
        border-color: var(--accent, #2563eb);
    }
    .fc .fc-button-primary:hover { background: var(--accent-dark, #1a4fc4); border-color: var(--accent-dark, #1a4fc4); }
    .fc .fc-daygrid-day.fc-day-today { background: var(--accent-bg, #eff6ff); }
</style>

<div class="events-header">
    <h2><i class="fas fa-calendar-days"></i> Événements</h2>
    <a href="{{ route('events.create') }}" class="btn-new-event"><i class="fas fa-plus"></i> Nouvel événement</a>
</div>

@if(session('success'))
    <div style="background:#d4edda; color:#155724; padding:12px 20px; border-radius:10px; margin-bottom:20px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif

<div class="events-layout">
    <div class="calendar-card">
        <div id="calendar"></div>
    </div>

    <div class="list-card">
        <h3><i class="fas fa-list"></i> Mes événements à venir</h3>

        @forelse($aVenir as $event)
            <a href="{{ route('events.show', $event) }}" class="event-card">
                <div class="event-title">{{ $event->titre }}</div>
                <div class="event-meta">
                    <span><i class="fas fa-calendar-alt"></i> {{ $event->date_debut->translatedFormat('d/m/Y à H:i') }}</span>
                    @if($event->lieu)
                        <span><i class="fas fa-location-dot"></i> {{ $event->lieu }}</span>
                    @endif
                </div>
                @if($event->createur_id === auth()->id())
                    <span class="badge-statut badge-organisateur"><i class="fas fa-star"></i> Organisateur</span>
                @else
                    @php $monStatut = $event->participants->firstWhere('id', auth()->id())?->pivot->statut ?? 'en_attente'; @endphp
                    <span class="badge-statut badge-{{ $monStatut }}">
                        @if($monStatut === 'confirme') <i class="fas fa-check"></i> Confirmé
                        @elseif($monStatut === 'decline') <i class="fas fa-xmark"></i> Décliné
                        @else <i class="fas fa-hourglass-half"></i> En attente
                        @endif
                    </span>
                @endif
            </a>
        @empty
            <div class="empty-state">
                <i class="fas fa-calendar-xmark"></i>
                Aucun événement à venir.
            </div>
        @endforelse

        @if($passes->count() > 0)
        <h3 style="margin-top:24px;"><i class="fas fa-clock-rotate-left"></i> Événements passés</h3>
        @foreach($passes->take(5) as $event)
            <a href="{{ route('events.show', $event) }}" class="event-card" style="opacity:0.7;">
                <div class="event-title">{{ $event->titre }}</div>
                <div class="event-meta">
                    <span><i class="fas fa-calendar-alt"></i> {{ $event->date_debut->translatedFormat('d/m/Y') }}</span>
                </div>
            </a>
        @endforeach
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            height: 'auto',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
            events: '{{ route('events.data') }}',
        });
        calendar.render();
    });
</script>
@endsection
