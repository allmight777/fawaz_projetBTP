@extends(auth()->user()->spaceLayout())

@section('title', 'Documents reçus')

@section('content')
<style>
.badge-bureau_etudes { background: #ede9fe; color: #7c3aed; }
.badge-maitre_ouvrage { background: #fce7f3; color: #db2777; }
.badge-bureau_controle { background: #fef3c7; color: #d97706; }
.badge-maitre_oeuvre { background: #ede9fe; color: #6d4fc2; }
.badge-statut {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: var(--fs-s);
        font-weight: 600;
        text-transform: uppercase;
    }
.badge-soumis { background: #dbeafe; color: #2563eb; }
.badge-en_cours { background: #fef3c7; color: #d97706; }
.badge-en_analyse { background: #dbeafe; color: #2563eb; }
.badge-a_corriger { background: #fee2e2; color: #dc2626; }
</style>



<div class="table-container">
    <div class="table-wrapper">
        <table data-filtres="statut,type,structure,utilisateur,date">
            <thead>
                <tr>
                    <th>Identifiant</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Envoyé par</th>
                    <th>Structure d'origine</th>
                    <th>Statut</th>
                    <th>Visas</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dossiers as $dossier)
                @php
                    $structureType = $dossier->creePar->structure->type ?? null;
                    $structureLabels = [
                        'entreprise' => 'Entreprise',
                        'bureau_etudes' => "Bureau d'Études",
                        'maitre_ouvrage' => "Maître d'Ouvrage",
                        'bureau_controle' => 'Bureau de Contrôle',
                        'maitre_oeuvre' => "Maître d'Œuvre",
                    ];
                @endphp
                <tr>
                    <td><span style="font-family:monospace; font-size: var(--fs-s); color:var(--accent);">{{ $dossier->identifiant_affiche ?? '#'.$dossier->id }}</span></td>
                    <td>{{ Str::limit($dossier->titre, 30) }}</td>
                    <td>{{ $dossier->documentType->nom ?? '-' }}</td>
                    <td>{{ $dossier->creePar->full_name ?? '-' }}</td>
                    <td>
                        @if($structureType)
                            <span class="badge badge-{{ $structureType }}">{{ $structureLabels[$structureType] ?? $structureType }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <span class="badge-statut badge-{{ $dossier->statut }}">{{ $dossier->statut_label }}</span>
                    </td>
                    <td>@include('partials.visas-colonne')</td>
                    <td style="font-size: var(--fs-m); color:#888;">{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ sroute('cheflot.documents.recus.show', $dossier->id) }}" class="btn-link">
                            <i class="fas fa-eye"></i> Analyser
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            Aucun document reçu pour le moment
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
