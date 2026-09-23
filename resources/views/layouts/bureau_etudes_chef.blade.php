<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Chef Bureau d\'Études')</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            display: flex;
            min-height: 100vh;
        }

        :root {
            --accent: #047857;
            --accent-dark: #064e3b;
            --accent-bg: #f0fdf4;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 280px;
            background: #064e3b;
            min-height: 100vh;
            padding: 30px 20px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }

        .sidebar-header .logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .logo-icon {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.15);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
        }
        .logo-text h2 {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .logo-text p {
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            font-weight: 500;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }
        .nav-item {
            margin-bottom: 4px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 12px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .nav-link i {
            width: 20px;
            font-size: 16px;
            text-align: center;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }
        .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .nav-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 20px 16px;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 280px;
            flex: 1;
            min-height: 100vh;
        }

        .top-bar {
            background: #fff;
            padding: 20px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 100;
            flex-wrap: wrap;
            gap: 15px;
        }
        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #064e3b;
            letter-spacing: -0.5px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--accent-bg);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }
        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
        }
        .user-role {
            font-size: 12px;
            color: #6b7280;
        }
        .logout-btn {
            background: none;
            border: none;
            color: #ef4444;
            font-size: 18px;
            cursor: pointer;
            padding: 8px;
            border-radius: 10px;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background: #fef2f2;
        }

        .content-wrapper {
            padding: 30px 35px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .top-bar {
                padding: 15px 20px;
            }
            .content-wrapper {
                padding: 20px;
            }
            .page-title {
                font-size: 18px;
            }
        }
    </style>
    @include('layouts.partials.space-styles-dashboard')
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon"><i class="fas fa-hard-hat"></i></div>
                <div class="logo-text">
                    <h2>Fawaz BTP</h2>
                    <p>Bureau d'Études - Chef</p>
                </div>
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('bureau_etudes.chef.dashboard') }}" class="nav-link {{ request()->routeIs('bureau_etudes.chef.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('bureau_etudes.chef.demandes') }}" class="nav-link {{ request()->routeIs('bureau_etudes.chef.demandes*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i><span>Demandes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('bureau_etudes.chef.controleurs') }}" class="nav-link {{ request()->routeIs('bureau_etudes.chef.controleurs*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i><span>Contrôleurs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('bureau_etudes.chef.statistiques') }}" class="nav-link {{ request()->routeIs('bureau_etudes.chef.statistiques*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i><span>Statistiques</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('bureau_etudes.chef.historique') }}" class="nav-link {{ request()->routeIs('bureau_etudes.chef.historique*') ? 'active' : '' }}">
                    <i class="fas fa-history"></i><span>Historique</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('bureau_etudes.chef.archives') }}" class="nav-link {{ request()->routeIs('bureau_etudes.chef.archives*') ? 'active' : '' }}">
                    <i class="fas fa-box-archive"></i><span>Archives</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('events.index') }}" class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-days"></i><span>Événements</span>
                </a>
            </li>
            <div class="nav-divider"></div>
            <li class="nav-item">
                <a href="{{ route('profile.edit') }}" class="nav-link">
                    <i class="fas fa-user-cog"></i><span>Mon profil</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="top-bar">
            <h1 class="page-title">@yield('title', 'Dashboard Chef Bureau d\'Études')</h1>
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->nom ?? 'U', 0, 1)) }}</div>
                <div>
                    <div class="user-name">{{ Auth::user()->full_name ?? Auth::user()->name ?? 'Utilisateur' }}</div>
                    <div class="user-role">{{ Auth::user()->role ?? 'Chef BE' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        </div>
        <div class="content-wrapper">
            @if(session('success'))
                <div style="background:#d4edda; color:#155724; padding:12px 20px; border-radius:10px; margin-bottom:20px;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="background:#f8d7da; color:#721c24; padding:12px 20px; border-radius:10px; margin-bottom:20px;">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </div>
    </div>

    <!-- Mobile toggle script -->
    <script>
        // Ajouter un bouton toggle pour mobile dans le top-bar
        document.addEventListener('DOMContentLoaded', function() {
            const topBar = document.querySelector('.top-bar');
            const toggleBtn = document.createElement('button');
            toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
            toggleBtn.style.cssText = `
                background: none;
                border: none;
                font-size: 22px;
                color: #064e3b;
                cursor: pointer;
                padding: 8px;
                display: none;
            `;
            toggleBtn.id = 'mobileToggle';
            topBar.insertBefore(toggleBtn, topBar.firstChild);

            // Afficher le bouton sur mobile
            const style = document.createElement('style');
            style.textContent = `
                @media (max-width: 768px) {
                    #mobileToggle { display: block !important; }
                }
            `;
            document.head.appendChild(style);

            document.getElementById('mobileToggle').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
            });
        });
    </script>
</body>
</html>
