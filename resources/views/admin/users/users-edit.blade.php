@extends('layouts.admin')

@section('title', 'Modifier un utilisateur')

@section('content')
<style>
    .form-container {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        max-width: 900px;
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
        font-size: 13px;
    }
    label i {
        color: #ff8c00;
        margin-right: 8px;
    }
    .required {
        color: #dc3545;
    }
    input, select, textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s;
        font-family: inherit;
        background: #fafaf8;
    }
    textarea {
        min-height: 80px;
        resize: vertical;
    }
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #ff8c00;
        box-shadow: 0 0 0 3px rgba(255,140,0,0.1);
        background: white;
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
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(255,140,0,0.3);
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
        transition: all 0.3s;
    }
    .btn-back:hover {
        background: #5a6268;
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
        transition: all 0.3s;
        margin-left: auto;
    }
    .btn-danger:hover {
        background: #c82333;
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
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .info-text i {
        margin-top: 2px;
    }
    .section-divider {
        grid-column: span 2;
        border-top: 1px dashed #ddd;
        padding-top: 10px;
        margin-bottom: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .user-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background: #e7f3ff;
        color: #0066cc;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-badge.active {
        background: #d4edda;
        color: #155724;
    }
    .status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
    }
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    .raison-field {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    .raison-field.visible {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
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
        .btn-submit, .btn-back, .btn-danger {
            justify-content: center;
            margin-left: 0;
        }
        .section-divider {
            grid-column: span 1;
        }
    }
</style>

<div class="form-container">
    <div class="form-title">
        <i class="fas fa-user-edit"></i> Modifier un utilisateur
    </div>
    <div class="form-subtitle">
        Modifiez les informations de l'utilisateur <span class="user-badge">{{ $user->full_name }}</span>
    </div>

    <div class="info-text">
        <i class="fas fa-info-circle"></i>
        <span>Le mot de passe par défaut est <strong>"password"</strong>. L'utilisateur pourra le modifier après sa première connexion.</span>
    </div>

    <!-- Statut actuel -->
    <div style="background:#f8f7f4; border-radius:12px; padding:15px 20px; margin-bottom:20px; display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
        <span style="font-weight:600; color:#333;">Statut actuel :</span>
        <span class="status-badge @if($user->role == 'EN_ATTENTE') pending @elseif($user->statut === 'actif') active @else inactive @endif">
            @if($user->role == 'EN_ATTENTE')
                <i class="fas fa-clock"></i> En attente
            @elseif($user->statut === 'actif')
                <i class="fas fa-check-circle"></i> Actif
            @elseif($user->statut === 'desactive')
                <i class="fas fa-ban"></i> Désactivé
            @else
                <i class="fas fa-times-circle"></i> Inactif
            @endif
        </span>
        @if($user->role != 'EN_ATTENTE')
            <span style="font-size:13px; color:#888;">
                Dernière modification : {{ $user->updated_at->format('d/m/Y H:i') }}
            </span>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="form-row">
            <!-- ===== IDENTITÉ ===== -->
            <div class="section-divider">
                <i class="fas fa-id-card"></i> Identité
            </div>

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

            <!-- ===== STRUCTURE ===== -->
            <div class="section-divider">
                <i class="fas fa-building"></i> Structure & Rôle
            </div>

            <div class="form-group">
                <label><i class="fas fa-building"></i> Structure / Organisme <span class="required">*</span></label>
                <select name="structure_id" required>
                    <option value="">— Sélectionnez une structure —</option>
                    @foreach($structures as $structure)
                        <option value="{{ $structure->id }}" {{ old('structure_id', $user->structure_id) == $structure->id ? 'selected' : '' }}>
                            {{ $structure->nom }} ({{ ucfirst(str_replace('_', ' ', $structure->type)) }})
                        </option>
                    @endforeach
                </select>
                @error('structure_id')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-user-tag"></i> Catégorie de rôle <span class="required">*</span></label>
                <select name="categorie_role" required>
                    <option value="">— Sélectionnez —</option>
                    <option value="responsable_organisme" {{ old('categorie_role', $user->categorie_role) == 'responsable_organisme' ? 'selected' : '' }}>
                        Responsable d'organisme
                    </option>
                    <option value="collaborateur" {{ old('categorie_role', $user->categorie_role) == 'collaborateur' ? 'selected' : '' }}>
                        Collaborateur
                    </option>
                </select>
                @error('categorie_role')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-briefcase"></i> Fonction</label>
                <input type="text" name="fonction" value="{{ old('fonction', $user->fonction) }}" placeholder="Ex: Ingénieur, Chef de projet...">
                @error('fonction')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-microscope"></i> Spécialité</label>
                <input type="text" name="specialite" value="{{ old('specialite', $user->specialite) }}" placeholder="Ex: Structure, Électricité...">
                @error('specialite')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <!-- ===== AFFECTATION ===== -->
            <div class="section-divider">
                <i class="fas fa-link"></i> Affectation
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

            <!-- ===== STATUT ===== -->
            <div class="section-divider">
                <i class="fas fa-toggle-on"></i> Statut du compte
            </div>

            <div class="form-group">
                <label><i class="fas fa-toggle-on"></i> Statut <span class="required">*</span></label>
                <select name="statut" id="status-select" required>
                    <option value="actif" {{ old('statut', $user->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ old('statut', $user->statut) == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    <option value="desactive" {{ old('statut', $user->statut) == 'desactive' ? 'selected' : '' }}>Désactivé</option>
                    @if($user->role == 'EN_ATTENTE')
                        <option value="rejected" {{ old('statut') == 'rejected' ? 'selected' : '' }}>Refuser</option>
                    @endif
                </select>
                @error('statut')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <!-- Champ raison (visible si statut = Inactif ou Refuser) -->
            <div class="form-group form-group-full raison-field" id="raison-field">
                <label><i class="fas fa-comment"></i> Raison <span class="required">*</span></label>
                <textarea name="raison" id="raison-text" placeholder="Veuillez indiquer la raison de cette modification de statut..."></textarea>
                <div style="font-size:12px; color:#888; margin-top:4px;">
                    <i class="fas fa-info-circle"></i> Cette raison sera envoyée par email à l'utilisateur.
                </div>
                @error('raison')
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
            <button type="button" class="btn-danger" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) document.getElementById('delete-form').submit();">
                <i class="fas fa-trash"></i> Supprimer
            </button>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status-select');
        const raisonField = document.getElementById('raison-field');
        const raisonText = document.getElementById('raison-text');

        function toggleRaisonField() {
            const value = statusSelect.value;
            if (value === 'inactif' || value === 'desactive' || value === 'rejected') {
                raisonField.classList.add('visible');
                raisonText.setAttribute('required', 'required');
            } else {
                raisonField.classList.remove('visible');
                raisonText.removeAttribute('required');
            }
        }

        statusSelect.addEventListener('change', toggleRaisonField);
        toggleRaisonField();
    });
</script>
@endsection
