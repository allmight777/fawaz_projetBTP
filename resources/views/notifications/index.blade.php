@extends(auth()->user()->spaceLayout())

@section('title', 'Notifications')

@section('content')
@php
    $nonLues = auth()->user()->unreadNotifications()->count();

    // Icône et ton d'une notification, déduits de son message.
    $style = function (string $message): array {
        $m = mb_strtolower($message);
        return match (true) {
            str_contains($m, 'applicable') => ['stamp', 'ok'],
            str_contains($m, 'valid') => ['circle-check', 'ok'],
            str_contains($m, 'correction') || str_contains($m, 'corrig') => ['circle-exclamation', 'warn'],
            str_contains($m, 'archiv') => ['box-archive', 'neutre'],
            str_contains($m, 'à viser') || str_contains($m, 'visa') => ['file-signature', 'accent'],
            str_contains($m, 'transf') => ['right-left', 'accent'],
            str_contains($m, 'transmis') || str_contains($m, 'partag') => ['share-nodes', 'accent'],
            str_contains($m, 'affect') => ['user-check', 'accent'],
            str_contains($m, 'version') => ['file-circle-plus', 'accent'],
            str_contains($m, 'information') => ['circle-question', 'accent'],
            default => ['bell', 'accent'],
        };
    };

    $jour = function ($date): string {
        $d = $date->copy()->locale('fr');
        return match (true) {
            $d->isToday() => "Aujourd'hui",
            $d->isYesterday() => 'Hier',
            default => ucfirst($d->translatedFormat('l j F Y')),
        };
    };
    $groupes = $notifications->getCollection()->groupBy(fn ($n) => $jour($n->created_at));
@endphp

<div class="page-header">
    <h1>Notifications</h1>
    @if($nonLues)
        <form method="POST" action="{{ route('notifications.tout') }}">@csrf<button class="btn" type="submit"><i class="fas fa-check-double"></i> Tout marquer comme lu</button></form>
    @endif
</div>

@if($notifications->count())
    @foreach($groupes as $libelleJour => $liste)
        <div class="nt-jour">{{ $libelleJour }}</div>
        <div class="nt-groupe">
            @foreach($liste as $n)
                @php
                    $message = $n->data['message'] ?? 'Notification';
                    [$icone, $ton] = $style($message);
                    // « Qui a fait quoi : document » → action en titre, document en pastille.
                    $morceaux = explode(' : ', $message, 2);
                    $action = $morceaux[0];
                    $document = $morceaux[1] ?? null;
                @endphp
                <a href="{{ route('notifications.lire', $n->id) }}" class="nt-carte {{ $n->read_at ? 'lue' : 'non-lue' }} ton-{{ $ton }}">
                    <span class="nt-icone"><i class="fas fa-{{ $icone }}"></i></span>
                    <span class="nt-corps">
                        <strong>{{ $action }}</strong>
                        @if($document)<span class="nt-doc"><i class="fas fa-file-lines"></i> {{ \Illuminate\Support\Str::limit($document, 70) }}</span>@endif
                    </span>
                    <span class="nt-heure" title="{{ $n->created_at->format('d/m/Y à H:i') }}">
                        {{ $n->created_at->format('H:i') }}
                        <small>{{ $n->created_at->locale('fr')->diffForHumans() }}</small>
                    </span>
                    @unless($n->read_at)<span class="nt-pastille" aria-label="Non lue"></span>@endunless
                </a>
            @endforeach
        </div>
    @endforeach

    <div style="margin-top:18px;">{{ $notifications->links() }}</div>
@else
    <div class="table-container">
        <div class="nt-vide">
            <span class="nt-vide-ico"><i class="fas fa-bell-slash"></i></span>
            <strong>Aucune notification pour l'instant</strong>
            <p>Les alertes sur vos documents, visas et demandes apparaîtront ici.</p>
        </div>
    </div>
@endif
@endsection
