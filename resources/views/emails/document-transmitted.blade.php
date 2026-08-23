<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document transmis</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #f8f7f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { width: 60px; height: 60px; background: linear-gradient(135deg, #2563eb, #1a4fc4); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; color: white; margin-bottom: 16px; }
        h1 { font-size: 26px; font-weight: 700; color: #1a1a1a; margin: 0; }
        .subtitle { color: #666; font-size: 14px; margin-top: 4px; }
        .content { color: #333; line-height: 1.7; }
        .content p { margin-bottom: 16px; }
        .info-box { background: #f8f7f4; border-radius: 12px; padding: 16px 20px; margin: 20px 0; border-left: 4px solid #2563eb; }
        .info-box strong { color: #1a1a1a; }
        .btn { display: inline-block; background: linear-gradient(135deg, #2563eb, #1a4fc4); color: white; padding: 14px 32px; border-radius: 12px; text-decoration: none; font-weight: 600; margin-top: 10px; }
        .btn:hover { transform: translateY(-2px); }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #888; font-size: 13px; }
        .badge { display: inline-block; background: #dbeafe; color: #2563eb; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo"><i class="fas fa-file-alt" style="font-family:'Font Awesome 6 Free';font-weight:900;"></i></div>
            <h1>Document transmis</h1>
            <p class="subtitle">Un nouveau document vous a été transmis</p>
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p><strong>Vous avez reçu un document de la part de <strong>{{ $emetteur->full_name }}</strong>. Merci de vérifier sa conformité avant de le transmettre officiellement.</p>

            <div class="info-box">
                <p style="margin:0;"><strong>Informations du document :</strong></p>
                <p style="margin:4px 0 0 0; font-size:14px;">
                    <strong>Titre :</strong> {{ $dossier->titre }}<br>
                    <strong>Type :</strong> {{ $dossier->documentType->nom ?? 'Non défini' }}<br>
                    <strong>Version :</strong> V{{ $version->numero_version }}<br>
                    <strong>Mode :</strong> <span class="badge">{{ $mode === 'validation' ? 'À valider' : 'Informatif' }}</span>
                </p>
            </div>

            @if($version->commentaire)
                <div style="background:#f0f7ff; border-radius:12px; padding:12px 16px; margin:16px 0; border-left:4px solid #2563eb;">
                    <p style="margin:0; font-size:14px; color:#333;">
                        <strong>Commentaire :</strong><br>
                        {{ $version->commentaire }}
                    </p>
                </div>
            @endif

            <div style="text-align:center;">
                <a href="{{ $url }}" class="btn">
                    <i class="fas fa-eye" style="font-family:'Font Awesome 6 Free';font-weight:900;"></i> Voir le document
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
