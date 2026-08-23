{{-- Style commun aux espaces "tableau de bord" (Admin, Chef de Lot, Contrôleur) : sidebar
     blanche + top-bar, sur le même principe que l'espace Entreprise. Seule la couleur
     d'accent change, via --accent/--accent-dark/--accent-bg définies par chaque layout. --}}
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Inter', sans-serif;
        background: #ffffff;
        overflow-x: hidden;
    }

    /* ========== MOBILE TOGGLE ========== */
    .mobile-toggle {
        display: none;
        background: none;
        border: none;
        font-size: 24px;
        color: var(--accent);
        cursor: pointer;
        margin-right: 15px;
    }

    /* ========== SIDEBAR ========== */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 280px;
        height: 100%;
        background: #ffffff;
        border-right: 1px solid #e5e7eb;
        z-index: 100;
        transition: all 0.3s ease;
        overflow-y: auto;
    }

    .sidebar-header {
        padding: 25px 20px;
        border-bottom: 1px solid #eef0f3;
        margin-bottom: 20px;
    }

    .logo { display: flex; align-items: center; gap: 12px; }

    .logo-icon {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        flex-shrink: 0;
    }

    .logo-text h2 { color: #1a1a1a; font-size: 20px; font-weight: 700; }
    .logo-text p { color: var(--accent); font-size: 11px; font-weight: 500; }

    .nav-menu { padding: 0 15px; }
    .nav-item { list-style: none; margin-bottom: 8px; }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        color: #555;
        text-decoration: none;
        border-radius: 12px;
        transition: all 0.3s;
        font-weight: 500;
    }

    .nav-link i { width: 22px; font-size: 18px; }

    .nav-link:hover { background: var(--accent-bg); color: var(--accent); }

    .nav-link.active {
        background: var(--accent-bg);
        color: var(--accent);
        font-weight: 700;
    }

    .nav-divider { height: 1px; background: #eef0f3; margin: 15px 0; }

    /* ========== MAIN CONTENT ========== */
    .main-content {
        margin-left: 280px;
        transition: all 0.3s ease;
    }

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

    .top-bar-left { display: flex; align-items: center; }

    .page-title { font-size: 24px; font-weight: 700; color: #1a1a1a; }

    .user-menu { display: flex; align-items: center; gap: 20px; }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 15px;
        background: var(--accent-bg);
        border-radius: 30px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    .user-name { font-weight: 600; color: #333; }
    .user-role { font-size: 12px; color: var(--accent); }

    .logout-btn {
        background: none;
        border: none;
        color: var(--accent-dark);
        cursor: pointer;
        font-size: 20px;
        transition: transform 0.3s;
    }

    .logout-btn:hover { transform: scale(1.1); }

    /* Content Wrapper */
    .content-wrapper { padding: 30px; }

    /* Responsive */
    @media (max-width: 768px) {
        .mobile-toggle { display: block; }

        .sidebar { transform: translateX(-100%); }
        .sidebar.active { transform: translateX(0); }

        .main-content { margin-left: 0; }

        .top-bar { padding: 12px 15px; flex-wrap: wrap; }
        .page-title { font-size: 18px; }
        .content-wrapper { padding: 15px; }
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--accent-dark); }
</style>
