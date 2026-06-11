@extends('layouts.admin')

@section('title', 'Utilisateurs')

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
    .badge-admin { background: rgba(255,68,68,0.1); color: #ff4444; }
    .badge-chef { background: rgba(255,140,0,0.1); color: #ff8c00; }
    .badge-controleur { background: rgba(52,211,153,0.1); color: #34d399; }
    .badge-actif { background: rgba(52,211,153,0.1); color: #34d399; }
    .badge-inactif { background: rgba(239,68,68,0.1); color: #ef4444; }
    .action-btns {
        display: flex;
        gap: 8px;
    }
    .btn-edit {
        background: #ffc107;
        color: #000;
        padding: 5px 10px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    @media (max-width: 768px) {
        .table-container { padding: 15px; overflow-x: auto; }
        th, td { padding: 10px; }
        .action-btns { flex-direction: column; gap: 5px; }
    }
</style>


<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-users"></i> Liste des utilisateurs</h3>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> Ajouter
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Lot</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td><strong>{{ $user->full_name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge
                            @if($user->role == 'ADMIN') badge-admin
                            @elseif($user->role == 'CHEF LOT') badge-chef
                            @else badge-controleur @endif">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td>{{ $user->lot->nom ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $user->actif ? 'badge-actif' : 'badge-inactif' }}">
                            {{ $user->actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="action-btns">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-edit">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete" onclick="return confirm('Supprimer cet utilisateur ?')">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
