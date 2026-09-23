```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demande de validation interne</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f5f7;
            margin: 0;
            padding: 0;
            color: #1f2937;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(15, 23, 42, 0.07);
        }

        .header {
            background: #172033;
            padding: 32px 40px;
            text-align: center;
        }

        .brand {
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            line-height: 1.3;
        }

        .header .subtitle {
            color: #cbd5e1;
            font-size: 14px;
            margin: 10px 0 0;
            line-height: 1.5;
        }

        .content {
            padding: 36px 40px;
            color: #374151;
            font-size: 15px;
            line-height: 1.7;
        }

        .content p {
            margin: 0 0 16px;
        }

        .content strong {
            color: #111827;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px 22px;
            margin: 24px 0;
        }

        .info-title {
            color: #172033;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .info-row {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            color: #64748b;
            display: inline-block;
            width: 100px;
        }

        .info-value {
            color: #1f2937;
            font-weight: 600;
        }

        .comment-box {
            background: #f8fafc;
            border-left: 4px solid #64748b;
            border-radius: 6px;
            padding: 14px 18px;
            margin: 20px 0 24px;
        }

        .comment-title {
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .comment-text {
            color: #374151;
            font-size: 14px;
            line-height: 1.6;
        }

        .action {
            text-align: center;
            margin: 30px 0 10px;
        }

        .btn {
            display: inline-block;
            background: #172033;
            color: #ffffff !important;
            padding: 13px 28px;
            border-radius: 7px;
            text-decoration: none !important;
            font-size: 14px;
            font-weight: 700;
            border: 1px solid #172033;
        }

        .btn:hover {
            background: #0f172a;
        }

        .action-note {
            color: #94a3b8;
            font-size: 12px;
            margin-top: 12px;
        }

        .footer {
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            padding: 22px 30px;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.6;
        }

        .footer p {
            margin: 3px 0;
        }

        @media only screen and (max-width: 640px) {
            .container {
                margin: 15px;
                border-radius: 10px;
            }

            .header {
                padding: 28px 24px;
            }

            .content {
                padding: 28px 24px;
            }

            .info-label {
                display: block;
                width: auto;
                margin-bottom: 2px;
            }

            .info-value {
                display: block;
            }

            .btn {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="header">
            <div class="brand">FawazBTP</div>

            <h1>Demande de validation interne</h1>

            <p class="subtitle">
                Un document nécessite votre validation avant transmission.
            </p>
        </div>

        <div class="content">

            <p>Bonjour,</p>

            <p>
                Votre collaborateur
                <strong>{{ $emetteur->full_name }}</strong>
                a soumis un document nécessitant votre validation avant sa transmission au
                <strong>Bureau de Contrôle</strong>.
            </p>

            <div class="info-box">

                <div class="info-title">
                    Informations du document
                </div>

                <div class="info-row">
                    <span class="info-label">Titre</span>
                    <span class="info-value">{{ $dossier->titre }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Type</span>
                    <span class="info-value">
                        {{ $dossier->documentType->nom ?? 'Non défini' }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Version</span>
                    <span class="info-value">
                        V{{ $version->numero_version }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Entreprise</span>
                    <span class="info-value">
                        {{ $emetteur->structure?->nom ?? 'Non définie' }}
                    </span>
                </div>

            </div>

            @if($version->commentaire)

                <div class="comment-box">

                    <div class="comment-title">
                        Message du collaborateur
                    </div>

                    <div class="comment-text">
                        {{ $version->commentaire }}
                    </div>

                </div>

            @endif

            <div class="action">

                <a href="{{ $url }}" class="btn">
                    Examiner et transférer
                </a>

                <div class="action-note">
                    Cliquez sur le bouton pour consulter le document et effectuer sa validation.
                </div>

            </div>

        </div>

        <div class="footer">

            <p>
                &copy; {{ date('Y') }} FawazBTP. Tous droits réservés.
            </p>

            <p>
                Cet email a été envoyé automatiquement. Merci de ne pas y répondre.
            </p>

        </div>

    </div>

</body>
</html>
```
