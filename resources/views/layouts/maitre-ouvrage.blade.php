<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Fawaz BTP'))</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --accent: #E91E8C;
            --accent-dark: #AD1457;
            --accent-bg: #fdf0f8;
        }
    </style>
    @include('layouts.partials.space-styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-building-shield"></i></div>
            <div>
                <div class="logo-text">Fawaz<span>BTP</span></div>
                <div class="logo-sub">Espace Maître d'Ouvrage</div>
            </div>
        </div>
        <ul class="menu">
            <li>
                <a href="{{ auth()->user()->isResponsableOrganisme() ? route('maitre_ouvrage.chef.dashboard') : route('maitre_ouvrage.collaborateur.dashboard') }}"
                   class="{{ request()->routeIs('maitre_ouvrage.chef.dashboard') || request()->routeIs('maitre_ouvrage.collaborateur.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-folder-open"></i> Documents de mon lot
                </a>
            </li>
            <li>
                <a href="{{ route('maitre_ouvrage.archives') }}"
                   class="{{ request()->routeIs('maitre_ouvrage.archives') ? 'active' : '' }}">
                    <i class="fas fa-box-archive"></i> Documents archivés
                </a>
            </li>
            <li>
                <a href="{{ route('events.index') }}"
                   class="{{ request()->routeIs('events.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-days"></i> Événements
                </a>
            </li>
            <li>
                <a href="{{ route('profile.edit') }}"
                   class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i> Mon profil
                </a>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" style="background:none; border:none; cursor:pointer; display:flex; align-items:center; gap:12px; padding:12px 16px; width:100%; border-radius:10px; color:#dc2626; font-size:14px; font-weight:500; font-family:inherit;">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </button>
                </form>
            </li>
        </ul>
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nom, 0, 1)) }}</div>
            <div>
                <div class="name">{{ auth()->user()->full_name }}</div>
                <div class="role">Maître d'Ouvrage</div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:14px; color:#888;">
                    <i class="fas fa-calendar-alt"></i> {{ now()->format('d/m/Y') }}
                </span>
            </div>
        </div>
        @if(session('success'))
            <div style="background:#d4edda; color:#155724; padding:12px 20px; border-radius:10px; margin-bottom:20px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @yield('content')
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('active');
        }
    </script>
</body>
</html>
