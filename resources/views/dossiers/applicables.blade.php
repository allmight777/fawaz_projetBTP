@extends(auth()->user()->spaceLayout())

@section('title', 'Documents applicables')

@section('content')
<div class="page-header">
    <h1>Documents applicables</h1>
</div>

<div class="table-container">
    <div class="table-wrapper">
        <table data-filtres="type,structure">
            <thead>
                <tr>
                    <th>Identifiant</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Structure émettrice</th>
                    <th>Version</th>
                    <th>Applicable depuis</th>
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
                        <td>{{ $dossier->applicable_le->format('d/m/Y') }}</td>
                        <td>@include('partials.visas-colonne')</td>
                        <td><a href="{{ route('dossiers.show', $dossier) }}" class="btn-link">Consulter</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state">Aucun document applicable pour le moment.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
