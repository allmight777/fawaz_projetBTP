{{-- Tableau de dossiers : même présentation partout (archives, liste, etc.). --}}
@php $pastille = ['valide' => 'badge-valide', 'archive' => 'badge-controle', 'a_corriger' => 'badge-rejete']; @endphp
<div class="table-container">
    <div class="table-wrapper">
        <table data-filtres="{{ $filtresTable ?? 'statut,type,structure' }}">
            <thead>
                <tr>
                    <th>Identifiant</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Structure émettrice</th>
                    <th>Version</th>
                    <th>Statut</th>
                    <th>Visas</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($dossiers as $dossier)
                    <tr>
                        <td><span style="font-family:var(--mono); font-size: var(--fs-s); color:var(--accent-d); font-weight:600;">{{ $dossier->identifiant_affiche }}</span></td>
                        <td>{{ Str::limit($dossier->titre, 50) }}</td>
                        <td>{{ $dossier->documentType->code ?? '—' }}</td>
                        <td>{{ $dossier->structureEmettrice?->nom ?? '—' }}</td>
                        <td>V{{ $dossier->version_courante }}</td>
                        <td><span class="badge {{ $pastille[$dossier->statut] ?? 'badge-attente' }}">{{ $dossier->statut_label }}</span></td>
                        <td>@include('partials.visas-colonne')</td>
                        <td><a href="{{ route('dossiers.show', $dossier) }}" class="btn-link">Consulter</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state">Aucun document pour le moment.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($dossiers, 'links'))
        <div style="margin-top:14px;">{{ $dossiers->links() }}</div>
    @endif
</div>
