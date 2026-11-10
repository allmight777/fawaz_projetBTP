@extends(auth()->user()->spaceLayout())

@section('title', 'Archives')

@section('content')
@php
    $moi = auth()->user();
    // Mes archives : émises par moi ou par ma structure. Partagées avec moi : tout le reste.
    $groupes = $dossiers->getCollection()->groupBy(fn ($d) => $d->appartientA($moi) ? 'miens' : 'partages');
    $sections = [
        ['miens', 'Mes archives', 'box-archive', $groupes['miens'] ?? collect()],
        ['partages', 'Partagés avec moi', 'share-nodes', $groupes['partages'] ?? collect()],
    ];
    $avecTitres = ($groupes['partages'] ?? collect())->isNotEmpty();
@endphp

<div class="page-header">
    <h1><i class="fas fa-box-archive" style="color:var(--accent);"></i> Archives</h1>
</div>

@foreach($sections as [$cle, $titre, $icone, $liste])
    @continue($cle === 'partages' && !$avecTitres)
    @if($avecTitres)
        <div class="sect-titre {{ $cle === 'partages' ? 'partage' : '' }}">
            <span class="st-ico"><i class="fas fa-{{ $icone }}"></i></span>
            <h2>{{ $titre }}</h2>
            <span class="st-nb">{{ $liste->count() }}</span>
        </div>
    @endif
    <div class="table-container">
        <div class="table-wrapper">
            <table data-filtres="structure,type">
                <thead>
                    <tr>
                        <th>Identifiant</th>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Structure émettrice</th>
                        <th>Versions</th>
                        <th>Archivé le</th>
                        <th>Visas</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($liste as $dossier)
                        <tr>
                            <td><span style="font-family:var(--mono); font-size: var(--fs-s); color:var(--accent-d); font-weight:600;">{{ $dossier->identifiant_affiche }}</span></td>
                            <td>{{ Str::limit($dossier->titre, 50) }}</td>
                            <td>{{ $dossier->documentType->code ?? '—' }}</td>
                            <td>{{ $dossier->structureEmettrice?->nom ?? '—' }}</td>
                            <td>@foreach($dossier->versions as $version)<span style="margin-right:6px;">V{{ $version->numero_version }}</span>@endforeach</td>
                            <td>{{ $dossier->updated_at->format('d/m/Y') }}</td>
                            <td>@include('partials.visas-colonne')</td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('dossiers.show', $dossier) }}" class="btn-link" title="Consulter" aria-label="Consulter"><i class="fas fa-eye"></i></a>
                                @include('partials.partager-bouton')
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty-state">Aucun document archivé pour le moment.</div></td></tr>
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
@endsection
