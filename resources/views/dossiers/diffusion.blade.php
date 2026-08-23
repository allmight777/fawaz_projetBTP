@extends('layouts.cheflot')

@section('title', 'Diffusion post-validation')

@section('content')
<style>
    .form-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 700px; margin: 0 auto; }
    .form-title { font-size: 24px; font-weight: 700; color: #064e3b; margin-bottom: 10px; }
    .form-subtitle { color: #6b7280; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; }
    .checkbox-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; }
    textarea { width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: inherit; }
    .btn-submit { background: linear-gradient(135deg, #047857, #065f46); color: white; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 600; cursor: pointer; }
</style>

<div class="form-container">
    <div class="form-title"><i class="fas fa-share-nodes"></i> Diffusion post-validation</div>
    <div class="form-subtitle">{{ $dossier->identifiant }} — {{ $dossier->titre }} — document validé, aucune diffusion automatique</div>

    <form method="POST" action="{{ route('cheflot.dossiers.diffusion.store', $dossier) }}">
        @csrf

        <div class="form-group">
            <label>Structures destinataires</label>
            @foreach($structures as $structure)
                <div class="checkbox-item">
                    <input type="checkbox" name="structures[]" value="{{ $structure->id }}" id="structure-{{ $structure->id }}">
                    <label for="structure-{{ $structure->id }}" style="margin:0; font-weight:400;">{{ $structure->nom }} ({{ $structure->libelle_type }})</label>
                </div>
            @endforeach
        </div>

        <div class="form-group">
            <label>Utilisateurs destinataires</label>
            <div style="max-height:180px; overflow-y:auto;">
                @foreach($users as $destinataire)
                    <div class="checkbox-item">
                        <input type="checkbox" name="users[]" value="{{ $destinataire->id }}" id="user-{{ $destinataire->id }}">
                        <label for="user-{{ $destinataire->id }}" style="margin:0; font-weight:400;">{{ $destinataire->full_name }}</label>
                    </div>
                @endforeach
            </div>
        </div>
        @error('destinataires') <span style="color:#dc2626; font-size:12px;">{{ $message }}</span> @enderror

        <div class="form-group">
            <label>Commentaire</label>
            <textarea name="commentaire" rows="3"></textarea>
        </div>

        <div class="checkbox-item">
            <input type="checkbox" name="archiver" value="1" id="archiver">
            <label for="archiver" style="margin:0; font-weight:400;">Archiver le dossier après diffusion</label>
        </div>

        <button type="submit" class="btn-submit" style="margin-top:15px;"><i class="fas fa-paper-plane"></i> Diffuser</button>
    </form>
</div>
@endsection
