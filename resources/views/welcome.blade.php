<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion de chantiers & Projets BTP</title>
      <link rel="shortcut icon" href="{{ asset('images/login.jpg') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================================
                   RESET & BASE
                ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8f7f4;
            color: #1a1a1a;
            line-height: 1.7;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }

        img {
            max-width: 100%;
            display: block;
            border-radius: 16px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .section-padding {
            padding: 100px 0;
        }

        .section-padding.alt {
            background: #ffffff;
        }

        .section-padding.dark {
            background: #0a1628;
            color: #f0f0f0;
        }

        .section-padding.gradient {
            background: linear-gradient(135deg, #0a1628 0%, #1a2a4a 50%, #0d2137 100%);
            color: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 42px;
            font-weight: 800;
            color: #1a1a1a;
            letter-spacing: -0.5px;
            position: relative;
            display: inline-block;
        }

        .section-title h2 .orange {
            color: #ff6b00;
        }
        .section-title h2 .green {
            color: #00a86b;
        }
        .section-title h2 .blue {
            color: #2563eb;
        }

        .section-title .subtitle {
            color: #666;
            max-width: 650px;
            margin: 12px auto 0;
            font-size: 18px;
        }

        .section-title.dark h2 {
            color: #ffffff;
        }

        .section-title.dark .subtitle {
            color: #aab;
        }

        .section-title .title-line {
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #ff6b00, #00a86b, #2563eb);
            margin: 12px auto 0;
            border-radius: 4px;
        }

        /* ============================================================
                   BUTTONS
                ============================================================ */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #ff6b00, #e05a00);
            color: white;
            padding: 16px 38px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 25px rgba(255, 107, 0, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 10px 45px rgba(255, 107, 0, 0.45);
        }

        .btn-green {
            background: linear-gradient(135deg, #00a86b, #008a56);
            box-shadow: 0 4px 25px rgba(0, 168, 107, 0.35);
        }

        .btn-green:hover {
            box-shadow: 0 10px 45px rgba(0, 168, 107, 0.45);
        }

        .btn-blue {
            background: linear-gradient(135deg, #2563eb, #1a4fc4);
            box-shadow: 0 4px 25px rgba(37, 99, 235, 0.35);
        }

        .btn-blue:hover {
            box-shadow: 0 10px 45px rgba(37, 99, 235, 0.45);
        }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: transparent;
            color: white;
            padding: 14px 34px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 15px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            transform: translateY(-3px);
        }

        /* ============================================================
                   HEADER
                ============================================================ */
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 16px 0;
            box-shadow: 0 2px 30px rgba(0, 0, 0, 0.06);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(15px);
            border-bottom: 3px solid transparent;
            border-image: linear-gradient(90deg, #ff6b00, #00a86b, #2563eb) 1;
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #ff6b00, #00a86b);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            box-shadow: 0 4px 20px rgba(255, 107, 0, 0.3);
            animation: pulse-icon 2s infinite;
        }

        @keyframes pulse-icon {
            0%, 100% {
                box-shadow: 0 4px 20px rgba(255, 107, 0, 0.3);
            }
            50% {
                box-shadow: 0 4px 35px rgba(0, 168, 107, 0.4);
            }
        }

        .logo-text {
            font-size: 24px;
            font-weight: 800;
            color: #1a1a1a;
            letter-spacing: -0.5px;
        }

        .logo-text .orange {
            color: #ff6b00;
        }
        .logo-text .green {
            color: #00a86b;
        }
        .logo-text .blue {
            color: #2563eb;
        }

        .logo-sub {
            font-size: 11px;
            font-weight: 400;
            color: #888;
            display: block;
            margin-top: -3px;
            letter-spacing: 0.3px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
            list-style: none;
        }

        .nav-links a {
            font-size: 14px;
            font-weight: 500;
            color: #444;
            position: relative;
            padding: 4px 0;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #ff6b00, #00a86b);
            transition: width 0.3s ease;
            border-radius: 3px;
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: #ff6b00;
        }

        .nav-links a.active {
            color: #ff6b00;
        }

        .btn-login-header {
            background: linear-gradient(135deg, #ff6b00, #e05a00);
            color: white !important;
            padding: 10px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 20px rgba(255, 107, 0, 0.3);
        }

        .btn-login-header::after {
            display: none !important;
        }

        .btn-login-header:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 35px rgba(255, 107, 0, 0.4);
            color: white !important;
        }

        .menu-toggle {
            display: none;
            font-size: 26px;
            color: #333;
            cursor: pointer;
            background: none;
            border: none;
            padding: 4px 8px;
        }

        /* ============================================================
                   HERO
                ============================================================ */
        .hero {
            background: linear-gradient(165deg, #0a1628 0%, #1a2a4a 40%, #0d2137 100%);
            padding: 100px 0 110px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 107, 0, 0.15), transparent 70%);
            border-radius: 50%;
            animation: float-bg 8s ease-in-out infinite;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -5%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(0, 168, 107, 0.1), transparent 70%);
            border-radius: 50%;
            animation: float-bg 10s ease-in-out infinite reverse;
        }

        @keyframes float-bg {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(30px, -30px) scale(1.1);
            }
        }

        .hero .container {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 70px;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 54px;
            font-weight: 900;
            line-height: 1.08;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .hero-content h1 .orange {
            color: #ff6b00;
        }
        .hero-content h1 .green {
            color: #00e676;
        }
        .hero-content h1 .blue {
            color: #60a5fa;
        }

        .hero-content .hero-description {
            font-size: 19px;
            opacity: 0.85;
            max-width: 500px;
            margin-bottom: 36px;
            line-height: 1.8;
            font-weight: 300;
            color: #c8d0dc;
        }

        .hero-buttons {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .hero-stats {
            display: flex;
            gap: 40px;
            margin-top: 44px;
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .hero-stats .stat-item .number {
            font-size: 28px;
            font-weight: 800;
        }

        .hero-stats .stat-item .number .orange {
            color: #ff6b00;
        }
        .hero-stats .stat-item .number .green {
            color: #00e676;
        }
        .hero-stats .stat-item .number .blue {
            color: #60a5fa;
        }

        .hero-stats .stat-item .label {
            font-size: 13px;
            opacity: 0.7;
            margin-top: 2px;
        }

        .hero-image {
            position: relative;
        }

        .hero-image img {
            border-radius: 20px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            width: 100%;
            height: 420px;
            object-fit: cover;
            transition: transform 0.6s ease;
            border: 2px solid rgba(255, 255, 255, 0.05);
        }

        .hero-image:hover img {
            transform: scale(1.02);
        }

        .hero-image .badge-float {
            position: absolute;
            bottom: -20px;
            left: -20px;
            background: linear-gradient(135deg, #ff6b00, #e05a00);
            padding: 16px 24px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 10px 40px rgba(255, 107, 0, 0.4);
            animation: float-badge 3s ease-in-out infinite;
        }

        @keyframes float-badge {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
        }

        .hero-image .badge-float i {
            font-size: 28px;
            color: white;
        }

        .hero-image .badge-float .text {
            color: white;
            font-size: 13px;
            font-weight: 500;
        }

        .hero-image .badge-float .text strong {
            display: block;
            font-size: 18px;
            font-weight: 800;
        }

        /* ============================================================
                   STATS SECTION
                ============================================================ */
        .stats-section {
            background: #ffffff;
            padding: 60px 0;
            border-bottom: 1px solid #f0eeea;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }

        .stat-card {
            background: #faf9f6;
            border-radius: 16px;
            padding: 30px 24px;
            text-align: center;
            border: 1px solid #f0eeea;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #ff6b00, #00a86b, #2563eb);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
        }

        .stat-card .icon {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .stat-card .icon.orange {
            color: #ff6b00;
        }
        .stat-card .icon.green {
            color: #00a86b;
        }
        .stat-card .icon.blue {
            color: #2563eb;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 800;
            color: #1a1a1a;
            letter-spacing: -0.5px;
        }

        .stat-card .label {
            font-size: 14px;
            color: #888;
            margin-top: 4px;
        }

        /* ============================================================
                   CLIENTS BADGES
                ============================================================ */
        .clients-bar {
            background: #ffffff;
            padding: 30px 0;
            border-bottom: 1px solid #f0eeea;
        }

        .clients-bar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .client-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #888;
            font-weight: 500;
            font-size: 14px;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .client-badge:hover {
            opacity: 1;
            color: #ff6b00;
        }

        .client-badge i {
            font-size: 28px;
            color: #ff6b00;
        }

        .client-badge i.green {
            color: #00a86b;
        }
        .client-badge i.blue {
            color: #2563eb;
        }

        /* ============================================================
                   À PROPOS
                ============================================================ */
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-image {
            position: relative;
        }

        .about-image img {
            width: 100%;
            height: 420px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
        }

        .about-image .floating-card {
            position: absolute;
            bottom: -20px;
            right: -20px;
            background: white;
            padding: 20px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            gap: 16px;
            animation: float-badge 3.5s ease-in-out infinite;
        }

        .about-image .floating-card i {
            font-size: 30px;
            color: #ff6b00;
        }

        .about-image .floating-card .text {
            font-size: 13px;
            color: #666;
        }

        .about-image .floating-card .text strong {
            color: #1a1a1a;
            font-size: 16px;
        }

        .about-text h3 {
            font-size: 34px;
            font-weight: 800;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .about-text h3 .orange {
            color: #ff6b00;
        }
        .about-text h3 .green {
            color: #00a86b;
        }
        .about-text h3 .blue {
            color: #2563eb;
        }

        .about-text .lead {
            font-size: 17px;
            color: #444;
            margin-bottom: 16px;
            font-weight: 500;
        }

        .about-text p {
            color: #666;
            margin-bottom: 14px;
            font-size: 15px;
        }

        .about-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-top: 24px;
        }

        .about-features li {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #333;
            padding: 10px 16px;
            background: #f8f7f4;
            border-radius: 10px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .about-features li:hover {
            background: #fff1e0;
            border-left-color: #ff6b00;
        }

        .about-features li i {
            font-size: 16px;
            width: 20px;
        }

        .about-features li i.orange {
            color: #ff6b00;
        }
        .about-features li i.green {
            color: #00a86b;
        }
        .about-features li i.blue {
            color: #2563eb;
        }

        /* ============================================================
                   SERVICES
                ============================================================ */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .service-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0eeea;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            transform: scaleX(0);
            transition: transform 0.4s ease;
            transform-origin: left;
        }

        .service-card:nth-child(1)::before,
        .service-card:nth-child(4)::before {
            background: #ff6b00;
        }
        .service-card:nth-child(2)::before,
        .service-card:nth-child(5)::before {
            background: #00a86b;
        }
        .service-card:nth-child(3)::before,
        .service-card:nth-child(6)::before {
            background: #2563eb;
        }

        .service-card:hover::before {
            transform: scaleX(1);
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
        }

        .service-card .icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 30px;
            transition: all 0.4s ease;
        }

        .service-card .icon.orange {
            background: linear-gradient(135deg, #fff1e0, #ffe4cc);
            color: #ff6b00;
        }
        .service-card .icon.green {
            background: linear-gradient(135deg, #e0f5ed, #c8f0e0);
            color: #00a86b;
        }
        .service-card .icon.blue {
            background: linear-gradient(135deg, #e0ebff, #c8dfff);
            color: #2563eb;
        }

        .service-card:hover .icon {
            transform: scale(1.05) rotate(-3deg);
        }

        .service-card .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .service-card .badge.orange {
            background: #fff1e0;
            color: #ff6b00;
        }
        .service-card .badge.green {
            background: #e0f5ed;
            color: #00a86b;
        }
        .service-card .badge.blue {
            background: #e0ebff;
            color: #2563eb;
        }

        .service-card h4 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .service-card p {
            font-size: 14px;
            color: #666;
            line-height: 1.7;
        }

        .service-card .learn-more {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            font-weight: 600;
            font-size: 14px;
        }

        .service-card .learn-more.orange {
            color: #ff6b00;
        }
        .service-card .learn-more.green {
            color: #00a86b;
        }
        .service-card .learn-more.blue {
            color: #2563eb;
        }

        .service-card .learn-more i {
            transition: transform 0.3s ease;
        }

        .service-card:hover .learn-more i {
            transform: translateX(6px);
        }

        /* ============================================================
                   GALERIE
                ============================================================ */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .gallery-item {
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            transition: all 0.4s ease;
            aspect-ratio: 4/3;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.08);
        }

        .gallery-item .overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 30px 24px 20px;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.75));
            color: white;
            transform: translateY(30px);
            opacity: 0;
            transition: all 0.4s ease;
        }

        .gallery-item:hover .overlay {
            transform: translateY(0);
            opacity: 1;
        }

        .gallery-item .overlay h5 {
            font-size: 18px;
            font-weight: 700;
        }

        .gallery-item .overlay p {
            font-size: 13px;
            opacity: 0.8;
        }

        .gallery-item .overlay .tag {
            display: inline-block;
            background: rgba(255, 107, 0, 0.85);
            padding: 2px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 6px;
        }

        .gallery-item .overlay .tag.green {
            background: rgba(0, 168, 107, 0.85);
        }
        .gallery-item .overlay .tag.blue {
            background: rgba(37, 99, 235, 0.85);
        }

        /* ============================================================
                   PARTENAIRES
                ============================================================ */
        .partners-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .partner-card {
            background: rgba(255, 255, 255, 0.04);
            border-radius: 20px;
            padding: 32px 28px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.4s ease;
            backdrop-filter: blur(5px);
        }

        .partner-card:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 107, 0, 0.3);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
        }

        .partner-card .logo-partner {
            width: 100px;
            height: 100px;
            margin: 0 auto 16px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
        }

        .partner-card:hover .logo-partner {
            border-color: #ff6b00;
            transform: scale(1.05);
        }

        .partner-card .logo-partner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .partner-card .logo-partner .placeholder-icon {
            font-size: 40px;
            color: rgba(255, 255, 255, 0.3);
        }

        .partner-card h4 {
            font-size: 18px;
            font-weight: 700;
            color: white;
            margin-bottom: 4px;
        }

        .partner-card .sector {
            font-size: 13px;
            color: #ff6b00;
            font-weight: 600;
        }

        .partner-card p {
            font-size: 14px;
            color: #aab;
            margin-top: 10px;
            line-height: 1.6;
        }

        .partner-card .partner-badge {
            display: inline-block;
            margin-top: 12px;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .partner-card .partner-badge.orange {
            background: rgba(255, 107, 0, 0.2);
            color: #ff6b00;
        }
        .partner-card .partner-badge.green {
            background: rgba(0, 168, 107, 0.2);
            color: #00e676;
        }
        .partner-card .partner-badge.blue {
            background: rgba(37, 99, 235, 0.2);
            color: #60a5fa;
        }

        /* ============================================================
                   PROCESSUS
                ============================================================ */
        .process-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-top: 20px;
        }

        .process-step {
            text-align: center;
            padding: 30px 20px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.4s ease;
            position: relative;
        }

        .process-step:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-6px);
        }

        .process-step .step-number {
            font-size: 44px;
            font-weight: 900;
            background: linear-gradient(135deg, #ff6b00, #00a86b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 8px;
        }

        .process-step .icon {
            font-size: 34px;
            margin-bottom: 12px;
        }

        .process-step .icon.orange {
            color: #ff6b00;
        }
        .process-step .icon.green {
            color: #00a86b;
        }
        .process-step .icon.blue {
            color: #60a5fa;
        }

        .process-step h5 {
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 6px;
            color: white;
        }

        .process-step p {
            font-size: 13px;
            color: #aab;
            line-height: 1.6;
        }

        /* ============================================================
                   LOCALISATION
                ============================================================ */
        .location-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .location-map {
            position: relative;
        }

        .location-map img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
        }

        .location-map .map-pin {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #ff6b00, #e05a00);
            color: white;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            box-shadow: 0 8px 35px rgba(255, 107, 0, 0.4);
            animation: pulse-map 2s infinite;
        }

        @keyframes pulse-map {
            0%, 100% {
                transform: translate(-50%, -50%) scale(1);
                box-shadow: 0 8px 35px rgba(255, 107, 0, 0.4);
            }
            50% {
                transform: translate(-50%, -50%) scale(1.12);
                box-shadow: 0 8px 50px rgba(255, 107, 0, 0.6);
            }
        }

        .location-info h3 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .location-info h3 .orange {
            color: #ff6b00;
        }
        .location-info h3 .green {
            color: #00a86b;
        }
        .location-info h3 .blue {
            color: #2563eb;
        }

        .location-info .lead {
            font-size: 16px;
            color: #555;
            margin-bottom: 24px;
        }

        .location-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .location-details .item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px;
            background: #f8f7f4;
            border-radius: 12px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .location-details .item:hover {
            background: #fff1e0;
            border-left-color: #ff6b00;
        }

        .location-details .item i {
            width: 36px;
            height: 36px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .location-details .item i.orange {
            color: #ff6b00;
        }
        .location-details .item i.green {
            color: #00a86b;
        }
        .location-details .item i.blue {
            color: #2563eb;
        }

        .location-details .item .info strong {
            display: block;
            font-size: 14px;
            color: #1a1a1a;
        }

        .location-details .item .info span {
            font-size: 13px;
            color: #888;
        }

        /* ============================================================
                   CONTACT
                ============================================================ */
        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            background: white;
            border-radius: 24px;
            padding: 56px;
            box-shadow: 0 4px 40px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0eeea;
        }

        .contact-form .form-group {
            margin-bottom: 20px;
        }

        .contact-form label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }

        .contact-form label .required {
            color: #dc2626;
        }

        .contact-form input,
        .contact-form textarea,
        .contact-form select {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #e5e5e0;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #faf9f6;
        }

        .contact-form input:focus,
        .contact-form textarea:focus,
        .contact-form select:focus {
            outline: none;
            border-color: #ff6b00;
            box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.08);
            background: white;
        }

        .contact-form textarea {
            min-height: 140px;
            resize: vertical;
        }

        .contact-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .contact-info-side h3 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .contact-info-side h3 .orange {
            color: #ff6b00;
        }
        .contact-info-side h3 .green {
            color: #00a86b;
        }

        .contact-info-side .lead {
            color: #666;
            margin-bottom: 28px;
            font-size: 15px;
        }

        .contact-info-side .contact-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: #f8f7f4;
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .contact-info-side .contact-item:hover {
            background: #fff1e0;
            border-left-color: #ff6b00;
        }

        .contact-info-side .contact-item i {
            width: 44px;
            height: 44px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .contact-info-side .contact-item i.orange {
            color: #ff6b00;
        }
        .contact-info-side .contact-item i.green {
            color: #00a86b;
        }
        .contact-info-side .contact-item i.blue {
            color: #2563eb;
        }

        .contact-info-side .contact-item .info strong {
            display: block;
            font-size: 14px;
            color: #1a1a1a;
        }

        .contact-info-side .contact-item .info span {
            font-size: 13px;
            color: #888;
        }

        .contact-social {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .contact-social a {
            width: 46px;
            height: 46px;
            background: #f8f7f4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 18px;
            transition: all 0.4s ease;
        }

        .contact-social a:hover {
            transform: translateY(-4px) scale(1.05);
        }

        .contact-social a:nth-child(1):hover {
            background: #ff6b00;
            color: white;
        }
        .contact-social a:nth-child(2):hover {
            background: #00a86b;
            color: white;
        }
        .contact-social a:nth-child(3):hover {
            background: #2563eb;
            color: white;
        }
        .contact-social a:nth-child(4):hover {
            background: #e74c3c;
            color: white;
        }

        /* ============================================================
                   FOOTER
                ============================================================ */
        .footer {
            background: #0a1628;
            color: #aaa;
            padding: 60px 0 24px;
            border-top: 3px solid;
            border-image: linear-gradient(90deg, #ff6b00, #00a86b, #2563eb) 1;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 50px;
            margin-bottom: 40px;
        }

        .footer-brand .logo-text {
            color: white;
        }

        .footer-brand .logo-sub {
            color: #888;
        }

        .footer-brand p {
            font-size: 14px;
            margin-top: 14px;
            max-width: 320px;
            color: #888;
            line-height: 1.8;
        }

        .footer-brand .social {
            display: flex;
            gap: 12px;
            margin-top: 18px;
        }

        .footer-brand .social a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 16px;
            transition: all 0.4s ease;
        }

        .footer-brand .social a:hover {
            transform: translateY(-3px);
        }

        .footer-brand .social a:nth-child(1):hover {
            background: #ff6b00;
            color: white;
        }
        .footer-brand .social a:nth-child(2):hover {
            background: #00a86b;
            color: white;
        }
        .footer-brand .social a:nth-child(3):hover {
            background: #2563eb;
            color: white;
        }
        .footer-brand .social a:nth-child(4):hover {
            background: #e74c3c;
            color: white;
        }

        .footer h5 {
            color: white;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 18px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 30px;
            height: 3px;
            background: linear-gradient(90deg, #ff6b00, #00a86b);
            border-radius: 3px;
        }

        .footer ul {
            list-style: none;
        }

        .footer ul li {
            margin-bottom: 10px;
        }

        .footer ul li a {
            font-size: 14px;
            color: #888;
            transition: all 0.3s ease;
        }

        .footer ul li a:hover {
            color: #ff6b00;
            padding-left: 4px;
        }

        .footer-bottom {
            border-top: 1px solid #1a2a3a;
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 13px;
            color: #666;
        }

        .footer-bottom .heart {
            color: #ff6b00;
        }

        /* ============================================================
                   ANIMATIONS SCROLL
                ============================================================ */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .animate-on-scroll.delay-1 {
            transition-delay: 0.1s;
        }
        .animate-on-scroll.delay-2 {
            transition-delay: 0.2s;
        }
        .animate-on-scroll.delay-3 {
            transition-delay: 0.3s;
        }
        .animate-on-scroll.delay-4 {
            transition-delay: 0.4s;
        }
        .animate-on-scroll.delay-5 {
            transition-delay: 0.5s;
        }

        /* ============================================================
                   RESPONSIVE
                ============================================================ */
        @media (max-width: 1024px) {
            .hero .container {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 40px;
            }

            .hero-content .hero-description {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-stats {
                justify-content: center;
            }

            .about-grid,
            .location-grid,
            .contact-wrapper {
                grid-template-columns: 1fr;
            }

            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .partners-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .process-steps {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }

            .hero-image .badge-float {
                left: 50%;
                transform: translateX(-50%);
                bottom: -15px;
            }

            .hero-image .badge-float {
                animation: none;
            }

            .clients-bar .container {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                width: 100%;
                padding: 24px 0;
                gap: 18px;
                background: white;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
                margin-top: 8px;
            }

            .nav-links.open {
                display: flex;
            }

            .nav-links a {
                font-size: 16px;
                padding: 4px 0;
            }

            .btn-login-header {
                text-align: center;
            }

            .header .container {
                flex-wrap: wrap;
            }

            .hero {
                padding: 60px 0 80px;
            }

            .hero-content h1 {
                font-size: 34px;
            }

            .hero-content .hero-description {
                font-size: 16px;
            }

            .hero-stats {
                flex-wrap: wrap;
                gap: 20px;
                justify-content: center;
            }

            .hero-image img {
                height: 260px;
            }

            .section-title h2 {
                font-size: 30px;
            }

            .section-padding {
                padding: 60px 0;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            .gallery-grid {
                grid-template-columns: 1fr 1fr;
            }

            .partners-grid {
                grid-template-columns: 1fr;
            }

            .process-steps {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            .about-grid {
                gap: 30px;
            }

            .about-image img {
                height: 280px;
            }

            .about-features {
                grid-template-columns: 1fr;
            }

            .location-details {
                grid-template-columns: 1fr;
            }

            .location-map img {
                height: 260px;
            }

            .contact-wrapper {
                padding: 24px;
            }

            .contact-form .form-row {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }

            .hero-image .badge-float {
                position: relative;
                bottom: 0;
                left: 0;
                transform: none;
                margin-top: 16px;
                justify-content: center;
            }

            .clients-bar .container {
                gap: 12px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 16px;
            }

            .hero-content h1 {
                font-size: 28px;
            }

            .hero-buttons .btn-primary,
            .hero-buttons .btn-outline {
                width: 100%;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .stat-card .number {
                font-size: 26px;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .service-card {
                padding: 24px 18px;
            }

            .process-step {
                padding: 20px 14px;
            }

            .contact-wrapper {
                padding: 16px;
            }

            .section-title h2 {
                font-size: 24px;
            }

            .about-text h3 {
                font-size: 24px;
            }

            .location-info h3 {
                font-size: 24px;
            }

            .contact-info-side h3 {
                font-size: 22px;
            }

            .stat-card {
                padding: 20px 16px;
            }

            .stat-card .number {
                font-size: 22px;
            }

            .clients-bar .container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

    <!-- ============================================================
    HEADER
    ============================================================ -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-hard-hat"></i>
                </div>
                <div>
                    <span class="logo-text">
                        Fawaz<span class="orange">B</span><span class="green">T</span><span class="blue">P</span>
                    </span>
                    <span class="logo-sub">Gestion de chantiers & Projets</span>
                </div>
            </div>

            <button class="menu-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-links">
                <li><a href="#" class="active">Accueil</a></li>
                <li><a href="#apropos">À propos</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#gallery">Galerie</a></li>
                <li><a href="#partners">Partenaires</a></li>
                <li><a href="#process">Processus</a></li>
                <li><a href="#localisation">Localisation</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="{{ route('login') }}" class="btn-login-header">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </a></li>
            </ul>
        </div>
    </header>

    <!-- ============================================================
    HERO
    ============================================================ -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>
                    Gestion <span class="orange">intelligente</span><br>
                    de vos <span class="green">projets</span> <span class="blue">BTP</span>
                </h1>
                <p class="hero-description">
                    Centralisez, automatisez et sécurisez la circulation de vos documents techniques.
                    Une plateforme conçue pour les professionnels du bâtiment et des travaux publics.
                </p>
                <div class="hero-buttons">
                    <a href="#contact" class="btn-primary">
                        <i class="fas fa-rocket"></i> Commencer maintenant
                    </a>
                    <a href="#services" class="btn-outline">
                        <i class="fas fa-play-circle"></i> Découvrir
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="number"><span class="orange">120</span>+</div>
                        <div class="label">Projets gérés</div>
                    </div>
                    <div class="stat-item">
                        <div class="number"><span class="green">98</span>%</div>
                        <div class="label">Satisfaction client</div>
                    </div>
                    <div class="stat-item">
                        <div class="number"><span class="blue">15</span></div>
                        <div class="label">Partenaires</div>
                    </div>
                    <div class="stat-item">
                        <div class="number"><span class="orange">4.8</span></div>
                        <div class="label">Note moyenne</div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="{{ asset('images/chantier-hero.jpg') }}"
                alt="Chantier BTP"
                onerror="this.src='https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&h=420&fit=crop'">
                <div class="badge-float">
                    <i class="fas fa-shield-alt"></i>
                    <div class="text">
                        <strong>Certifié</strong>
                        Sécurité & Qualité
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    CLIENTS BAR
    ============================================================ -->
    <section class="clients-bar">
        <div class="container">
            <div class="client-badge">
                <i class="fas fa-building"></i>
                <span>Entreprises générales</span>
            </div>
            <div class="client-badge">
                <i class="fas fa-drafting-compass green"></i>
                <span>Bureaux d'études</span>
            </div>
            <div class="client-badge">
                <i class="fas fa-clipboard-check blue"></i>
                <span>Bureaux de contrôle</span>
            </div>
            <div class="client-badge">
                <i class="fas fa-users-cog orange"></i>
                <span>Maîtres d'ouvrage</span>
            </div>
            <div class="client-badge">
                <i class="fas fa-flask green"></i>
                <span>Laboratoires</span>
            </div>
        </div>
    </section>

    <!-- ============================================================
    STATS
    ============================================================ -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card animate-on-scroll">
                    <div class="icon orange"><i class="fas fa-file-alt"></i></div>
                    <div class="number">15 000+</div>
                    <div class="label">Documents gérés</div>
                </div>
                <div class="stat-card animate-on-scroll delay-1">
                    <div class="icon green"><i class="fas fa-users-cog"></i></div>
                    <div class="number">250+</div>
                    <div class="label">Utilisateurs actifs</div>
                </div>
                <div class="stat-card animate-on-scroll delay-2">
                    <div class="icon blue"><i class="fas fa-check-circle"></i></div>
                    <div class="number">3 200+</div>
                    <div class="label">Validations effectuées</div>
                </div>
                <div class="stat-card animate-on-scroll delay-3">
                    <div class="icon orange"><i class="fas fa-clock"></i></div>
                    <div class="number">-45%</div>
                    <div class="label">Délais réduits</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    À PROPOS
    ============================================================ -->
    <section id="apropos" class="section-padding alt">
        <div class="container">
            <div class="section-title animate-on-scroll">
                <h2>À <span class="orange">propos</span> de <span class="blue">Fawaz</span><span class="green">BTP</span></h2>
                <div class="title-line"></div>
                <p class="subtitle">Une solution complète pour la gestion documentaire et le suivi de vos chantiers</p>
            </div>
            <div class="about-grid">
                <div class="about-image animate-on-scroll">
                    <img src="{{ asset('images/about-btp.jpg') }}"
                    alt="À propos"
                    onerror="this.src='https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=600&h=420&fit=crop'">
                    <div class="floating-card">
                        <i class="fas fa-shield-alt"></i>
                        <div class="text">
                            <strong>Sécurisé & fiable</strong><br>
                            Données chiffrées
                        </div>
                    </div>
                </div>
                <div class="about-text animate-on-scroll delay-1">
                    <h3>Une plateforme <span class="orange">innovante</span> pour le <span class="blue">BTP</span></h3>
                    <p class="lead">
                        FawazBTP est un système électronique de gestion documentaire conçu spécifiquement
                        pour les projets de construction de grande envergure.
                    </p>
                    <p>
                        Notre solution centralise l'ensemble des documents techniques (plans, notes de calcul,
                        rapports, fiches techniques, procédures) et automatise les circuits de validation
                        selon les hiérarchies des structures intervenantes.
                    </p>
                    <p>
                        Grâce à une traçabilité complète et une gestion fiable des versions, FawazBTP
                        permet de réduire les erreurs, d'optimiser la communication entre les acteurs
                        et de faciliter l'archivage en fin de projet.
                    </p>
                    <ul class="about-features">
                        <li><i class="fas fa-check-circle orange"></i> Centralisation documentaire</li>
                        <li><i class="fas fa-check-circle green"></i> Workflows automatisés</li>
                        <li><i class="fas fa-check-circle blue"></i> Gestion des versions</li>
                        <li><i class="fas fa-check-circle orange"></i> Traçabilité complète</li>
                        <li><i class="fas fa-check-circle green"></i> Notifications en temps réel</li>
                        <li><i class="fas fa-check-circle blue"></i> Archivage sécurisé</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    SERVICES
    ============================================================ -->
    <section id="services" class="section-padding">
        <div class="container">
            <div class="section-title animate-on-scroll">
                <h2>Nos <span class="orange">services</span></h2>
                <div class="title-line"></div>
                <p class="subtitle">Des fonctionnalités pensées pour répondre aux exigences des projets BTP modernes</p>
            </div>
            <div class="services-grid">
                <div class="service-card animate-on-scroll">
                    <div class="badge orange">Fonctionnalité clé</div>
                    <div class="icon orange"><i class="fas fa-folder-open"></i></div>
                    <h4>Gestion documentaire centralisée</h4>
                    <p>Centralisation de tous vos documents techniques : plans, notes de calcul, dossiers d'exécution, fiches techniques, rapports d'essais.</p>
                    <a href="#" class="learn-more orange">En savoir plus <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card animate-on-scroll delay-1">
                    <div class="badge green">Fonctionnalité clé</div>
                    <div class="icon green"><i class="fas fa-route"></i></div>
                    <h4>Workflows de validation</h4>
                    <p>Circuits automatisés avec checklists dynamiques, affectation aux contrôleurs, suivi en temps réel et décision finale du Bureau de Contrôle.</p>
                    <a href="#" class="learn-more green">En savoir plus <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card animate-on-scroll delay-2">
                    <div class="badge blue">Fonctionnalité clé</div>
                    <div class="icon blue"><i class="fas fa-code-branch"></i></div>
                    <h4>Gestion des versions</h4>
                    <p>Suivi automatisé des versions (V1, V2, V3...) avec conservation de l'historique complet et accès à toutes les révisions.</p>
                    <a href="#" class="learn-more blue">En savoir plus <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card animate-on-scroll delay-3">
                    <div class="badge orange">Fonctionnalité clé</div>
                    <div class="icon orange"><i class="fas fa-clipboard-list"></i></div>
                    <h4>Traçabilité & audit</h4>
                    <p>Journal d'audit détaillé : toutes les actions sont enregistrées pour une transparence totale et une analyse des performances.</p>
                    <a href="#" class="learn-more orange">En savoir plus <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card animate-on-scroll delay-4">
                    <div class="badge green">Fonctionnalité clé</div>
                    <div class="icon green"><i class="fas fa-bell"></i></div>
                    <h4>Notifications automatiques</h4>
                    <p>Alertes par email pour chaque étape : réception, affectation, correction, validation, transmission et archivage avec lien direct.</p>
                    <a href="#" class="learn-more green">En savoir plus <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card animate-on-scroll delay-5">
                    <div class="badge blue">Fonctionnalité clé</div>
                    <div class="icon blue"><i class="fas fa-archive"></i></div>
                    <h4>Archivage sécurisé</h4>
                    <p>Conservation intégrale des documents avec toutes leurs versions, observations et décisions pour une capitalisation optimale.</p>
                    <a href="#" class="learn-more blue">En savoir plus <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    GALERIE
    ============================================================ -->
    <section id="gallery" class="section-padding alt">
        <div class="container">
            <div class="section-title animate-on-scroll">
                <h2>Notre <span class="orange">galerie</span></h2>
                <div class="title-line"></div>
                <p class="subtitle">Découvrez nos réalisations et nos chantiers en cours</p>
            </div>
            <div class="gallery-grid">
                <div class="gallery-item animate-on-scroll">
                    <img src="{{ asset('images/gallery1.jpg') }}" alt="Chantier 1" onerror="this.src='https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400&h=300&fit=crop'">
                    <div class="overlay">
                        <h5>Construction immeuble R+8</h5>
                        <p>Paris - 2025</p>
                        <span class="tag">Projet terminé</span>
                    </div>
                </div>
                <div class="gallery-item animate-on-scroll delay-1">
                    <img src="{{ asset('images/gallery2.jpg') }}" alt="Chantier 2" onerror="this.src='https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=400&h=300&fit=crop'">
                    <div class="overlay">
                        <h5>Rénovation pont</h5>
                        <p>Lyon - 2025</p>
                        <span class="tag green">En cours</span>
                    </div>
                </div>
                <div class="gallery-item animate-on-scroll delay-2">
                    <img src="{{ asset('images/gallery3.jpg') }}" alt="Chantier 3" onerror="this.src='https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=400&h=300&fit=crop'">
                    <div class="overlay">
                        <h5>Lycée international</h5>
                        <p>Marseille - 2024</p>
                        <span class="tag blue">Livré</span>
                    </div>
                </div>
                <div class="gallery-item animate-on-scroll delay-3">
                    <img src="{{ asset('images/gallery4.jpg') }}" alt="Chantier 4" onerror="this.src='https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400&h=300&fit=crop&crop=center'">
                    <div class="overlay">
                        <h5>Complexe sportif</h5>
                        <p>Toulouse - 2025</p>
                        <span class="tag">Projet terminé</span>
                    </div>
                </div>
                <div class="gallery-item animate-on-scroll delay-4">
                    <img src="{{ asset('images/gallery5.jpg') }}" alt="Chantier 5" onerror="this.src='https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=400&h=300&fit=crop&crop=center'">
                    <div class="overlay">
                        <h5>Résidence étudiante</h5>
                        <p>Bordeaux - 2025</p>
                        <span class="tag green">En cours</span>
                    </div>
                </div>
                <div class="gallery-item animate-on-scroll delay-5">
                    <img src="{{ asset('images/gallery6.jpg') }}" alt="Chantier 6" onerror="this.src='https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=400&h=300&fit=crop&crop=center'">
                    <div class="overlay">
                        <h5>Centre commercial</h5>
                        <p>Lille - 2024</p>
                        <span class="tag blue">Livré</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    ENTREPRISES PARTENAIRES
    ============================================================ -->
    <section id="partners" class="section-padding gradient">
        <div class="container">
            <div class="section-title dark animate-on-scroll">
                <h2>Nos <span class="orange">partenaires</span></h2>
                <div class="title-line" style="background: linear-gradient(90deg, #ff6b00, #00a86b, #2563eb);"></div>
                <p class="subtitle" style="color: #aab;">Des entreprises de confiance qui nous accompagnent</p>
            </div>
            <div class="partners-grid">
                <div class="partner-card animate-on-scroll">
                    <div class="logo-partner">
                        <span class="placeholder-icon"><i class="fas fa-building"></i></span>
                    </div>
                    <h4>Bouygues Construction</h4>
                    <div class="sector">Bâtiment & Génie Civil</div>
                    <p>Leader mondial de la construction, Bouygues est un partenaire historique pour nos projets d'envergure. Plus de 50 ans d'expertise.</p>
                    <span class="partner-badge orange">Partenaire Premium</span>
                </div>
                <div class="partner-card animate-on-scroll delay-1">
                    <div class="logo-partner">
                        <span class="placeholder-icon"><i class="fas fa-road"></i></span>
                    </div>
                    <h4>Vinci Group</h4>
                    <div class="sector">Infrastructures & Grands Projets</div>
                    <p>Spécialiste des infrastructures, Vinci apporte son expertise dans la réalisation de nos grands chantiers autoroutiers et ferroviaires.</p>
                    <span class="partner-badge green">Partenaire Stratégique</span>
                </div>
                <div class="partner-card animate-on-scroll delay-2">
                    <div class="logo-partner">
                        <span class="placeholder-icon"><i class="fas fa-hard-hat"></i></span>
                    </div>
                    <h4>Eiffage Construction</h4>
                    <div class="sector">Construction & Génie Civil</div>
                    <p>Acteur majeur du BTP français, Eiffage collabore avec nous sur les projets de grande envergure en métropole et à l'international.</p>
                    <span class="partner-badge blue">Partenaire Premium</span>
                </div>
                <div class="partner-card animate-on-scroll delay-3">
                    <div class="logo-partner">
                        <span class="placeholder-icon"><i class="fas fa-bolt"></i></span>
                    </div>
                    <h4>SPIE</h4>
                    <div class="sector">Énergie & Services</div>
                    <p>Expert en énergie et services, SPIE intervient sur nos chantiers pour les installations électriques, CVC et énergies renouvelables.</p>
                    <span class="partner-badge orange">Partenaire Technique</span>
                </div>
                <div class="partner-card animate-on-scroll delay-4">
                    <div class="logo-partner">
                        <span class="placeholder-icon"><i class="fas fa-city"></i></span>
                    </div>
                    <h4>Sogea-Satom</h4>
                    <div class="sector">Travaux Publics & Construction</div>
                    <p>Spécialiste des travaux publics en Afrique et Outre-Mer, Sogea-Satom est un partenaire clé pour nos projets d'infrastructure.</p>
                    <span class="partner-badge green">Partenaire Stratégique</span>
                </div>
                <div class="partner-card animate-on-scroll delay-5">
                    <div class="logo-partner">
                        <span class="placeholder-icon"><i class="fas fa-water"></i></span>
                    </div>
                    <h4>Groupe Fayat</h4>
                    <div class="sector">Travaux Publics & Construction</div>
                    <p>Acteur majeur du BTP, le Groupe Fayat nous accompagne sur nos projets de travaux publics et de génie civil à forte technicité.</p>
                    <span class="partner-badge blue">Partenaire Premium</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    PROCESSUS
    ============================================================ -->
    <section id="process" class="section-padding alt">
        <div class="container">
            <div class="section-title animate-on-scroll">
                <h2>Notre <span class="orange">processus</span></h2>
                <div class="title-line"></div>
                <p class="subtitle">Un cycle de vie documentaire contrôlé, de l'import à l'archivage</p>
            </div>
            <div class="process-steps" style="grid-template-columns: repeat(4, 1fr);">
                <div class="process-step animate-on-scroll" style="background: #f8f7f4; border-color: #f0eeea;">
                    <div class="step-number" style="background: linear-gradient(135deg, #ff6b00, #00a86b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">01</div>
                    <div class="icon orange"><i class="fas fa-cloud-upload-alt"></i></div>
                    <h5 style="color: #1a1a1a;">Importation</h5>
                    <p style="color: #666;">Intégration du document avec ses métadonnées et attribution d'un identifiant unique</p>
                </div>
                <div class="process-step animate-on-scroll delay-1" style="background: #f8f7f4; border-color: #f0eeea;">
                    <div class="step-number" style="background: linear-gradient(135deg, #00a86b, #2563eb); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">02</div>
                    <div class="icon green"><i class="fas fa-check-double"></i></div>
                    <h5 style="color: #1a1a1a;">Checklist</h5>
                    <p style="color: #666;">Vérification préalable via une checklist dynamique adaptée au type de document</p>
                </div>
                <div class="process-step animate-on-scroll delay-2" style="background: #f8f7f4; border-color: #f0eeea;">
                    <div class="step-number" style="background: linear-gradient(135deg, #2563eb, #ff6b00); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">03</div>
                    <div class="icon blue"><i class="fas fa-users-cog"></i></div>
                    <h5 style="color: #1a1a1a;">Analyse</h5>
                    <p style="color: #666;">Affectation aux contrôleurs spécialisés, observations et consolidation des remarques</p>
                </div>
                <div class="process-step animate-on-scroll delay-3" style="background: #f8f7f4; border-color: #f0eeea;">
                    <div class="step-number" style="background: linear-gradient(135deg, #ff6b00, #2563eb); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">04</div>
                    <div class="icon orange"><i class="fas fa-check-circle"></i></div>
                    <h5 style="color: #1a1a1a;">Décision</h5>
                    <p style="color: #666;">Validation, demande de correction ou archivage. Gestion automatisée des versions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    LOCALISATION
    ============================================================ -->
    <section id="localisation" class="section-padding alt" style="background: #ffffff;">
        <div class="container">
            <div class="section-title animate-on-scroll">
                <h2>Notre <span class="orange">localisation</span></h2>
                <div class="title-line"></div>
                <p class="subtitle">Retrouvez-nous sur nos différents sites pour un accompagnement de proximité</p>
            </div>
            <div class="location-grid">
                <div class="location-map animate-on-scroll">
                    <img src="{{ asset('images/map-btp.jpg') }}"
                    alt="Localisation"
                    onerror="this.src='https://images.unsplash.com/photo-1524661135-423995f22d0b?w=600&h=380&fit=crop'">
                    <div class="map-pin">
                        <i class="fas fa-map-pin"></i>
                    </div>
                </div>
                <div class="location-info animate-on-scroll delay-1">
                    <h3>Nos <span class="orange">sites</span> principaux</h3>
                    <p class="lead">
                        Nous intervenons sur l'ensemble du territoire avec des équipes dédiées
                        à chaque région pour vous offrir un service personnalisé.
                    </p>
                    <div class="location-details">
                        <div class="item">
                            <i class="fas fa-map-marker-alt orange"></i>
                            <div class="info">
                                <strong>Siège social</strong>
                                <span>12 Avenue des Chantiers, 75012 Paris</span>
                            </div>
                        </div>
                        <div class="item">
                            <i class="fas fa-map-marker-alt green"></i>
                            <div class="info">
                                <strong>Agence Sud</strong>
                                <span>45 Rue des Entrepreneurs, 13008 Marseille</span>
                            </div>
                        </div>
                        <div class="item">
                            <i class="fas fa-phone-alt blue"></i>
                            <div class="info">
                                <strong>Téléphone</strong>
                                <span>+33 1 23 45 67 89</span>
                            </div>
                        </div>
                        <div class="item">
                            <i class="fas fa-envelope orange"></i>
                            <div class="info">
                                <strong>Email</strong>
                                <span>contact@fawazbtp.com</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    CONTACT
    ============================================================ -->
    <section id="contact" class="section-padding">
        <div class="container">
            <div class="section-title animate-on-scroll">
                <h2>Contactez-<span class="orange">nous</span></h2>
                <div class="title-line"></div>
                <p class="subtitle">Une question, un projet ? N'hésitez pas à nous contacter</p>
            </div>
            <div class="contact-wrapper">
                <div class="contact-form animate-on-scroll">
                    <form action="#" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nom complet <span class="required">*</span></label>
                                <input type="text" id="name" name="name" placeholder="Votre nom" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email <span class="required">*</span></label>
                                <input type="email" id="email" name="email" placeholder="exemple@entreprise.fr" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone" placeholder="+33 6 12 34 56 78">
                        </div>
                        <div class="form-group">
                            <label for="subject">Sujet <span class="required">*</span></label>
                            <select id="subject" name="subject" required>
                                <option value="">Choisissez un sujet</option>
                                <option value="devis">Demande de devis</option>
                                <option value="demo">Demande de démonstration</option>
                                <option value="support">Support technique</option>
                                <option value="partenariat">Partenariat</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" placeholder="Décrivez votre demande en détail..." required></textarea>
                        </div>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-paper-plane"></i> Envoyer le message
                        </button>
                    </form>
                </div>
                <div class="contact-info-side animate-on-scroll delay-1">
                    <h3>Pourquoi <span class="orange">nous</span> choisir ?</h3>
                    <p class="lead">
                        Notre équipe est à votre écoute pour vous accompagner dans la mise en place
                        de votre système de gestion documentaire.
                    </p>

                    <div class="contact-item">
                        <i class="fas fa-clock orange"></i>
                        <div class="info">
                            <strong>Disponibilité</strong>
                            <span>Lun - Ven : 8h00 - 18h00</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-headset green"></i>
                        <div class="info">
                            <strong>Support technique</strong>
                            <span>Assistance 24/7 pour les projets urgents</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-shield-alt blue"></i>
                        <div class="info">
                            <strong>Sécurité</strong>
                            <span>Données chiffrées et hébergées en France</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-users-cog orange"></i>
                        <div class="info">
                            <strong>Équipe experte</strong>
                            <span>Des professionnels du BTP à votre service</span>
                        </div>
                    </div>

                    <div class="contact-social">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    FOOTER
    ============================================================ -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">
                        <div class="logo-icon" style="background: linear-gradient(135deg, #ff6b00, #00a86b);">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <div>
                            <span class="logo-text" style="color:white;">Fawaz<span class="orange">B</span><span class="green">T</span><span class="blue">P</span></span>
                            <span class="logo-sub">Gestion de chantiers & Projets</span>
                        </div>
                    </div>
                    <p>
                        La solution de gestion documentaire pour les projets de construction.
                        Centralisez, validez, tracez et archivez vos documents techniques en toute sécurité.
                    </p>
                    <div class="social">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h5>Liens rapides</h5>
                    <ul>
                        <li><a href="#">Accueil</a></li>
                        <li><a href="#apropos">À propos</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#process">Processus</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Ressources</h5>
                    <ul>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Support technique</a></li>
                        <li><a href="#">API & intégrations</a></li>
                        <li><a href="#">Blog & actualités</a></li>
                        <li><a href="#">Changelog</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Informations</h5>
                    <ul>
                        <li><a href="#">Mentions légales</a></li>
                        <li><a href="#">Politique de confidentialité</a></li>
                        <li><a href="#">CGU / CGV</a></li>
                        <li><a href="#">Plan du site</a></li>
                        <li><a href="#">Recrutement</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} FawazBTP. Tous droits réservés.</span>
                <span>Conçu avec <i class="fas fa-heart heart"></i> pour le BTP</span>
            </div>
        </div>
    </footer>

    <script>
        // ============================================================
        // SCROLL ANIMATIONS
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            const animateElements = document.querySelectorAll('.animate-on-scroll');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            animateElements.forEach(el => observer.observe(el));
        });

        // ============================================================
        // MOBILE MENU
        // ============================================================
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('open');
        });

        // Close menu on link click
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelector('.nav-links').classList.remove('open');
            });
        });
    </script>

</body>
</html>
