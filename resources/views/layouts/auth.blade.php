<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · {{ config('app.name', 'Fawaz BTP') }}</title>
    <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}?v={{ @filemtime(public_path('css/ui.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ @filemtime(public_path('css/auth.css')) }}">
</head>
<body>
<div class="auth-grid">
    <aside class="auth-side">
        {{-- Photo : Unsplash (licence libre) --}}
        <img src="{{ asset('images/auth-btp.jpg') }}" alt="" onerror="this.remove()">
        <div class="auth-brand"><b>Fawaz<span>BTP</span></b></div>
        <div class="auth-pitch">
            <div class="chips">
                <span><i>01</i>Workflows de validation</span>
                <span><i>02</i>Versions et historique</span>
                <span><i>03</i>Traçabilité complète</span>
            </div>
        </div>
    </aside>

    <main class="auth-main">
        <div class="auth-box">
            @include('partials.auth-deco')
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
