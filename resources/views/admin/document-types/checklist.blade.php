@extends('layouts.admin')

@section('title', 'Gestion des critères - ' . $documentType->nom)

@section('content')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .page-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }
    .page-header h2 i {
        color: #ff8c00;
        margin-right: 10px;
    }
    .page-header .subtitle {
        font-size: 14px;
        color: #888;
        font-weight: 400;
    }
    .btn-primary {
        background: linear-gradient(135deg, #ff8c00, #ff6b00);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255,140,0,0.3);
        color: white;
    }
    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
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
    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }
    .btn-delete:hover {
        background: #dc2626;
        color: white;
    }
    .btn-back {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background: #5a6268;
        color: white;
    }
    .btn-submit {
        background: linear-gradient(135deg, #ff8c00, #ff6b00);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255,140,0,0.3);
    }
    .table-container {
        background: white;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .table-wrapper {
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    th {
        text-align: left;
        padding: 12px 14px;
        background: #f8f7f4;
        color: #1a1a1a;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 2px solid #e8e5e0;
    }
    td {
        padding: 12px 14px;
        border-bottom: 1px solid #f0eeea;
        color: #333;
    }
    tr:hover td {
        background: #faf9f6;
    }
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-block;
    }
    .badge-obligatoire { background: #fef3c7; color: #d97706; }
    .badge-facultatif { background: #dbeafe; color: #2563eb; }
    .alert {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .alert-success {
        background: #d1fae5;
        color: #065f46;
    }
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
    }
    .empty-state {
        padding: 40px 20px;
        text-align: center;
        color: #888;
    }
    .empty-state i {
        font-size: 40px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.3;
        color: #ff8c00;
    }
    .form-inline {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    .form-inline input[type="text"] {
        padding: 8px 14px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        flex: 1;
        min-width: 200px;
    }
    .form-inline input[type="text"]:focus {
        outline: none;
        border-color: #ff8c00;
        box-shadow: 0 0 0 3px rgba(255,140,0,0.1);
    }
    .form-inline label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #333;
        cursor: pointer;
    }
    .form-inline label input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #ff8c00;
    }
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
        font-size: 13px;
        color: #555;
    }
    .type-info strong {
        color: #1a1a1a;
    }
    @media (max-width: 768px) {
        .form-inline {
            flex-direction: column;
            align-items: stretch;
        }
        .form-inline input[type="text"] {
            min-width: auto;
        }
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
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
    <span><strong>Catégorie :</strong> {{ ucfirst(str_replace('_', ' ', $documentType->categorie)) }}</span>
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
        <i class="fas fa-plus-circle" style="color:#ff8c00;"></i> Ajouter un critère
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
        <table>
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
            <i class="fas fa-edit" style="color:#ff8c00;"></i> Modifier le critère
        </h4>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom:16px;">
                <label style="display:block; font-weight:600; font-size:13px; margin-bottom:4px;">Libellé <span class="required" style="color:#dc2626;">*</span></label>
                <input type="text" name="libelle" id="editLibelle" style="width:100%; padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size:14px;">
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label style="display:flex; align-items:center; gap:8px; font-weight:600; font-size:13px; cursor:pointer;">
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
