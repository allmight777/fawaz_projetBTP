<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') · {{ config('app.name', 'Fawaz BTP') }}</title>
    <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Tailwind + Alpine : certaines pages (dossiers, profil, événements) en dépendent encore --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}?v={{ @filemtime(public_path('css/ui.css')) }}">
    @stack('head')
</head>
@php
    $authUser = auth()->user();
    $menu = \App\Support\SpaceMenu::pour($space ?? 'entreprise', $authUser);
    $initiales = mb_strtoupper(mb_substr($authUser->prenom ?: '', 0, 1) . mb_substr($authUser->nom ?: '', 0, 1)) ?: '?';
@endphp
<body style="--accent: {{ $menu['accent'] }}">

<div class="pillbar">
    <div class="logo"><b>Fawaz<span>BTP</span></b></div>

    <nav>
        @foreach($menu['items'] as $item)
            <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'on' : '' }}">{{ $item['label'] }}@if(!empty($item['badge']))<em class="bubble">{{ $item['badge'] }}</em>@endif</a>
        @endforeach
    </nav>

    @php $nonLues = $authUser->unreadNotifications()->count(); @endphp
    <a href="{{ route('notifications.index') }}" class="notif {{ request()->routeIs('notifications.*') ? 'on' : '' }}" aria-label="Notifications">Notifications @if($nonLues)<em class="bubble">{{ $nonLues > 99 ? '99+' : $nonLues }}</em>@endif</a>

    <details class="u" id="userMenu">
        <summary>
            <i>{{ $initiales }}</i>
            <span>{{ $authUser->prenom ?: $authUser->nom }}<small>{{ \App\Support\SpaceMenu::roleLabel($authUser) }}</small></span>
        </summary>
        <div class="umenu">
            <a href="{{ route('profile.edit') }}">Mon profil</a>
            @if($authUser->peutViser())<a href="{{ route('cachets.edit') }}">Mes cachets</a>@endif
            <hr>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Se déconnecter</button></form>
        </div>
    </details>
</div>

<main class="page">
    @yield('content')
</main>

<script>
    // Ferme le menu utilisateur quand on clique ailleurs
    document.addEventListener('click', function (e) {
        var m = document.getElementById('userMenu');
        if (m && m.open && !m.contains(e.target)) m.open = false;
    });
</script>
<script src="{{ asset('js/tables.js') }}?v={{ @filemtime(public_path('js/tables.js')) }}" defer></script>
@stack('scripts')
</body>
</html>
