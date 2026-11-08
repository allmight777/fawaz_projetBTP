@extends('layouts.admin')

@section('title', 'Gestion des lots')

@section('content')
<style>
.btn-warning {
        background: var(--accent);
        color: #fff;
        padding: 6px 12px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
    }
.btn-warning:hover {
        background: var(--accent-d);
    }
.btn-sm {
        font-size: var(--fs-s);
        padding: 5px 10px;
    }
.action-btns {
        display: flex;
        gap: 8px;
    }
@media (max-width: 768px) {
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
        <table data-filtres="statut">
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
