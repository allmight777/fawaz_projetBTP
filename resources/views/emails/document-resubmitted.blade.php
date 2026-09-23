<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document corrigé et resoumis</title>

    <style>
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            background: #eef1f6;
            margin: 0;
            padding: 0;
            color: #1f2937;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.12);
        }

        /* ===== HEADER ===== */
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 40px 40px 36px;
            text-align: center;
            position: relative;
        }

        .header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
        }

        .brand {
            display: inline-block;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 20px;
            padding: 6px 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.05);
        }

        .header h1 {
            color: #ffffff;
            font-size: 26px;
            font-weight: 700;
            margin: 0;
            line-height: 1.3;
            letter-spacing: -0.3px;
        }

        .subtitle {
            color: #94a3b8;
            font-size: 14px;
            margin: 12px 0 0;
            line-height: 1.5;
        }

        /* ===== CONTENT ===== */
        .content {
            padding: 38px 40px;
            color: #374151;
            font-size: 15px;
            line-height: 1.7;
        }

        .content p {
            margin: 0 0 18px;
        }

        .content strong {
            color: #111827;
            font-weight: 700;
        }

        /* ===== INFO BOX ===== */
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 22px 24px;
            margin: 26px 0;
        }

        .info-title {
            display: flex;
            align-items: center;
            color: #1e293b;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-title::before {
            content: "";
            display: inline-block;
            width: 4px;
            height: 16px;
            background: linear-gradient(180deg, #3b82f6, #8b5cf6);
            border-radius: 4px;
            margin-right: 10px;
        }

        .info-row {
            padding: 10px 0;
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
            width: 130px;
            font-weight: 500;
        }

        .info-value {
            color: #1f2937;
            font-weight: 600;
        }

        /* ===== COMMENT BOX ===== */
        .comment-box {
            background: linear-gradient(135deg, #f0f9ff 0%, #f8fafc 100%);
            border-left: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 22px 0 26px;
        }

        .comment-title {
            display: flex;
            align-items: center;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .comment-title::before {
            content: "💬";
            margin-right: 8px;
            font-size: 14px;
        }

        .comment-text {
            color: #374151;
            font-size: 14px;
            line-height: 1.6;
            font-style: italic;
        }

        /* ===== ACTION ===== */
        .action {
            text-align: center;
            margin: 34px 0 12px;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff !important;
            padding: 15px 34px;
            border-radius: 10px;
            text-decoration: none !important;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.25);
            transition: all 0.2s ease;
            border: none;
        }

        .btn:hover {
            background: linear-gradient(135deg, #0f172a 0%, #020617 100%);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.35);
            transform: translateY(-1px);
        }

        .action-note {
            color: #94a3b8;
            font-size: 12px;
            margin-top: 14px;
            line-height: 1.5;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            padding: 26px 30px;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.6;
        }

        .footer p {
            margin: 4px 0;
        }

        .footer .brand-footer {
            color: #64748b;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 11px;
            margin-bottom: 6px;
        }

        /* ===== RESPONSIVE ===== */
        @media only screen and (max-width: 640px) {
            .container {
                margin: 15px;
                border-radius: 12px;
            }

            .header {
                padding: 30px 24px 28px;
            }

            .header h1 {
                font-size: 22px;
            }

            .content {
                padding: 28px 24px;
            }

            .info-label {
                display: block;
                width: auto;
                margin-bottom: 3px;
            }

            .info-value {
                display: block;
            }

            .btn {
                display: block;
                text-align: center;
                padding: 15px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- HEADER -->
        <div class="header">

            <div class="brand">
                FawazBTP
            </div>

            <h1>
                Document corrigé et resoumis
            </h1>

            <p class="subtitle">
                Une nouvelle version est disponible pour contrôle
            </p>

        </div>

        <!-- CONTENT -->
        <div class="content">

            <p>
                <strong>{{ $emetteur->full_name }}</strong>
                ({{ $emetteur->structure?->nom ?? 'N/A' }}),
                qui avait précédemment transmis ce document, l'a corrigé à la suite de votre refus et vous le resoumet pour contrôle.
            </p>

            <!-- INFO BOX -->
            <div class="info-box">

                <div class="info-title">
                    Informations du document
                </div>

                <div class="info-row">
                    <span class="info-label">Titre</span>
                    <span class="info-value">
                        {{ $dossier->titre }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Type</span>
                    <span class="info-value">
                        {{ $dossier->documentType->nom ?? 'Non défini' }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Nouvelle version</span>
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

            <!-- COMMENT -->
            @if($version->commentaire)

                <div class="comment-box">

                    <div class="comment-title">
                        Message de l'émetteur
                    </div>

                    <div class="comment-text">
                        {{ $version->commentaire }}
                    </div>

                </div>

            @endif

            <!-- ACTION -->
            <div class="action">

                <a href="{{ $url }}" class="btn">
                    Se connecter
                </a>

                <div class="action-note">
                    Connectez-vous pour consulter le document et vérifier les corrections apportées.
                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="footer">

            <p class="brand-footer">
                FawazBTP
            </p>

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
