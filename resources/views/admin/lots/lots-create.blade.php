@extends('layouts.admin')

@section('title', 'Ajouter un lot')

@section('content')
<style>
label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
label i {
        color: #8b1e3f;
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
        font-size: var(--fs-m);
        transition: all 0.3s;
        font-family: inherit;
    }
input:focus, textarea:focus, select:focus {
        outline: none;
        border-color: #8b1e3f;
        box-shadow: 0 0 0 3px rgba(139,30,63,0.1);
    }
textarea {
        resize: vertical;
        min-height: 100px;
    }
</style>

<div class="form-container">
    <div class="form-title">
        <i class="fas fa-layer-group"></i> Ajouter un lot
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
