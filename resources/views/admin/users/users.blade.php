@extends('layouts.admin')

@section('title', 'Gestion des utilisateurs')

@section('content')
<div class="page-header">
    <h1>Gestion des utilisateurs</h1>
    <a href="{{ route('admin.users.create') }}" class="btn-submit" style="width:auto; display:inline-block;">Ajouter un utilisateur</a>
</div>

<div class="table-container" style="background:white; border-radius:20px; padding:25px; box-shadow:0 5px 20px rgba(0,0,0,0.05); overflow-x:auto;">
    @if(session('success'))
        <div style="background:#d4edda; color:#155724; padding:12px 16px; border-radius:10px; margin-bottom:20px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#f8d7da; color:#721c24; padding:12px 16px; border-radius:10px; margin-bottom:20px;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <table data-filtres="structure,role,statut" style="width:100%; border-collapse:collapse; font-size: var(--fs-m);">
        <thead>
            <tr style="background:#f8f7f4; border-radius:10px;">
                <th style="padding:12px 15px; text-align:left; font-weight:600; color:#333;">Nom</th>
                <th style="padding:12px 15px; text-align:left; font-weight:600; color:#333;">Email</th>
                <th style="padding:12px 15px; text-align:left; font-weight:600; color:#333;">Structure</th>
                <th style="padding:12px 15px; text-align:left; font-weight:600; color:#333;">Fonction</th>
                <th style="padding:12px 15px; text-align:left; font-weight:600; color:#333;">Rôle</th>
                <th style="padding:12px 15px; text-align:left; font-weight:600; color:#333;">Statut</th>
                <th style="padding:12px 15px; text-align:center; font-weight:600; color:#333;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:12px 15px; font-weight:500;">
                        {{ $user->full_name }}
                    </td>
                    <td style="padding:12px 15px; color:#666;">
                        {{ $user->email }}
                    </td>
                    <td style="padding:12px 15px; color:#666;">
                        {{ $user->structure?->nom ?? '-' }}
                        <span style="font-size: var(--fs-s); color:#888; display:block;">
                            {{ $user->structure?->type_label ?? '' }}
                        </span>
                    </td>
                    <td style="padding:12px 15px; color:#666;">
                        {{ $user->fonction ?? '-' }}
                        @if($user->specialite)
                            <span style="font-size: var(--fs-s); color:#888; display:block;">
                                <i class="fas fa-microscope"></i> {{ $user->specialite }}
                            </span>
                        @endif
                    </td>
                    <td style="padding:12px 15px;">
                        <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-size: var(--fs-s); font-weight:600;
                            @if($user->role == 'ADMIN') background:#e7f3ff; color:#0066cc;
                            @elseif($user->role == 'CHEF LOT') background:#f8e6ec; color:#8b1e3f;
                            @elseif($user->role == 'CONTROLEUR') background:#e8f5e9; color:#2e7d32;
                            @else background:#f5f5f5; color:#888;
                            @endif">
                            {{ $user->role }}
                            @if($user->categorie_role)
                                <span style="font-weight:400; opacity:0.7;">
                                    ({{ $user->categorie_role == 'responsable_organisme' ? 'Responsable' : 'Collaborateur' }})
                                </span>
                            @endif
                        </span>
                    </td>
                    <td style="padding:12px 15px;">
                        <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-size: var(--fs-s); font-weight:600;
                            @if($user->role == 'EN_ATTENTE') background:#fff3cd; color:#856404;
                            @elseif($user->statut === 'actif') background:#d4edda; color:#155724;
                            @elseif($user->statut === 'desactive') background:#f8d7da; color:#721c24;
                            @else background:#f5f5f5; color:#666;
                            @endif">
                            @if($user->role == 'EN_ATTENTE')
                                En attente
                            @elseif($user->statut === 'actif')
                                Actif
                            @elseif($user->statut === 'desactive')
                                Désactivé
                            @else
                                Inactif
                            @endif
                        </span>
                    </td>
                    <td style="padding:12px 15px; text-align:center;">
                        <a href="{{ route('admin.users.edit', $user) }}" style="color:#8b1e3f; margin-right:10px; text-decoration:none;" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none; border:none; color:#dc3545; cursor:pointer;" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="padding:40px; text-align:center; color:#888;">
                        <i class="fas fa-users" style="font-size:40px; display:block; margin-bottom:10px; opacity:0.3;"></i>
                        Aucun utilisateur trouvé
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
