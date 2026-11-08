@extends(auth()->user()->spaceLayout())

@section('title', 'Notifications')

@section('content')
<div class="page-header">
    <h1>Notifications</h1>
    @if(auth()->user()->unreadNotifications()->count())
        <form method="POST" action="{{ route('notifications.tout') }}">@csrf<button class="btn" type="submit">Tout marquer comme lu</button></form>
    @endif
</div>

<div class="table-container">
    @forelse($notifications as $n)
        <a href="{{ route('notifications.lire', $n->id) }}" class="row-link" style="display:flex; gap:14px; align-items:center;">
            <span style="width:10px; height:10px; border-radius:50%; flex-shrink:0; background:{{ $n->read_at ? 'transparent' : 'var(--accent)' }}; border:2px solid {{ $n->read_at ? 'var(--line)' : 'var(--accent)' }};"></span>
            <span style="flex:1; font-weight:{{ $n->read_at ? '500' : '800' }}; color:{{ $n->read_at ? 'var(--muted)' : 'var(--ink)' }};">{{ $n->data['message'] ?? 'Notification' }}</span>
            <small style="color:var(--muted); white-space:nowrap;">{{ $n->created_at->diffForHumans() }}</small>
        </a>
    @empty
        <div class="empty-state">Aucune notification pour l'instant.</div>
    @endforelse
    <div style="margin-top:14px;">{{ $notifications->links() }}</div>
</div>
@endsection
