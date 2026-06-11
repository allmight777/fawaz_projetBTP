@extends('layouts.cheflot')

@section('title', 'Créer une demande')

@section('content')
<style>
    .form-container {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        max-width: 800px;
        margin: 0 auto;
    }
    .form-title {
        font-size: 24px;
        font-weight: 700;
        color: #064e3b;
        margin-bottom: 10px;
    }
    .form-subtitle {
        color: #6b7280;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e5e7eb;
    }
    .form-group { margin-bottom: 20px; }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
    }
    label i { color: #047857; margin-right: 8px; }
    .required { color: #dc2626; }
    input, select, textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s;
        font-family: inherit;
    }
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #047857;
        box-shadow: 0 0 0 3px rgba(4,120,87,0.1);
    }
    textarea { resize: vertical; min-height: 100px; }
    .file-upload {
        border: 2px dashed #e5e7eb;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #f9fafb;
    }
    .file-upload:hover {
        border-color: #047857;
        background: #f0fdf4;
    }
    .file-upload i {
        font-size: 40px;
        color: #047857;
        margin-bottom: 10px;
    }
    .file-upload p { color: #6b7280; margin: 0; }
    .file-info {
        background: #f0fdf4;
        border: 1px solid #047857;
        border-radius: 10px;
        padding: 10px;
        margin-top: 10px;
        display: none;
    }
    .file-info i { color: #047857; margin-right: 8px; }
    .option-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    .option-btn {
        flex: 1;
        padding: 10px;
        background: #f3f4f6;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s;
    }
    .option-btn.active {
        background: #047857;
        color: white;
        border-color: #047857;
    }
    .option-btn i { margin-right: 8px; }
    .option-panel { display: none; }
    .option-panel.active { display: block; }
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    .btn-submit {
        background: linear-gradient(135deg, #047857, #065f46);
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
    .btn-submit:hover { transform: translateY(-2px); }
    .btn-back {
        background: #6b7280;
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
    .btn-back:hover { background: #4b5563; }
    @media (max-width: 768px) {
        .form-container { padding: 20px; }
        .form-actions { flex-direction: column; }
        .btn-submit, .btn-back { justify-content: center; }
        .option-buttons { flex-direction: column; }
    }
</style>

<div class="form-container">
    <div class="form-title">
        <i class="fas fa-plus-circle"></i> Créer une nouvelle demande
    </div>
    <div class="form-subtitle">
        Remplissez le formulaire ci-dessous pour soumettre une nouvelle demande
    </div>

    <form method="POST" action="{{ route('demandes.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label><i class="fas fa-building"></i> Entreprise <span class="required">*</span></label>
            <input type="text" name="entreprise" value="{{ old('entreprise') }}" placeholder="Nom de l'entreprise" required>
            @error('entreprise') <span style="color:#dc2626; font-size:12px;">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label><i class="fas fa-layer-group"></i> Lot</label>
            <select name="lot_id">
                <option value="">Sélectionner un lot</option>
                @foreach($lots as $lot)
                    <option value="{{ $lot->id }}" {{ old('lot_id') == $lot->id ? 'selected' : '' }}>{{ $lot->code }} - {{ $lot->nom }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-file-alt"></i> Type de document <span class="required">*</span></label>
            <select name="type_document" required>
                <option value="">Sélectionner</option>
                <option value="Plan" {{ old('type_document') == 'Plan' ? 'selected' : '' }}>Plan</option>
                <option value="Rapport" {{ old('type_document') == 'Rapport' ? 'selected' : '' }}>Rapport</option>
                <option value="Contrat" {{ old('type_document') == 'Contrat' ? 'selected' : '' }}>Contrat</option>
                <option value="Devis" {{ old('type_document') == 'Devis' ? 'selected' : '' }}>Devis</option>
                <option value="Facture" {{ old('type_document') == 'Facture' ? 'selected' : '' }}>Facture</option>
                <option value="Autre" {{ old('type_document') == 'Autre' ? 'selected' : '' }}>Autre</option>
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-heading"></i> Titre du document <span class="required">*</span></label>
            <input type="text" name="titre_document" value="{{ old('titre_document') }}" placeholder="Titre du document" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-align-left"></i> Description</label>
            <textarea name="description" placeholder="Description détaillée...">{{ old('description') }}</textarea>
        </div>

        <div class="form-group">
            <label><i class="fas fa-flag"></i> Priorité <span class="required">*</span></label>
            <select name="priorite" required>
                <option value="Basse" {{ old('priorite') == 'Basse' ? 'selected' : '' }}>Basse</option>
                <option value="Moyenne" {{ old('priorite') == 'Moyenne' ? 'selected' : '' }}>Moyenne</option>
                <option value="Haute" {{ old('priorite') == 'Haute' ? 'selected' : '' }}>Haute</option>
                <option value="Urgente" {{ old('priorite') == 'Urgente' ? 'selected' : '' }}>Urgente</option>
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-paperclip"></i> Fichier joint</label>

            <div class="option-buttons">
                <div class="option-btn" data-option="upload">
                    <i class="fas fa-upload"></i> Uploader un fichier
                </div>
                <div class="option-btn" data-option="link">
                    <i class="fas fa-link"></i> Lien externe
                </div>
            </div>

            <div id="upload-panel" class="option-panel">
                <div class="file-upload" onclick="document.getElementById('fichier_input').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Cliquez pour sélectionner un fichier</p>
                    <small>PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 10MB)</small>
                </div>
                <input type="file" name="fichier" id="fichier_input" style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.jpeg">
                <div id="file-info" class="file-info">
                    <i class="fas fa-file"></i> <span id="file-name"></span>
                </div>
            </div>

            <div id="link-panel" class="option-panel">
                <input type="url" name="fichier_url" id="fichier_url" value="{{ old('fichier_url') }}" placeholder="https://drive.google.com/..." style="width:100%">
                <small style="color:#6b7280;">Lien Google Drive, Dropbox, ou tout autre lien direct</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Créer la demande
            </button>
            <a href="{{ route('cheflot.demandes') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Annuler
            </a>
        </div>
    </form>
</div>

<script>
    // Gestion des options (upload/lien)
    const optionBtns = document.querySelectorAll('.option-btn');
    const uploadPanel = document.getElementById('upload-panel');
    const linkPanel = document.getElementById('link-panel');
    const fileInput = document.getElementById('fichier_input');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const urlInput = document.getElementById('fichier_url');

    // Par défaut, upload est actif
    let activeOption = 'upload';
    uploadPanel.classList.add('active');
    linkPanel.classList.remove('active');
    optionBtns.forEach(btn => {
        if (btn.dataset.option === 'upload') {
            btn.classList.add('active');
        }
    });

    optionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const option = this.dataset.option;
            activeOption = option;

            optionBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            if (option === 'upload') {
                uploadPanel.classList.add('active');
                linkPanel.classList.remove('active');
                urlInput.value = '';
                urlInput.disabled = true;
                fileInput.disabled = false;
            } else {
                uploadPanel.classList.remove('active');
                linkPanel.classList.add('active');
                urlInput.disabled = false;
                fileInput.disabled = true;
                fileInfo.style.display = 'none';
                fileInput.value = '';
            }
        });
    });

    // Afficher le nom du fichier sélectionné
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            fileName.textContent = this.files[0].name;
            fileInfo.style.display = 'block';
        } else {
            fileInfo.style.display = 'none';
        }
    });

    // Activer/désactiver les champs
    urlInput.disabled = true;
</script>
@endsection
