@extends('layouts.admin')

@section('title', 'Gestion des utilisateurs')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:15px;">
    <div>
        <h2 style="font-size:24px; font-weight:700; color:#1a1a1a;">
            <i class="fas fa-users"></i> Gestion des utilisateurs
        </h2>
        <p style="color:#666; margin-top:4px;">Gérez tous les utilisateurs de la plateforme</p>
    </div>
    <a href="{{ route('admin.users.create') }}" style="background:linear-gradient(135deg, #ff8c00, #ff6b00); color:white; padding:12px 24px; border-radius:10px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:8px;">
        <i class="fas fa-user-plus"></i> Ajouter un utilisateur
    </a>
</div>

<div style="background:white; border-radius:20px; padding:25px; box-shadow:0 5px 20px rgba(0,0,0,0.05); overflow-x:auto;">
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

    <table style="width:100%; border-collapse:collapse; font-size:14px;">
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
                        <span style="font-size:11px; color:#888; display:block;">
                            {{ $user->structure?->type_label ?? '' }}
                        </span>
                    </td>
                    <td style="padding:12px 15px; color:#666;">
                        {{ $user->fonction ?? '-' }}
                        @if($user->specialite)
                            <span style="font-size:11px; color:#888; display:block;">
                                <i class="fas fa-microscope"></i> {{ $user->specialite }}
                            </span>
                        @endif
                    </td>
                    <td style="padding:12px 15px;">
                        <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;
                            @if($user->role == 'ADMIN') background:#e7f3ff; color:#0066cc;
                            @elseif($user->role == 'CHEF LOT') background:#fff3e0; color:#e65100;
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
                        <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;
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
                        <a href="{{ route('admin.users.edit', $user) }}" style="color:#ff8c00; margin-right:10px; text-decoration:none;" title="Modifier">
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
