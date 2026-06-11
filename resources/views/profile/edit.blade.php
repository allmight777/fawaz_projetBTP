@extends('layouts.admin')

@section('title', 'Mon profil')

@section('content')
<style>
    .profile-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .profile-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .profile-header {
        background: linear-gradient(135deg, #ff8c00, #ff6b00);
        padding: 30px;
        text-align: center;
        position: relative;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 48px;
        font-weight: 700;
        color: #ff8c00;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .profile-header h3 {
        color: white;
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .profile-header p {
        color: rgba(255,255,255,0.9);
        font-size: 14px;
    }

    .profile-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        margin-top: 10px;
    }

    .profile-body {
        padding: 30px;
    }

    .info-group {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-label {
        font-size: 13px;
        color: #888;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-label i {
        color: #ff8c00;
        width: 20px;
    }

    .info-value {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        padding-left: 28px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    label i {
        color: #ff8c00;
        margin-right: 8px;
    }

    input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s;
        font-family: inherit;
    }

    input:focus {
        outline: none;
        border-color: #ff8c00;
        box-shadow: 0 0 0 3px rgba(255,140,0,0.1);
    }

    input:disabled {
        background: #f5f5f5;
        cursor: not-allowed;
    }

    .btn-submit {
        background: linear-gradient(135deg, #ff8c00, #ff6b00);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.3s;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
    }

    .btn-danger {
        background: #dc3545;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.3s;
    }

    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-2px);
    }

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

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #ff8c00;
        font-size: 20px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    @media (max-width: 768px) {
        .profile-body {
            padding: 20px;
        }
        .form-row {
            grid-template-columns: 1fr;
        }
        .btn-submit, .btn-danger {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="profile-container">
    @if(session('status') === 'profile-updated')
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> Profil mis à jour avec succès !
        </div>
    @endif

    @if(session('status') === 'password-updated')
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> Mot de passe modifié avec succès !
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i> Veuillez corriger les erreurs ci-dessous.
        </div>
    @endif

    <!-- Informations personnelles -->
    <div class="profile-card">
 
        <div class="profile-body">
            <div class="info-group">
                <div class="info-label">
                    <i class="fas fa-user"></i> Nom complet
                </div>
                <div class="info-value">{{ Auth::user()->full_name }}</div>
            </div>
            <div class="info-group">
                <div class="info-label">
                    <i class="fas fa-envelope"></i> Adresse email
                </div>
                <div class="info-value">{{ Auth::user()->email }}</div>
            </div>
            <div class="info-group">
                <div class="info-label">
                    <i class="fas fa-calendar-alt"></i> Membre depuis
                </div>
                <div class="info-value">{{ Auth::user()->created_at->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Formulaire mise à jour profil -->
    <div class="profile-card">
        <div class="profile-body">
            <div class="section-title">
                <i class="fas fa-user-edit"></i>
                Modifier mes informations
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nom</label>
                        <input type="text" name="nom" value="{{ old('nom', Auth::user()->nom) }}" required>
                        @error('nom') <span style="color:#dc3545; font-size:12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Prénom</label>
                        <input type="text" name="prenom" value="{{ old('prenom', Auth::user()->prenom) }}">
                        @error('prenom') <span style="color:#dc3545; font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                    @error('email') <span style="color:#dc3545; font-size:12px;">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
            </form>
        </div>
    </div>

    <!-- Formulaire changement mot de passe -->
    <div class="profile-card">
        <div class="profile-body">
            <div class="section-title">
                <i class="fas fa-key"></i>
                Changer mon mot de passe
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Mot de passe actuel</label>
                    <input type="password" name="current_password" required>
                    @error('current_password') <span style="color:#dc3545; font-size:12px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Nouveau mot de passe</label>
                        <input type="password" name="password" required>
                        @error('password') <span style="color:#dc3545; font-size:12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-sync-alt"></i> Changer le mot de passe
                </button>
            </form>
        </div>
    </div>

    <!-- Suppression du compte -->
    <div class="profile-card">
        <div class="profile-body">
            <div class="section-title">
                <i class="fas fa-trash-alt" style="color:#dc3545;"></i>
                Supprimer mon compte
            </div>

            <p style="color: #666; margin-bottom: 20px; font-size: 14px;">
                Une fois votre compte supprimé, toutes vos données seront définitivement effacées.
            </p>

            <button type="button" class="btn-danger" onclick="document.getElementById('deleteAccountForm').style.display='block'">
                <i class="fas fa-trash-alt"></i> Supprimer mon compte
            </button>

            <form id="deleteAccountForm" method="POST" action="{{ route('profile.destroy') }}" style="display: none; margin-top: 20px;">
                @csrf
                @method('DELETE')

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirmez avec votre mot de passe</label>
                    <input type="password" name="password" placeholder="Entrez votre mot de passe" required>
                    <button type="submit" class="btn-danger" style="margin-top: 10px; width: auto;">
                        <i class="fas fa-check-circle"></i> Confirmer la suppression
                    </button>
                    <button type="button" class="btn-submit" style="margin-top: 10px; width: auto;" onclick="document.getElementById('deleteAccountForm').style.display='none'">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
