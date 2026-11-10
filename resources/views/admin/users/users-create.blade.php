@extends('layouts.admin')

@section('title', 'Ajouter un utilisateur')

@section('content')
<style>
.form-group-full {
        grid-column: span 2;
    }
label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: var(--fs-m);
    }
label i {
        color: #8b1e3f;
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
        font-size: var(--fs-m);
        transition: all 0.3s;
        font-family: inherit;
        background: #fafaf8;
    }
input:focus, select:focus {
        outline: none;
        border-color: #8b1e3f;
        box-shadow: 0 0 0 3px rgba(139,30,63,0.1);
        background: white;
    }
.info-text {
        background: #e7f3ff;
        padding: 12px 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        color: #0066cc;
        font-size: var(--fs-m);
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
        font-size: var(--fs-m);
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
@media (max-width: 768px) {
.form-group-full {
            grid-column: span 1;
        }
.section-divider {
            grid-column: span 1;
        }
}
</style>

<div class="form-container">
    <div class="form-title">
        <i class="fas fa-user-plus"></i> Ajouter un utilisateur
    </div>

    <div class="info-text">
        <i class="fas fa-info-circle"></i>
        <span>Le mot de passe par défaut est <strong>"password"</strong>. L'utilisateur devra le modifier après sa première connexion.</span>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="form-row fluide">
            <!-- ===== IDENTITÉ ===== -->
            <div class="section-divider">
                <i class="fas fa-id-card"></i> Identité
            </div>

            <div class="form-group">
                <label><i class="fas fa-user"></i> Nom <span class="required">*</span></label>
                <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Nom de famille" required>
                @error('nom')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-user"></i> Prénom</label>
                <input type="text" name="prenom" value="{{ old('prenom') }}" placeholder="Prénom">
                @error('prenom')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email <span class="required">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="exemple@email.com" required>
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
                        <option value="{{ $structure->id }}" {{ old('structure_id') == $structure->id ? 'selected' : '' }}>
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
                    <option value="responsable_organisme" {{ old('categorie_role') == 'responsable_organisme' ? 'selected' : '' }}>
                        Responsable d'organisme
                    </option>
                    <option value="collaborateur" {{ old('categorie_role') == 'collaborateur' ? 'selected' : '' }}>
                        Collaborateur
                    </option>
                </select>
                @error('categorie_role')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-briefcase"></i> Fonction</label>
                <input type="text" name="fonction" value="{{ old('fonction') }}" placeholder="Ex: Ingénieur, Chef de projet...">
                @error('fonction')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-microscope"></i> Spécialité</label>
                <input type="text" name="specialite" value="{{ old('specialite') }}" placeholder="Ex: Structure, Électricité...">
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
                        <option value="{{ $lot->id }}" {{ old('lot_id') == $lot->id ? 'selected' : '' }}>
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
                    <option value="1" {{ old('actif', '1') == '1' ? 'selected' : '' }}>Actif</option>
                    <option value="0" {{ old('actif') == '0' ? 'selected' : '' }}>Inactif</option>
                </select>
                @error('actif')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Créer l'utilisateur
            </button>
            <a href="{{ route('admin.users') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Annuler
            </a>
        </div>
    </form>
</div>
@endsection
