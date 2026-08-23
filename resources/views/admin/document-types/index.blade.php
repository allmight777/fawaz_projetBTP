@extends('layouts.admin')

@section('title', 'Gestion des types de documents')

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
    .btn-checklist {
        background: #d1fae5;
        color: #059669;
    }
    .btn-checklist:hover {
        background: #059669;
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
    .badge-validation { background: #fef3c7; color: #d97706; }
    .badge-simple { background: #dbeafe; color: #2563eb; }
    .badge-actif { background: #d1fae5; color: #059669; }
    .badge-inactif { background: #fee2e2; color: #dc2626; }
    .badge-categorie {
        background: #e5e7eb;
        color: #6b7280;
        font-size: 10px;
    }
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
        padding: 60px 20px;
        text-align: center;
        color: #888;
    }
    .empty-state i {
        font-size: 48px;
        display: block;
        margin-bottom: 16px;
        opacity: 0.3;
        color: #ff8c00;
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-file-alt"></i> Types de documents</h2>
    <a href="{{ route('admin.document-types.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i> Nouveau type
    </a>
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

<div class="table-container">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Mode</th>
                    <th>Critères</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documentTypes as $type)
                <tr>
                    <td><strong>{{ $type->code }}</strong></td>
                    <td>{{ $type->nom }}</td>
                    <td>
                        <span class="badge badge-categorie">
                            {{ ucfirst(str_replace('_', ' ', $type->categorie)) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $type->mode_traitement == 'validation' ? 'badge-validation' : 'badge-simple' }}">
                            {{ $type->mode_label }}
                        </span>
                    </td>
                    <td>
                        <span class="badge" style="background:#eff4ff; color:#2563eb;">
                            {{ $type->checklist_items_count }} critères
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $type->actif ? 'badge-actif' : 'badge-inactif' }}">
                            {{ $type->actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <a href="{{ route('admin.document-types.checklist', $type->id) }}"
                               class="btn-sm btn-checklist" title="Gérer les critères">
                                <i class="fas fa-list-check"></i>
                            </a>
                            <a href="{{ route('admin.document-types.edit', $type->id) }}"
                               class="btn-sm btn-edit" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.document-types.destroy', $type->id) }}"
                                  method="POST" style="display:inline-block;"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce type ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-delete" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            Aucun type de document trouvé
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
