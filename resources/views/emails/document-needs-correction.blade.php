<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Correction demandée</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #f8f7f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { width: 60px; height: 60px; background: linear-gradient(135deg, #dc2626, #991b1b); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; color: white; margin-bottom: 16px; font-weight: 700; }
        h1 { font-size: 26px; font-weight: 700; color: #1a1a1a; margin: 0; }
        .subtitle { color: #666; font-size: 14px; margin-top: 4px; }
        .content { color: #333; line-height: 1.7; }
        .content p { margin-bottom: 16px; }
        .info-box { background: #fef2f2; border-radius: 12px; padding: 16px 20px; margin: 20px 0; border-left: 4px solid #dc2626; }
        .info-box strong { color: #1a1a1a; }
        .motif-box { background: #fffbeb; border-radius: 12px; padding: 16px 20px; margin: 20px 0; border-left: 4px solid #f59e0b; }
        .motif-box p { margin: 0; font-size: 14px; color: #333; white-space: pre-line; }
        .btn { display: inline-block; background: linear-gradient(135deg, #dc2626, #991b1b); color: white; padding: 14px 32px; border-radius: 12px; text-decoration: none; font-weight: 600; margin-top: 10px; }
        .btn:hover { transform: translateY(-2px); }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #888; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">BC</div>
            <h1>Correction demandée</h1>
            <p class="subtitle">Le Bureau de Contrôle a demandé une correction de votre document</p>
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p>Le Bureau de Contrôle a examiné le document suivant et a demandé une <strong>correction</strong> avant validation finale :</p>

            <div class="info-box">
                <p style="margin:0;"><strong>Informations du document :</strong></p>
                <p style="margin:4px 0 0 0; font-size:14px;">
                    <strong>Titre :</strong> {{ $dossier->titre }}<br>
                    <strong>Type :</strong> {{ $dossier->documentType->nom ?? 'Non défini' }}<br>
                    <strong>Version analysée :</strong> V{{ $version->numero_version }}<br>
                    <strong>Structure émettrice :</strong> {{ $dossier->creePar->structure->nom ?? 'Non définie' }}
                </p>
            </div>

            @if($decision && $decision->commentaires)
                <div class="motif-box">
                    <p>
                        <strong>Motif de la correction demandée :</strong><br>
                        {{ $decision->commentaires }}
                    </p>
                </div>
            @endif

            <p>Merci de vous connecter à votre espace pour consulter les observations et soumettre une version corrigée.</p>

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
