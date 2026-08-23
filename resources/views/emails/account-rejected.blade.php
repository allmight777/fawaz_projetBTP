<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compte refusé</title>
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
            background: linear-gradient(135deg, #dc3545, #c82333);
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
        .badge-danger {
            display: inline-block;
            background: #f8d7da;
            color: #721c24;
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
            border-left: 4px solid #dc3545;
        }
        .btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
        }
        .btn:hover {
            background: #5a6268;
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
        .raison-box {
            background: #fff3cd;
            border-radius: 12px;
            padding: 16px 20px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .raison-box p {
            margin: 0;
            font-size: 14px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-times" style="font-family: 'Font Awesome 6 Free'; font-weight: 900;"></i>
            </div>
            <h1>Compte refusé</h1>
            <p class="subtitle">Votre demande de création de compte a été refusée</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $user->full_name }}</strong>,</p>

            <p>Nous regrettons de vous informer que votre demande de création de compte sur la plateforme <strong>FawazBTP</strong> a été <span class="badge-danger">refusée</span> par l'administrateur.</p>

            @if($raison)
                <div class="raison-box">
                    <p><strong>Raison du refus :</strong><br>{{ $raison }}</p>
                </div>
            @else
                <div class="raison-box">
                    <p><strong>Raison du refus :</strong><br>Aucune raison spécifiée par l'administrateur.</p>
                </div>
            @endif

            <div class="info-box">
                <p style="margin:0;"><strong>Informations de votre demande :</strong></p>
                <p style="margin:4px 0 0 0; font-size:14px; color:#555;">
                    <strong>Email :</strong> {{ $user->email }}<br>
                    <strong>Structure :</strong> {{ $user->structure?->nom ?? 'Non définie' }}
                </p>
            </div>

            <p>Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez obtenir plus d'informations, vous pouvez contacter l'administrateur.</p>

            <div style="text-align:center;">
                <a href="mailto:{{ config('mail.from.address') }}" class="btn">
                    <i class="fas fa-envelope" style="font-family: 'Font Awesome 6 Free'; font-weight: 900;"></i> Contacter l'administrateur
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
