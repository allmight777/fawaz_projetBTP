@extends($layout)

@section('title', 'Modifier l\'événement')

@section('content')
<style>
    .form-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 720px; margin: 0 auto; }
    .form-title { font-size: 22px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; }
    .form-title i { color: var(--accent, #2563eb); margin-right: 8px; }
    .form-subtitle { color: #6b7280; margin-bottom: 26px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
    .form-group { margin-bottom: 18px; }
    .form-row { display: flex; gap: 16px; }
    .form-row .form-group { flex: 1; }
    label { display: block; margin-bottom: 6px; font-weight: 600; color: #374151; font-size: 13px; }
    input[type="text"], input[type="datetime-local"], textarea {
        width: 100%; padding: 11px 14px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: inherit;
    }
    input:focus, textarea:focus { outline: none; border-color: var(--accent, #2563eb); }
    textarea { min-height: 90px; resize: vertical; }
    .error-text { color: #dc2626; font-size: 12px; margin-top: 6px; }
    .btn-submit {
        background: linear-gradient(135deg, var(--accent, #2563eb), var(--accent-dark, #1a4fc4));
        color: white; border: none; padding: 12px 26px; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 14px;
    }
</style>

<div class="form-container">
    <div class="form-title"><i class="fas fa-pen"></i> Modifier l'événement</div>
    <div class="form-subtitle">La liste des invités ne peut pas être modifiée ici — supprimez et recréez l'événement si besoin.</div>

    <form method="POST" action="{{ route('events.update', $event) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Titre <span style="color:#dc2626;">*</span></label>
            <input type="text" name="titre" value="{{ old('titre', $event->titre) }}" required>
            @error('titre') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description">{{ old('description', $event->description) }}</textarea>
            @error('description') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Date et heure de début <span style="color:#dc2626;">*</span></label>
                <input type="datetime-local" name="date_debut" value="{{ old('date_debut', $event->date_debut->format('Y-m-d\TH:i')) }}" required>
                @error('date_debut') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Date et heure de fin</label>
                <input type="datetime-local" name="date_fin" value="{{ old('date_fin', $event->date_fin?->format('Y-m-d\TH:i')) }}">
                @error('date_fin') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label>Lieu</label>
            <input type="text" name="lieu" value="{{ old('lieu', $event->lieu) }}">
            @error('lieu') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Enregistrer</button>
    </form>
</div>
@endsection
