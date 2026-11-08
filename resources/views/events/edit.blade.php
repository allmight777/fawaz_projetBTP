@extends($layout)

@section('title', 'Modifier l\'événement')

@section('content')
<style>
label { display: block; margin-bottom: 6px; font-weight: 600; color: #374151; font-size: var(--fs-m); }
input[type="text"], input[type="datetime-local"], textarea {
        width: 100%; padding: 11px 14px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: var(--fs-m); font-family: inherit;
    }
input:focus, textarea:focus { outline: none; border-color: var(--accent, #2563eb); }
textarea { min-height: 90px; resize: vertical; }
.error-text { color: #dc2626; font-size: var(--fs-s); margin-top: 6px; }
</style>

<div class="form-container">
    <div class="form-title"><i class="fas fa-pen"></i> Modifier l'événement</div>

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
