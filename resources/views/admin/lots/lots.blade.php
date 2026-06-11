@extends('layouts.admin')

@section('title', 'Gestion des lots')

@section('content')
<style>
    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 12px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 4px solid #28a745;
    }
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        padding: 12px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 4px solid #dc3545;
    }
    .btn-danger {
        background: #dc3545;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        cursor: pointer;
    }
    .btn-danger:hover {
        background: #c82333;
    }
    .btn-warning {
        background: #ffc107;
        color: #000;
        padding: 6px 12px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
    }
    .btn-warning:hover {
        background: #e0a800;
    }
    .btn-sm {
        font-size: 12px;
        padding: 5px 10px;
    }
    .table-container {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .table-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
    }
    .btn-primary {
        background: linear-gradient(135deg, #ff8c00, #ff6b00);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        text-align: left;
        padding: 15px;
        background: #f8f9fa;
        color: #555;
        font-weight: 600;
        font-size: 13px;
    }
    td {
        padding: 15px;
        border-bottom: 1px solid #eee;
        color: #333;
    }
    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-actif {
        background: rgba(52,211,153,0.1);
        color: #34d399;
    }
    .badge-inactif {
        background: rgba(239,68,68,0.1);
        color: #ef4444;
    }
    .action-btns {
        display: flex;
        gap: 8px;
    }
    @media (max-width: 768px) {
        .table-container {
            padding: 15px;
        }
        th, td {
            padding: 10px;
        }
        .action-btns {
            flex-direction: column;
            gap: 5px;
        }
    }
</style>



<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-layer-group"></i> Liste des lots</h3>
        <a href="{{ route('admin.lots.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> Ajouter un lot
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Nombre d'utilisateurs</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lots as $lot)
                <tr>
                    <td><strong>{{ $lot->code }}</strong></td>
                    <td>{{ $lot->nom }}</td>
                    <td>{{ $lot->description ?? '-' }}</td>
                    <td>
                        <span class="badge badge-actif">
                            {{ $lot->users->count() }} utilisateur(s)
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $lot->actif ? 'badge-actif' : 'badge-inactif' }}">
                            {{ $lot->actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="action-btns">
                        <a href="{{ route('admin.lots.edit', $lot) }}" class="btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <form action="{{ route('admin.lots.destroy', $lot) }}" method="POST" style="display:inline" onsubmit="return confirm('Supprimer ce lot ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 48px; color: #ccc;"></i>
                        <p style="margin-top: 10px; color: #999;">Aucun lot trouvé</p>
                        <a href="{{ route('admin.lots.create') }}" class="btn-primary" style="margin-top: 10px;">
                            <i class="fas fa-plus"></i> Créer votre premier lot
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
