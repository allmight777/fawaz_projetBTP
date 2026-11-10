@extends(auth()->user()->spaceLayout())

@section('title', 'Mes dossiers')

@section('content')
<style>
.badge-en-attente { background: #fef3c7; color: #d97706; }
.badge-en-cours { background: #dbeafe; color: #2563eb; }
.badge-brouillon { background: #e5e7eb; color: #6b7280; }
.identifiant-tag {
        font-family: monospace;
        font-size: var(--fs-s);
        color: #2563eb;
        background: #eff4ff;
        padding: 2px 8px;
        border-radius: 6px;
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-folder-open"></i> Mes dossiers</h2>
    <div>
        <a href="{{ eroute('entreprise.documents') }}" class="btn-outline-blue">
            <i class="fas fa-file-alt"></i> Voir les documents
        </a>
    </div>
</div>

@php
    $moi = auth()->user();
    $partagesIds = \App\Models\Dossier::idsPartagesAvec($moi);
    $groupes = $dossiers->getCollection()->groupBy(fn ($d) => in_array($d->id, $partagesIds, true) && !$d->appartientA($moi) ? 'partages' : 'miens');
    $avecTitres = ($groupes['partages'] ?? collect())->isNotEmpty();
    $sections = [
        ['miens', 'Mes dossiers', 'folder-open', $groupes['miens'] ?? collect()],
        ['partages', 'Partagés avec moi', 'share-nodes', $groupes['partages'] ?? collect()],
    ];
@endphp

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
        <table data-filtres="statut,type,date">
            <thead>
                <tr>
                    <th>Identifiant</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Versions</th>
                    <th>Statut</th>
                    <th>Visas</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($liste as $dossier)
                <tr>
                    <td>
                        <span class="identifiant-tag">{{ $dossier->identifiant ?? '#'.$dossier->id }}</span>
                    </td>
                    <td>{{ Str::limit($dossier->titre ?? 'Sans titre', 40) }}</td>
                    <td>{{ $dossier->documentType->nom ?? '-' }}</td>
                    <td>{{ $dossier->versions->count() }}</td>
                    <td>
                        <span class="badge
                            @if($dossier->statut == 'valide') badge-valide
                            @elseif($dossier->statut == 'en_cours') badge-en-cours
                            @elseif($dossier->statut == 'a_corriger') badge-rejete
                            @elseif($dossier->statut == 'soumis') badge-en-attente
                            @else badge-brouillon @endif">
                            {{ $dossier->statut_label }}
                        </span>
                    </td>
                    <td>@include('partials.visas-colonne')</td>
                    <td style="font-size: var(--fs-m); color:#888;">
                        {{ $dossier->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <a href="{{ eroute('entreprise.dossiers.show', $dossier->id) }}" class="btn-link" title="Voir le dossier" aria-label="Voir le dossier"><i class="fas fa-eye"></i></a>
                        @include('partials.partager-bouton')
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            Aucun dossier trouvé
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach

<div style="margin-top:16px;">
    {{ $dossiers->links() }}
</div>

@include('partials.partage-informatif')
@endsection
