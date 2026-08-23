<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Admin</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
      <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --accent: #ff8c00;
            --accent-dark: #ff6b00;
            --accent-bg: rgba(255,140,0,0.1);
        }
    </style>
    @include('layouts.partials.space-styles-dashboard')

    <style>
        /* Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255,140,0,0.15);
        }

        .stat-info h3 {
            font-size: 14px;
            color: #666;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: #1a1a1a;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: rgba(255,140,0,0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #ff8c00;
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff8c00, #ff6b00);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
            display: block;
        }

        @media (min-width: 768px) {
            table {
                display: table;
            }
        }

        th {
            text-align: left;
            padding: 15px;
            background: #f8f9fa;
            color: #555;
            font-weight: 600;
            font-size: 13px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-admin { background: rgba(255,68,68,0.1); color: #ff4444; }
        .badge-chef { background: rgba(255,140,0,0.1); color: #ff8c00; }
        .badge-controleur { background: rgba(52,211,153,0.1); color: #34d399; }
        .badge-actif { background: rgba(52,211,153,0.1); color: #34d399; }
        .badge-inactif { background: rgba(239,68,68,0.1); color: #ef4444; }

        .btn-icon {
            padding: 6px 10px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            margin: 0 3px;
            display: inline-block;
        }

        .btn-warning {
            background: #ffc107;
            color: #000;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
        }

        /* Alert */
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        /* Responsive (spécifique aux cartes/tableaux admin) */
        @media (max-width: 768px) {
            .stats-grid { gap: 15px; }
            .stat-card { padding: 15px; }
            .stat-number { font-size: 24px; }
            .table-header { flex-direction: column; align-items: flex-start; }
        }

        /* Loading state */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="logo-text">
                    <h2>Fawaz BTP</h2>
                    <p>Administration</p>
                </div>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.lots') }}" class="nav-link {{ request()->routeIs('admin.lots*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i>
                    <span>Gestion des lots</span>
                </a>
            </li>
            <li class="nav-item">
    <a href="{{ route('admin.document-types.index') }}" class="nav-link {{ request()->routeIs('admin.document-types*') ? 'active' : '' }}">
        <i class="fas fa-file-alt"></i>
        <span>Types de documents</span>
    </a>
</li>
            <li class="nav-item">
                <a href="{{ route('events.index') }}" class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-days"></i>
                    <span>Événements</span>
                </a>
            </li>
            <div class="nav-divider"></div>
            <li class="nav-item">
                <a href="{{ route('profile.edit') }}" class="nav-link">
                    <i class="fas fa-user-cog"></i>
                    <span>Mon profil</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->nom, 0, 1)) }}
                    </div>
                    <div>
                        <div class="user-name">{{ Auth::user()->full_name }}</div>
                        <div class="user-role">{{ Auth::user()->role }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="content-wrapper">
          

            @yield('content')
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');

        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('active')) {
                if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && sidebar) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>
