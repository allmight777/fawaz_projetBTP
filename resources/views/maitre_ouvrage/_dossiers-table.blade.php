<div style="background:white; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#fdf0f8;">
            <tr>
                <th style="padding:12px 20px; text-align:left; font-size:12px; text-transform:uppercase; color:#E91E8C;">Identifiant</th>
                <th style="padding:12px 20px; text-align:left; font-size:12px; text-transform:uppercase; color:#E91E8C;">Titre</th>
                <th style="padding:12px 20px; text-align:left; font-size:12px; text-transform:uppercase; color:#E91E8C;">Type</th>
                <th style="padding:12px 20px; text-align:left; font-size:12px; text-transform:uppercase; color:#E91E8C;">Structure émettrice</th>
                <th style="padding:12px 20px; text-align:left; font-size:12px; text-transform:uppercase; color:#E91E8C;">Statut</th>
                <th style="padding:12px 20px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($dossiers as $dossier)
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:14px 20px; font-family:monospace; font-size:13px; color:#374151;">{{ $dossier->identifiant_affiche }}</td>
                    <td style="padding:14px 20px; font-size:13px; color:#111827;">{{ $dossier->titre }}</td>
                    <td style="padding:14px 20px; font-size:13px; color:#6b7280;">{{ $dossier->documentType->code ?? '—' }}</td>
                    <td style="padding:14px 20px; font-size:13px; color:#6b7280;">{{ $dossier->structureEmettrice?->nom ?? '—' }}</td>
                    <td style="padding:14px 20px;">
                        <span style="padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; background:#eff4ff; color:#2563eb;">{{ $dossier->statut_label }}</span>
                    </td>
                    <td style="padding:14px 20px; text-align:right;">
                        <a href="{{ route('dossiers.show', $dossier) }}" style="color:#E91E8C; font-weight:600; font-size:13px; text-decoration:none;">Consulter</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:32px; text-align:center; color:#9ca3af; font-size:14px;">Aucun document pour le moment.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:20px;">
    {{ $dossiers->links() }}
</div>
