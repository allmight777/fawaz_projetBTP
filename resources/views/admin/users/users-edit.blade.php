@extends('layouts.admin')

@section('title', 'Modifier un utilisateur')

@section('content')
<style>
    .form-container {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        max-width: 800px;
        margin: 0 auto;
    }
    .form-title {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 10px;
    }
    .form-subtitle {
        color: #666;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group-full {
        grid-column: span 2;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    label i {
        color: #ff8c00;
        margin-right: 8px;
    }
    .required {
        color: #dc3545;
    }
    input, select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s;
        font-family: inherit;
    }
    input:focus, select:focus {
        outline: none;
        border-color: #ff8c00;
        box-shadow: 0 0 0 3px rgba(255,140,0,0.1);
    }
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
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
    }
    .btn-submit:hover {
        transform: translateY(-2px);
    }
    .btn-back {
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-back:hover {
        background: #5a6268;
    }
    .error {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .info-text {
        background: #e7f3ff;
        padding: 12px 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        color: #0066cc;
        font-size: 14px;
    }
    @media (max-width: 768px) {
        .form-container {
            padding: 20px;
        }
        .form-row {
            grid-template-columns: 1fr;
        }
        .form-group-full {
            grid-column: span 1;
        }
        .form-actions {
            flex-direction: column;
        }
        .btn-submit, .btn-back {
            justify-content: center;
        }
    }
</style>

<div class="form-container">
    <div class="form-title">
        <i class="fas fa-user-edit"></i> Modifier un utilisateur
    </div>
    <div class="form-subtitle">
        Modifiez les informations de l'utilisateur
    </div>

    <div class="info-text">
        <i class="fas fa-info-circle"></i> Le mot de passe par défaut est <strong>"password"</strong>. L'utilisateur pourra le modifier après sa première connexion.
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Nom <span class="required">*</span></label>
                <input type="text" name="nom" value="{{ old('nom', $user->nom) }}" required>
                @error('nom')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-user"></i> Prénom</label>
                <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}">
                @error('prenom')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email <span class="required">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-tag"></i> Rôle <span class="required">*</span></label>
                <select name="role" required>
                    <option value="CONTROLEUR" {{ old('role', $user->role) == 'CONTROLEUR' ? 'selected' : '' }}>Contrôleur</option>
                    <option value="CHEF LOT" {{ old('role', $user->role) == 'CHEF LOT' ? 'selected' : '' }}>Chef Lot</option>
                    <option value="ADMIN" {{ old('role', $user->role) == 'ADMIN' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-layer-group"></i> Lot</label>
                <select name="lot_id">
                    <option value="">Aucun</option>
                    @foreach($lots as $lot)
                        <option value="{{ $lot->id }}" {{ old('lot_id', $user->lot_id) == $lot->id ? 'selected' : '' }}>
                            {{ $lot->code }} - {{ $lot->nom }}
                        </option>
                    @endforeach
                </select>
                @error('lot_id')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-toggle-on"></i> Statut</label>
                <select name="actif">
                    <option value="1" {{ old('actif', $user->actif) == '1' ? 'selected' : '' }}>Actif</option>
                    <option value="0" {{ old('actif', $user->actif) == '0' ? 'selected' : '' }}>Inactif</option>
                </select>
                @error('actif')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Mettre à jour
            </button>
            <a href="{{ route('admin.users') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Annuler
            </a>
        </div>
    </form>
</div>
@endsection
