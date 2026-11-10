{{-- Tableau de dossiers : même présentation partout (archives, liste, etc.). --}}
@php
    $pastille = ['valide' => 'badge-valide', 'archive' => 'badge-controle', 'a_corriger' => 'badge-rejete'];
    $moiListe = auth()->user();
    $partagesIdsListe = \App\Models\Dossier::idsPartagesAvec($moiListe);
    $groupesListe = (method_exists($dossiers, 'getCollection') ? $dossiers->getCollection() : collect($dossiers))
        ->groupBy(fn ($d) => in_array($d->id, $partagesIdsListe, true) && !$d->appartientA($moiListe) ? 'partages' : 'miens');
    $avecTitresListe = ($groupesListe['partages'] ?? collect())->isNotEmpty();
    $sectionsListe = [
        ['miens', 'Mes documents et dossiers reçus', 'folder-open', $groupesListe['miens'] ?? collect()],
        ['partages', 'Partagés avec moi', 'share-nodes', $groupesListe['partages'] ?? collect()],
    ];
@endphp
@foreach($sectionsListe as [$cleListe, $titreListe, $iconeListe, $listeDossiers])
@continue($cleListe === 'partages' && !$avecTitresListe)
@if($avecTitresListe)
    <div class="sect-titre {{ $cleListe === 'partages' ? 'partage' : '' }}">
        <span class="st-ico"><i class="fas fa-{{ $iconeListe }}"></i></span>
        <h2>{{ $titreListe }}</h2>
        <span class="st-nb">{{ $listeDossiers->count() }}</span>
    </div>
@endif
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
                @forelse($listeDossiers as $dossier)
                    <tr>
                        <td><span style="font-family:var(--mono); font-size: var(--fs-s); color:var(--accent-d); font-weight:600;">{{ $dossier->identifiant_affiche }}</span></td>
                        <td>{{ Str::limit($dossier->titre, 50) }}</td>
                        <td>{{ $dossier->documentType->code ?? '—' }}</td>
                        <td>{{ $dossier->structureEmettrice?->nom ?? '—' }}</td>
                        <td>V{{ $dossier->version_courante }}</td>
                        <td><span class="badge {{ $pastille[$dossier->statut] ?? 'badge-attente' }}">{{ $dossier->statut_label }}</span></td>
                        <td>@include('partials.visas-colonne')</td>
                        <td style="white-space:nowrap;"><a href="{{ route('dossiers.show', $dossier) }}" class="btn-link" title="Consulter" aria-label="Consulter"><i class="fas fa-eye"></i></a> @include('partials.partager-bouton') @include('partials.archiver-action')</td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state">Aucun document pour le moment.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach
@if(method_exists($dossiers, 'links'))
    <div style="margin-top:14px;">{{ $dossiers->links() }}</div>
@endif
@include('partials.partage-informatif')
