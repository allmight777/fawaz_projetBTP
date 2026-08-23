@extends('layouts.cheflot')

@section('title', 'Nouvelle demande')

@section('content')
<style>
    .form-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #064e3b; margin-bottom: 8px; }
    .form-group label .required { color: #dc2626; }
    .form-control {
        width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 10px;
        font-size: 14px; background: #fff; transition: border-color 0.2s;
    }
    .form-control:focus { outline: none; border-color: #047857; box-shadow: 0 0 0 3px rgba(4,120,87,0.1); }
    textarea.form-control { resize: vertical; min-height: 90px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-hint { font-size: 12px; color: #6b7280; margin-top: 6px; }
    .file-drop {
        border: 2px dashed #d1d5db; border-radius: 12px; padding: 24px; text-align: center;
        background: #f9fafb; cursor: pointer; transition: border-color 0.2s;
    }
    .file-drop:hover { border-color: #047857; }
    .file-drop i { font-size: 28px; color: #047857; margin-bottom: 8px; display: block; }
    .btn-submit {
        background: #047857; color: white; border: none; padding: 12px 28px; border-radius: 10px;
        font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s;
    }
    .btn-submit:hover { background: #065f46; }
    .error-msg { color: #dc2626; font-size: 12px; margin-top: 6px; }
    .alert-error { background: #fee2e2; color: #dc2626; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; }
    @media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }
</style>

<div class="form-container">
    <h3 style="color:#064e3b; font-size:20px; font-weight:700; margin-bottom:24px;">
        <i class="fas fa-file-circle-plus"></i> Nouvelle demande
    </h3>

    @if ($errors->any())
        <div class="alert-error">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('demandes.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label>Entreprise <span class="required">*</span></label>
                <input type="text" name="entreprise" class="form-control" value="{{ old('entreprise') }}" required>
            </div>
            <div class="form-group">
                <label>Lot concerné</label>
                <select name="lot_id" class="form-control">
                    <option value="">— Aucun lot spécifique —</option>
                    @foreach($lots as $lot)
                        <option value="{{ $lot->id }}" @selected(old('lot_id') == $lot->id)>
                            {{ $lot->code }} - {{ $lot->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Type de document <span class="required">*</span></label>
                <select name="type_document" class="form-control" required>
                    <option value="">— Choisir —</option>
                    @foreach(['Plan', 'Rapport', 'Contrat', 'Devis', 'Facture', 'Autre'] as $type)
                        <option value="{{ $type }}" @selected(old('type_document') == $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Priorité <span class="required">*</span></label>
                <select name="priorite" class="form-control" required>
                    <option value="">— Choisir —</option>
                    @foreach(['Basse', 'Moyenne', 'Haute', 'Urgente'] as $p)
                        <option value="{{ $p }}" @selected(old('priorite') == $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Titre du document <span class="required">*</span></label>
            <input type="text" name="titre_document" class="form-control" value="{{ old('titre_document') }}" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        </div>

        <div class="form-group">
            <label>Fichier (max 10 Mo)</label>
            <div class="file-drop" onclick="document.getElementById('fichier-input').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                <span id="file-label">Cliquez pour choisir un fichier</span>
                <input type="file" name="fichier" id="fichier-input" class="form-control" style="display:none"
                       onchange="document.getElementById('file-label').innerText = this.files[0]?.name || 'Cliquez pour choisir un fichier'">
            </div>
            <p class="form-hint">Ou renseignez une URL de fichier externe ci-dessous à la place</p>
        </div>

        <div class="form-group">
            <label>URL du fichier (alternative)</label>
            <input type="url" name="fichier_url" class="form-control" value="{{ old('fichier_url') }}" placeholder="https://...">
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-paper-plane"></i> Soumettre la demande
        </button>
    </form>
</div>
@endsection
