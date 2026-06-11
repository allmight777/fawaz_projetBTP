@extends('layouts.admin')

@section('title', 'Ajouter un lot')

@section('content')
<style>
    .form-container {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        max-width: 600px;
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
    .form-group {
        margin-bottom: 20px;
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
    input, textarea, select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s;
        font-family: inherit;
    }
    input:focus, textarea:focus, select:focus {
        outline: none;
        border-color: #ff8c00;
        box-shadow: 0 0 0 3px rgba(255,140,0,0.1);
    }
    textarea {
        resize: vertical;
        min-height: 100px;
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
    @media (max-width: 768px) {
        .form-container {
            padding: 20px;
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
        <i class="fas fa-layer-group"></i> Ajouter un lot
    </div>
    <div class="form-subtitle">
        Créez un nouveau lot pour l'affectation des contrôleurs
    </div>

    <form method="POST" action="{{ route('admin.lots.store') }}">
        @csrf

        <div class="form-group">
            <label><i class="fas fa-tag"></i> Code du lot <span class="required">*</span></label>
            <input type="text" name="code" value="{{ old('code') }}" placeholder="Ex: L01, L02, LOT-A..." required>
            @error('code')
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-group">
            <label><i class="fas fa-layer-group"></i> Nom du lot <span class="required">*</span></label>
            <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Ex: Lot 1 - Terrassement" required>
            @error('nom')
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-group">
            <label><i class="fas fa-align-left"></i> Description</label>
            <textarea name="description" placeholder="Description détaillée du lot...">{{ old('description') }}</textarea>
            @error('description')
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-group">
            <label><i class="fas fa-toggle-on"></i> Statut</label>
            <select name="actif">
                <option value="1" {{ old('actif', '1') == '1' ? 'selected' : '' }}>Actif</option>
                <option value="0" {{ old('actif') == '0' ? 'selected' : '' }}>Inactif</option>
            </select>
            @error('actif')
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Enregistrer le lot
            </button>
            <a href="{{ route('admin.lots') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Annuler
            </a>
        </div>
    </form>
</div>
@endsection
