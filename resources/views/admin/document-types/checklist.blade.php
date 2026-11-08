@extends('layouts.admin')

@section('title', 'Gestion des critères - ' . $documentType->nom)

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
.badge-obligatoire { background: #fef3c7; color: #d97706; }
.badge-facultatif { background: #dbeafe; color: #2563eb; }
.type-info {
        background: #f8f7f4;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }
.type-info span {
        font-size: var(--fs-m);
        color: #555;
    }
.type-info strong {
        color: #1a1a1a;
    }
</style>

<div class="page-header">
    <div>
        <h2><i class="fas fa-list-check"></i> Critères de vérification</h2>
        <div class="subtitle">{{ $documentType->code }} - {{ $documentType->nom }}</div>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route('admin.document-types.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="type-info">
    <span><strong>Code :</strong> {{ $documentType->code }}</span>
    <span><strong>Mode :</strong> {{ $documentType->mode_label }}</span>
    <span><strong>Catégorie :</strong> {{ $documentType->libelle_categorie }}</span>
    <span><strong>Statut :</strong> {{ $documentType->actif ? 'Actif' : 'Inactif' }}</span>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<!-- Formulaire d'ajout -->
<div style="background:white; border-radius:16px; padding:20px 24px; margin-bottom:24px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
    <h4 style="font-size:16px; font-weight:700; color:#1a1a1a; margin-bottom:12px;">
        <i class="fas fa-plus-circle" style="color:#8b1e3f;"></i> Ajouter un critère
    </h4>
    <form action="{{ route('admin.document-types.checklist.store', $documentType->id) }}" method="POST" class="form-inline">
        @csrf
        <input type="text" name="libelle" placeholder="Libellé du critère..." required>
        <label>
            <input type="checkbox" name="obligatoire" value="1" checked>
            Obligatoire
        </label>
        <button type="submit" class="btn-submit">
            <i class="fas fa-plus"></i> Ajouter
        </button>
    </form>
</div>

<!-- Liste des critères -->
<div class="table-container">
    <div class="table-wrapper">
        <table data-no-filter>
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Libellé</th>
                    <th style="width:120px;">Type</th>
                    <th style="width:150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documentType->checklistItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->libelle }}</td>
                    <td>
                        <span class="badge {{ $item->obligatoire ? 'badge-obligatoire' : 'badge-facultatif' }}">
                            {{ $item->obligatoire ? 'Obligatoire' : 'Facultatif' }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <button class="btn-sm btn-edit" onclick="editItem({{ $item->id }}, '{{ $item->libelle }}', {{ $item->obligatoire ? 'true' : 'false' }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.document-types.checklist.destroy', [$documentType->id, $item->id]) }}"
                                  method="POST" style="display:inline-block;"
                                  onsubmit="return confirm('Supprimer ce critère ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <i class="fas fa-list-check"></i>
                            Aucun critère défini pour ce type de document
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal d'édition -->
<div class="modal" id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:white; border-radius:16px; padding:30px; max-width:500px; width:90%;">
        <h4 style="font-size:18px; font-weight:700; margin-bottom:16px;">
            <i class="fas fa-edit" style="color:#8b1e3f;"></i> Modifier le critère
        </h4>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom:16px;">
                <label style="display:block; font-weight:600; font-size: var(--fs-m); margin-bottom:4px;">Libellé <span class="required" style="color:#dc2626;">*</span></label>
                <input type="text" name="libelle" id="editLibelle" style="width:100%; padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size: var(--fs-m);">
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label style="display:flex; align-items:center; gap:8px; font-weight:600; font-size: var(--fs-m); cursor:pointer;">
                    <input type="checkbox" name="obligatoire" id="editObligatoire" value="1">
                    Obligatoire
                </label>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="closeEditModal()" style="background:#e5e7eb; color:#333; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">Annuler</button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editItem(id, libelle, obligatoire) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        const input = document.getElementById('editLibelle');
        const checkbox = document.getElementById('editObligatoire');

        form.action = `/admin/document-types/{{ $documentType->id }}/checklist/${id}`;
        input.value = libelle;
        checkbox.checked = obligatoire;

        modal.style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Fermer en cliquant en dehors
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
</script>
@endsection
