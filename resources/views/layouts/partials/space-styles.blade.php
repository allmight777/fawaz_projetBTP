{{-- Style commun à tous les espaces (Entreprise, Admin, Contrôleur, Chef de Lot, Maître d'Ouvrage).
     Seule la couleur d'accent change, via les variables CSS --accent/--accent-dark/--accent-bg
     définies par chaque layout avant cet include. --}}
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Inter', sans-serif;
        background: #ffffff;
        color: #1a1a1a;
        display: flex;
        min-height: 100vh;
    }
    /* Sidebar */
    .sidebar {
        width: 260px;
        background: white;
        border-right: 1px solid #e0e8f0;
        padding: 24px 16px;
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 100;
        overflow-y: auto;
    }
    .sidebar .logo {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 24px;
        border-bottom: 1px solid #e0e8f0;
        margin-bottom: 24px;
    }
    .sidebar .logo .logo-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        flex-shrink: 0;
    }
    .sidebar .logo .logo-text { font-size: 20px; font-weight: 800; color: #1a1a1a; }
    .sidebar .logo .logo-text span { color: var(--accent); }
    .sidebar .logo .logo-sub { font-size: 11px; color: #888; }
    .sidebar .menu { list-style: none; flex: 1; }
    .sidebar .menu li { margin-bottom: 4px; }
    .sidebar .menu li a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 10px;
        color: #555;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .sidebar .menu li a:hover { background: var(--accent-bg); color: var(--accent); }
    .sidebar .menu li a.active {
        background: var(--accent-bg);
        color: var(--accent);
        font-weight: 600;
    }
    .sidebar .menu li a i { width: 20px; text-align: center; }
    .sidebar .user-info {
        padding-top: 20px;
        border-top: 1px solid #e0e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .sidebar .user-info .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }
    .sidebar .user-info .name { font-weight: 600; font-size: 14px; color: #1a1a1a; }
    .sidebar .user-info .role { font-size: 12px; color: #888; }
    .main-content {
        margin-left: 260px;
        flex: 1;
        padding: 24px 32px;
        min-height: 100vh;
    }
    /* Mobile */
    .sidebar-toggle {
        display: none;
        background: none;
        border: none;
        font-size: 24px;
        color: #333;
        cursor: pointer;
        padding: 8px;
    }
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.4);
        z-index: 99;
    }
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            width: 280px;
        }
        .sidebar.open { transform: translateX(0); }
        .sidebar-overlay.active { display: block; }
        .main-content {
            margin-left: 0;
            padding: 16px;
        }
        .sidebar-toggle { display: block; }
    }
</style>
