@extends('layouts.admin')

@section('title', 'Ajouter un type de document')

@section('content')
<style>
    .form-container {
        background: white;
        border-radius: 16px;
        padding: 30px;
        max-width: 800px;
        margin: 0 auto;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .form-container h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 20px;
    }
    .form-container h3 i {
        color: #ff8c00;
        margin-right: 10px;
    }
    .form-group {
        margin-bottom: 18px;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 13px;
        color: #333;
        margin-bottom: 4px;
    }
    .form-group label .required {
        color: #dc2626;
    }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: white;
        transition: all 0.3s ease;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none;
        border-color: #ff8c00;
        box-shadow: 0 0 0 3px rgba(255,140,0,0.1);
    }
    .form-group textarea {
        min-height: 80px;
        resize: vertical;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #f0eeea;
    }
    .btn-submit {
        background: linear-gradient(135deg, #ff8c00, #ff6b00);
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255,140,0,0.3);
    }
    .btn-back {
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background: #5a6268;
    }
    .error {
        color: #dc2626;
        font-size: 12px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
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
    <h3><i class="fas fa-plus-circle"></i> Ajouter un type de document</h3>

    <form action="{{ route('admin.document-types.store') }}" method="POST">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label>Code <span class="required">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="Ex: PLN" required>
                @error('code')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Nom <span class="required">*</span></label>
                <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Ex: Plans d'exécution" required>
                @error('nom')
                    <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label>Catégorie <span class="required">*</span></label>
            <select name="categorie" required>
                <option value="">Sélectionnez une catégorie</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ old('categorie') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('categorie')
                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Mode de traitement <span class="required">*</span></label>
            <select name="mode_traitement" required>
                <option value="">Sélectionnez un mode</option>
                @foreach($modes as $key => $label)
                    <option value="{{ $key }}" {{ old('mode_traitement') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <div style="font-size:12px; color:#888; margin-top:4px;">
                <i class="fas fa-info-circle"></i>
                <span id="modeDescription">Sélectionnez le mode de traitement pour ce type de document.</span>
            </div>
            @error('mode_traitement')
                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Statut</label>
            <select name="actif">
                <option value="1" {{ old('actif', '1') == '1' ? 'selected' : '' }}>Actif</option>
                <option value="0" {{ old('actif') == '0' ? 'selected' : '' }}>Inactif</option>
            </select>
            @error('actif')
                <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Créer
            </button>
            <a href="{{ route('admin.document-types.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Annuler
            </a>
        </div>
    </form>
</div>

<script>
    // Description des modes
    const modeDescriptions = {
        'simple': 'Informatif : Les documents de ce type ne nécessitent pas de validation préalable. Transmission simple.',
        'validation': 'Validation : Les documents de ce type doivent passer par un processus de validation avec checklist.'
    };

    document.querySelector('select[name="mode_traitement"]').addEventListener('change', function() {
        const desc = document.getElementById('modeDescription');
        desc.textContent = modeDescriptions[this.value] || 'Sélectionnez le mode de traitement pour ce type de document.';
    });
</script>
@endsection
