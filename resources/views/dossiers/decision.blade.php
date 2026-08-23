@extends('layouts.cheflot')

@section('title', 'Consolidation et décision')

@section('content')
<style>
    .form-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto; }
    .form-title { font-size: 24px; font-weight: 700; color: #064e3b; margin-bottom: 10px; }
    .form-subtitle { color: #6b7280; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb; }
    .assignment-block { border: 1px solid #e5e7eb; border-radius: 10px; padding: 15px; margin-bottom: 15px; }
    .observation-item { display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; border-top: 1px solid #f3f4f6; }
    .observation-item.non-conformite { color: #b91c1c; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; }
    select, textarea { width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: inherit; }
    .btn-valider { background: linear-gradient(135deg, #047857, #065f46); color: white; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 600; cursor: pointer; margin-right: 10px; }
</style>

<div class="form-container">
    <div class="form-title"><i class="fas fa-gavel"></i> Consolidation et décision</div>
    <div class="form-subtitle">{{ $documentVersion->dossier->identifiant }} — {{ $documentVersion->dossier->titre }} (V{{ $documentVersion->numero_version }})</div>

    <form method="POST" action="{{ route('cheflot.dossiers.decision.store', $documentVersion) }}">
        @csrf

        @foreach($documentVersion->affectations as $affectation)
            <div class="assignment-block">
                <strong>{{ $affectation->controleur->full_name }}</strong>
                @if($affectation->specialite) <span style="color:#6b7280;">({{ $affectation->specialite }})</span> @endif
                — {{ $affectation->statut }}

                @forelse($affectation->observations as $observation)
                    <div class="observation-item {{ $observation->type === 'non_conformite' ? 'non-conformite' : '' }}">
                        <input type="checkbox" name="observations_retenues[]" value="{{ $observation->id }}" {{ $observation->retenue ? 'checked' : '' }}>
                        <div>
                            <span style="text-transform:uppercase; font-size:11px; font-weight:700;">{{ $observation->type === 'non_conformite' ? 'Non-conformité' : 'Observation' }}</span>
                            <p style="margin:0;">{{ $observation->contenu }}</p>
                        </div>
                    </div>
                @empty
                    <p style="color:#9ca3af; font-size:13px; margin-top:8px;">Aucune observation.</p>
                @endforelse
            </div>
        @endforeach

        <div class="form-group">
            <label>Décision</label>
            <select name="decision" required>
                <option value="valide">Validé</option>
                <option value="a_corriger">À corriger</option>
            </select>
        </div>

        <div class="form-group">
            <label>Commentaires</label>
            <textarea name="commentaires" rows="4" placeholder="Synthèse officielle..."></textarea>
        </div>

        <button type="submit" class="btn-valider"><i class="fas fa-check"></i> Enregistrer la décision</button>
    </form>
</div>
@endsection
