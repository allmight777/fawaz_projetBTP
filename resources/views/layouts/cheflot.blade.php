<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Chef Lot</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0fdf4;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100%;
            background: linear-gradient(180deg, #064e3b 0%, #047857 100%);
            z-index: 100;
            transition: all 0.3s ease;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #047857;
        }

        .logo-text h2 { color: white; font-size: 20px; font-weight: 700; }
        .logo-text p { color: #86efac; font-size: 11px; font-weight: 500; }

        .nav-menu { padding: 0 15px; }
        .nav-item { list-style: none; margin-bottom: 8px; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #bbf7d0;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s;
            font-weight: 500;
        }
        .nav-link i { width: 22px; font-size: 18px; }
        .nav-link:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-link.active { background: white; color: #047857; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .nav-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 15px 0; }

        /* Main Content */
        .main-content { margin-left: 280px; transition: all 0.3s ease; }

        /* Top Bar */
        .top-bar {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 99;
        }
        .page-title { font-size: 24px; font-weight: 700; color: #064e3b; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 15px;
            background: #f0fdf4;
            border-radius: 30px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #064e3b, #047857);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }
        .user-name { font-weight: 600; color: #064e3b; }
        .user-role { font-size: 12px; color: #047857; }
        .logout-btn {
            background: none;
            border: none;
            color: #047857;
            cursor: pointer;
            font-size: 20px;
        }

        .content-wrapper { padding: 30px; }

        /* Mobile */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon"><i class="fas fa-hard-hat"></i></div>
                <div class="logo-text">
                    <h2>Fawaz BTP</h2>
                    <p>Espace Chef de Lot</p>
                </div>
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="{{ route('cheflot.dashboard') }}" class="nav-link {{ request()->routeIs('cheflot.dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="{{ route('cheflot.demandes') }}" class="nav-link {{ request()->routeIs('cheflot.demandes*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i><span>Demandes</span></a></li>
            <li class="nav-item"><a href="{{ route('cheflot.controleurs') }}" class="nav-link {{ request()->routeIs('cheflot.controleurs') ? 'active' : '' }}"><i class="fas fa-users"></i><span>Contrôleurs</span></a></li>
            <li class="nav-item"><a href="{{ route('cheflot.statistiques') }}" class="nav-link {{ request()->routeIs('cheflot.statistiques') ? 'active' : '' }}"><i class="fas fa-chart-line"></i><span>Statistiques</span></a></li>
            <li class="nav-item">
    <a href="{{ route('cheflot.historique.global') }}" class="nav-link {{ request()->routeIs('cheflot.historique.global') ? 'active' : '' }}">
        <i class="fas fa-history"></i><span>Historique global</span>
    </a>
</li>
            <div class="nav-divider"></div>
            <li class="nav-item"><a href="{{ route('profile.edit') }}" class="nav-link"><i class="fas fa-user-cog"></i><span>Mon profil</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1 class="page-title">@yield('title', 'Dashboard Chef de Lot')</h1>
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
    <script>
        document.getElementById('mobileToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
