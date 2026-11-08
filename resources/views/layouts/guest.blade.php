<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Fawaz BTP') }}</title>
    <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    {{-- Tailwind : les composants Breeze (champs, boutons) en dépendent encore --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}?v={{ @filemtime(public_path('css/ui.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ @filemtime(public_path('css/auth.css')) }}">
</head>
<body>
    <div class="auth-solo">
        <div style="width:100%;max-width:480px">
            <a href="{{ url('/') }}" class="auth-brand" style="display:block"><b>Fawaz<span>BTP</span></b></a>
            <div class="auth-card">
                @include('partials.auth-deco')
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
