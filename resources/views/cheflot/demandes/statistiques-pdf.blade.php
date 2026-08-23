<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 12px; }
        .header {
            background: #047857;
            color: white;
            padding: 20px 24px;
            margin-bottom: 24px;
        }
        .header h1 { font-size: 20px; margin-bottom: 4px; }
        .header p { font-size: 12px; opacity: 0.9; }
        .container { padding: 0 24px; }
        .info-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .info-box p { margin-bottom: 4px; }
        .info-box strong { color: #064e3b; }

        .stats-row { display: table; width: 100%; margin-bottom: 24px; }
        .stat-box {
            display: table-cell;
            width: 20%;
            text-align: center;
            padding: 12px 8px;
            border: 1px solid #e5e7eb;
        }
        .stat-box .num { font-size: 20px; font-weight: bold; color: #064e3b; }
        .stat-box .label { font-size: 10px; color: #6b7280; text-transform: uppercase; margin-top: 4px; }

        h2 {
            font-size: 14px;
            color: #064e3b;
            margin: 20px 0 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #047857;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { text-align: left; padding: 8px 10px; background: #f0fdf4; color: #064e3b; font-size: 11px; border: 1px solid #e5e7eb; }
        td { padding: 8px 10px; font-size: 11px; border: 1px solid #e5e7eb; }

        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport de contrôle des documents</h1>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }} par {{ $user->full_name }}</p>
    </div>

    <div class="container">
        <div class="info-box">
            <p><strong>Période :</strong> du {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}</p>
            @if($collaborateurSelectionne)
                <p><strong>Collaborateur :</strong> {{ $collaborateurSelectionne->full_name }}</p>
            @else
                <p><strong>Collaborateur :</strong> Tous les collaborateurs</p>
            @endif
        </div>

        <h2>Vue d'ensemble</h2>
        <div class="stats-row">
            <div class="stat-box"><div class="num">{{ $stats['total_documents'] }}</div><div class="label">Total</div></div>
            <div class="stat-box"><div class="num">{{ $stats['par_statut']['soumis'] + $stats['par_statut']['en_cours'] }}</div><div class="label">En attente</div></div>
            <div class="stat-box"><div class="num">{{ $stats['par_statut']['en_analyse'] }}</div><div class="label">En analyse</div></div>
            <div class="stat-box"><div class="num">{{ $stats['par_statut']['valide'] }}</div><div class="label">Validés</div></div>
            <div class="stat-box"><div class="num">{{ $stats['par_statut']['a_corriger'] }}</div><div class="label">À corriger</div></div>
        </div>

        <h2>Répartition par structure d'origine</h2>
        <table>
            <thead><tr><th>Structure</th><th>Nombre de documents</th></tr></thead>
            <tbody>
                @foreach($stats['par_structure'] as $structure => $count)
                <tr><td>{{ $structure }}</td><td>{{ $count }}</td></tr>
                @endforeach
            </tbody>
        </table>

        <h2>Détail par type de document</h2>
        <table>
            <thead><tr><th>Type</th><th>Nombre</th><th>Pourcentage</th></tr></thead>
            <tbody>
                @php $totalType = $stats['total_documents'] > 0 ? $stats['total_documents'] : 1; @endphp
                @forelse($stats['par_type'] as $typeNom => $count)
                <tr>
                    <td>{{ $typeNom }}</td>
                    <td>{{ $count }}</td>
                    <td>{{ round(($count / $totalType) * 100, 1) }}%</td>
                </tr>
                @empty
                <tr><td colspan="3">Aucune donnée pour cette période</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Performance par collaborateur</h2>
        <table>
            <thead><tr><th>Collaborateur</th><th>Documents terminés</th></tr></thead>
            <tbody>
                @forelse($stats['par_collaborateur'] as $nom => $count)
                <tr><td>{{ $nom }}</td><td>{{ $count }}</td></tr>
                @empty
                <tr><td colspan="2">Aucune donnée pour cette période</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Évolution mensuelle</h2>
        <table>
            <thead><tr><th>Mois</th><th>Documents reçus</th></tr></thead>
            <tbody>
                @foreach($stats['evolution_mensuelle'] as $mois)
                <tr><td>{{ $mois['mois'] }}</td><td>{{ $mois['total'] }}</td></tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Rapport généré automatiquement — Fawaz BTP, Bureau de Contrôle
        </div>
    </div>
</body>
</html>
