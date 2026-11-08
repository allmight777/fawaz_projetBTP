@extends('layouts.entreprise')

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
        <a href="{{ route('entreprise.documents') }}" class="btn-outline-blue">
            <i class="fas fa-file-alt"></i> Voir les documents
        </a>
    </div>
</div>

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
                @forelse($dossiers as $dossier)
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
                        <a href="{{ route('entreprise.dossiers.show', $dossier->id) }}" class="btn-link" title="Voir le dossier">
                            <i class="fas fa-eye"></i> Voir
                        </a>
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
    <div style="margin-top:16px;">
        {{ $dossiers->links() }}
    </div>
</div>
@endsection
