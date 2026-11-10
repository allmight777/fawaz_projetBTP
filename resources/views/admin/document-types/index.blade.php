@extends('layouts.admin')

@section('title', 'Gestion des types de documents')

@section('content')
<style>
.btn-sm {
        padding: 6px 12px;
        font-size: var(--fs-s);
        border-radius: 6px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.3s ease;
    }
.btn-edit {
        background: #dbeafe;
        color: #2563eb;
    }
.btn-edit:hover {
        background: #2563eb;
        color: white;
    }
.btn-checklist {
        background: #d1fae5;
        color: #059669;
    }
.btn-checklist:hover {
        background: #059669;
        color: white;
    }
.badge-validation { background: #fef3c7; color: #d97706; }
.badge-simple { background: #dbeafe; color: #2563eb; }
.badge-categorie {
        background: #e5e7eb;
        color: #6b7280;
        font-size: var(--fs-s);
    }
</style>

<div class="page-header">
    <h1>Types de documents</h1>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route('admin.document-categories.index') }}" class="btn">Catégories</a>
        <a href="{{ route('admin.document-types.create') }}" class="btn-submit" style="width:auto; display:inline-block;">Nouveau type</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

{{-- Filtre par catégorie (sans rechargement) --}}
<div class="cat-filter" id="catFilter">
    <button type="button" class="cat-chip on" data-cat="">Toutes <b>{{ $documentTypes->count() }}</b></button>
    @foreach($groupes as $cle => $types)
        <button type="button" class="cat-chip" data-cat="{{ $cle }}">{{ $categories[$cle] ?? ucfirst(str_replace('_', ' ', $cle)) }} <b>{{ $types->count() }}</b></button>
    @endforeach
</div>

@forelse($groupes as $cle => $types)
    <section class="cat-group" data-cat="{{ $cle }}">
        <div class="table-container">
            <div class="table-header">
                <h3>{{ $categories[$cle] ?? ucfirst(str_replace('_', ' ', $cle)) }}</h3>
                <span class="cat-count">{{ $types->count() }} type{{ $types->count() > 1 ? 's' : '' }}</span>
            </div>
            <div class="table-wrapper">
                <table data-filtres="mode,statut">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Mode</th>
                            <th>Critères</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($types as $type)
                        <tr>
                            <td><strong>{{ $type->code }}</strong></td>
                            <td>{{ $type->nom }}</td>
                            <td>
                                <span class="badge {{ $type->mode_traitement == 'validation' ? 'badge-validation' : 'badge-simple' }}">{{ $type->mode_label }}</span>
                            </td>
                            <td><span class="badge badge-controle">{{ $type->checklist_items_count }} critère{{ $type->checklist_items_count > 1 ? 's' : '' }}</span></td>
                            <td>
                                <span class="badge {{ $type->actif ? 'badge-actif' : 'badge-inactif' }}">{{ $type->actif ? 'Actif' : 'Inactif' }}</span>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="{{ route('admin.document-types.checklist', $type->id) }}" class="btn-sm btn-checklist" title="Gérer les critères"><i class="fas fa-list-check"></i></a>
                                    <a href="{{ route('admin.document-types.edit', $type->id) }}" class="btn-sm btn-edit" title="Modifier"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.document-types.destroy', $type->id) }}" method="POST" style="display:inline-block;"
                                          data-confirm="Ce type de document sera supprimé." data-confirm-title="Supprimer ce type ?" data-confirm-ok="Supprimer" data-confirm-tone="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm btn-delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@empty
    <div class="empty-state">Aucun type de document trouvé</div>
@endforelse

<script>
    (function () {
        var chips = document.querySelectorAll('#catFilter .cat-chip');
        var groupes = document.querySelectorAll('.cat-group');
        function filtrer(cat) {
            chips.forEach(function (c) { c.classList.toggle('on', c.dataset.cat === cat); });
            groupes.forEach(function (g) { g.hidden = cat !== '' && g.dataset.cat !== cat; });
            try { history.replaceState(null, '', cat ? '#' + cat : location.pathname); } catch (e) {}
        }
        chips.forEach(function (c) { c.addEventListener('click', function () { filtrer(c.dataset.cat); }); });
        var depart = location.hash.slice(1);
        if (depart && document.querySelector('.cat-group[data-cat="' + depart + '"]')) filtrer(depart);
    })();
</script>
@endsection
