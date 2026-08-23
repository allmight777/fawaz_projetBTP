<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demande de Controle</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #f8f7f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; color: white; margin-bottom: 16px; }
        h1 { font-size: 26px; font-weight: 700; color: #1a1a1a; margin: 0; }
        .subtitle { color: #666; font-size: 14px; margin-top: 4px; }
        .content { color: #333; line-height: 1.7; }
        .content p { margin-bottom: 16px; }
        .info-box { background: #fffbeb; border-radius: 12px; padding: 16px 20px; margin: 20px 0; border-left: 4px solid #f59e0b; }
        .info-box strong { color: #1a1a1a; }
        .btn { display: inline-block; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 14px 32px; border-radius: 12px; text-decoration: none; font-weight: 600; margin-top: 10px; }
        .btn:hover { transform: translateY(-2px); }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #888; font-size: 13px; }
        .badge { display: inline-block; background: #fef3c7; color: #d97706; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo"><i class="fas fa-clipboard-check" style="font-family:'Font Awesome 6 Free';font-weight:900;"></i></div>
            <h1>Demande de Controle</h1>
            <p class="subtitle">Un document nécessite votre validation</p>
        </div>
        <div class="content">
            <p>Bonjour Chef du Bureau de Contrôle,</p>
            <p><strong>{{ $emetteur->full_name }}</strong> a soumis un document pour validation sur la plateforme <strong>FawazBTP</strong>.</p>

            <div class="info-box">
                <p style="margin:0;"><strong>Informations du document :</strong></p>
                <p style="margin:4px 0 0 0; font-size:14px;">
                    <strong>Titre :</strong> {{ $dossier->titre }}<br>
                    <strong>Type :</strong> {{ $dossier->documentType->nom ?? 'Non défini' }}<br>
                    <strong>Version :</strong> V{{ $version->numero_version }}<br>
                    <strong>Entreprise :</strong> {{ $emetteur->structure?->nom ?? 'Non définie' }}
                </p>
            </div>

            @if($version->commentaire)
                <div style="background:#fffbeb; border-radius:12px; padding:12px 16px; margin:16px 0; border-left:4px solid #f59e0b;">
                    <p style="margin:0; font-size:14px; color:#333;">
                        <strong>Message de l'émetteur :</strong><br>
                        {{ $version->commentaire }}
                    </p>
                </div>
            @endif

            <div style="text-align:center;">
                <a href="{{ $url }}" class="btn">
                    <i class="fas fa-check-double" style="font-family:'Font Awesome 6 Free';font-weight:900;"></i> Valider le document
                </a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} FawazBTP. Tous droits réservés.</p>
            <p style="font-size:12px; color:#aaa;">Cet email a été envoyé automatiquement. Veuillez ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
