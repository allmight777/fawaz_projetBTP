<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compte activé</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f8f7f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ff8c00, #ff6b00);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 26px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 4px;
        }
        .content {
            color: #333;
            line-height: 1.7;
        }
        .content p {
            margin-bottom: 16px;
        }
        .badge-success {
            display: inline-block;
            background: #d4edda;
            color: #155724;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .info-box {
            background: #f8f7f4;
            border-radius: 12px;
            padding: 16px 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #ff8c00, #ff6b00);
            color: white;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 13px;
        }
        .footer a {
            color: #ff8c00;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-hard-hat" style="font-family: 'Font Awesome 6 Free'; font-weight: 900;"></i>
            </div>
            <h1>Compte activé !</h1>
            <p class="subtitle">Votre compte FawazBTP est désormais actif</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $user->full_name }}</strong>,</p>

            <p>Nous avons le plaisir de vous informer que votre compte sur la plateforme <strong>FawazBTP</strong> a été <span class="badge-success">activé</span> par l'administrateur.</p>

            <div class="info-box">
                <p style="margin:0;"><strong>Informations de votre compte :</strong></p>
                <p style="margin:4px 0 0 0; font-size:14px; color:#555;">
                    <strong>Email :</strong> {{ $user->email }}<br>
                    <strong>Structure :</strong> {{ $user->structure?->nom ?? 'Non définie' }}<br>
                    <strong>Rôle :</strong> {{ $user->role }}
                </p>
            </div>

            @if($raison)
                <div style="background: #fff3cd; border-radius: 12px; padding: 12px 16px; margin: 16px 0; border-left: 4px solid #ffc107;">
                    <p style="margin:0; font-size:14px; color:#856404;">
                        <strong>Message de l'administrateur :</strong><br>
                        {{ $raison }}
                    </p>
                </div>
            @endif

            <p>Vous pouvez dès maintenant vous connecter à votre espace et accéder à l'ensemble des fonctionnalités.</p>

            <div style="text-align:center;">
                <a href="{{ route('login') }}" class="btn">
                    <i class="fas fa-sign-in-alt" style="font-family: 'Font Awesome 6 Free'; font-weight: 900;"></i> Se connecter
                </a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} FawazBTP. Tous droits réservés.</p>
            <p style="font-size:12px; color:#aaa;">
                Cet email a été envoyé automatiquement. Veuillez ne pas y répondre.
            </p>
        </div>
    </div>
</body>
</html>
