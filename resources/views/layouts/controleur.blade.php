<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Contrôleur</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --accent: #047857;
            --accent-dark: #064e3b;
            --accent-bg: #f0fdf4;
        }
    </style>
    @include('layouts.partials.space-styles-dashboard')
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon"><i class="fas fa-hard-hat"></i></div>
                <div class="logo-text">
                    <h2>Fawaz BTP</h2>
                    <p>Espace Contrôleur</p>
                </div>
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="{{ route('controleur.dashboard') }}" class="nav-link {{ request()->routeIs('controleur.dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="{{ route('controleur.affectations.index') }}" class="nav-link {{ request()->routeIs('controleur.affectations*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i><span>Mes affectations</span></a></li>
            <li class="nav-item"><a href="{{ route('dossiers.index') }}" class="nav-link {{ request()->routeIs('dossiers.*') ? 'active' : '' }}"><i class="fas fa-folder-open"></i><span>Tous les dossiers</span></a></li>
            <li class="nav-item"><a href="{{ route('events.index') }}" class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}"><i class="fas fa-calendar-days"></i><span>Événements</span></a></li>
            <div class="nav-divider"></div>
            <li class="nav-item"><a href="{{ route('profile.edit') }}" class="nav-link"><i class="fas fa-user-cog"></i><span>Mon profil</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1 class="page-title">@yield('title', 'Dashboard Contrôleur')</h1>
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->nom, 0, 1)) }}</div>
                <div><div class="user-name">{{ Auth::user()->full_name }}</div><div class="user-role">{{ Auth::user()->role }}</div></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i></button></form>
            </div>
        </div>
        <div class="content-wrapper">
            @if(session('success'))<div style="background:#d4edda; color:#155724; padding:12px 20px; border-radius:10px; margin-bottom:20px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
            @if(session('error'))<div style="background:#f8d7da; color:#721c24; padding:12px 20px; border-radius:10px; margin-bottom:20px;"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>@endif
            @yield('content')
        </div>
    </div>
</body>
</html>
