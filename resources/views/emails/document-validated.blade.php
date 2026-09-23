<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document validé</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #f8f7f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { width: 60px; height: 60px; background: linear-gradient(135deg, #047857, #064e3b); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; color: white; margin-bottom: 16px; font-weight: 700; }
        h1 { font-size: 26px; font-weight: 700; color: #1a1a1a; margin: 0; }
        .subtitle { color: #666; font-size: 14px; margin-top: 4px; }
        .content { color: #333; line-height: 1.7; }
        .content p { margin-bottom: 16px; }
        .info-box { background: #f0fdf4; border-radius: 12px; padding: 16px 20px; margin: 20px 0; border-left: 4px solid #047857; }
        .info-box strong { color: #1a1a1a; }
        .success-box { background: #ecfdf5; border-radius: 12px; padding: 16px 20px; margin: 20px 0; border-left: 4px solid #059669; text-align: center; }
        .success-box p { margin: 0; font-size: 15px; color: #064e3b; font-weight: 600; }
        .btn { display: inline-block; background: linear-gradient(135deg, #047857, #064e3b); color: white; padding: 14px 32px; border-radius: 12px; text-decoration: none; font-weight: 600; margin-top: 10px; }
        .btn:hover { transform: translateY(-2px); }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #888; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">BC</div>
            <h1>Document validé</h1>
            <p class="subtitle">Le Bureau de Contrôle a validé votre document</p>
        </div>
        <div class="content">
            <p>Bonjour,</p>

            <div class="success-box">
                <p>Votre document a été validé avec succès par le Bureau de Contrôle.</p>
            </div>

            <div class="info-box">
                <p style="margin:0;"><strong>Informations du document :</strong></p>
                <p style="margin:4px 0 0 0; font-size:14px;">
                    <strong>Titre :</strong> {{ $dossier->titre }}<br>
                    <strong>Type :</strong> {{ $dossier->documentType->nom ?? 'Non défini' }}<br>
                    <strong>Version validée :</strong> V{{ $version->numero_version }}<br>
                    <strong>Structure émettrice :</strong> {{ $dossier->creePar->structure->nom ?? 'Non définie' }}
                </p>
            </div>

            @if($decision && $decision->commentaires)
                <div style="background:#f0fdf4; border-radius:12px; padding:12px 16px; margin:16px 0; border-left:4px solid #047857;">
                    <p style="margin:0; font-size:14px; color:#333;">
                        <strong>Commentaire du validateur :</strong><br>
                        {{ $decision->commentaires }}
                    </p>
                </div>
            @endif

            <p>Vous pouvez consulter le document validé et tamponné depuis votre espace.</p>

            <div style="text-align:center;">
                <a href="{{ $url }}" class="btn">Se connecter</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} FawazBTP. Tous droits réservés.</p>
            <p style="font-size:12px; color:#aaa;">Cet email a été envoyé automatiquement. Veuillez ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
