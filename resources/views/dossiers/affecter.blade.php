@extends('layouts.cheflot')

@section('title', 'Affecter des contrôleurs')

@section('content')
<style>
    .form-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 700px; margin: 0 auto; }
    .form-title { font-size: 24px; font-weight: 700; color: #064e3b; margin-bottom: 10px; }
    .form-subtitle { color: #6b7280; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; }
    input[type="text"] { width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; }
    .controleur-item { display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 8px; }
    .btn-submit { background: linear-gradient(135deg, #047857, #065f46); color: white; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 600; cursor: pointer; }
    input[type="datetime-local"] { width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: inherit; }
    .btn-delai { background: #f0fdf4; color: #047857; border: 1px solid #d1fae5; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; }
    .btn-delai:hover { background: #047857; color: white; }
</style>

<div class="form-container">
    <div class="form-title"><i class="fas fa-user-check"></i> Affecter des contrôleurs</div>
    <div class="form-subtitle">{{ $documentVersion->dossier->identifiant }} — {{ $documentVersion->dossier->titre }} (V{{ $documentVersion->numero_version }})</div>

    <form method="POST" action="{{ route('cheflot.dossiers.affecter.store', $documentVersion) }}">
        @csrf

        <div class="form-group">
            <label>Contrôleurs</label>
            @foreach($controleurs as $controleur)
                <div class="controleur-item">
                    <input type="checkbox" name="controleurs[]" value="{{ $controleur->id }}" id="controleur-{{ $controleur->id }}">
                    <label for="controleur-{{ $controleur->id }}" style="margin:0; font-weight:400;">{{ $controleur->full_name }} @if($controleur->lot) — {{ $controleur->lot->nom }} @endif</label>
                </div>
            @endforeach
            @error('controleurs') <span style="color:#dc2626; font-size:12px;">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label>Spécialité (optionnel)</label>
            <input type="text" name="specialite" placeholder="HSE, géotechnique, topographe, qualité...">
        </div>

        <div class="form-group">
            <label>Délai de traitement (optionnel)</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:8px;">
                <button type="button" class="btn-delai" data-heures="24">+24h</button>
                <button type="button" class="btn-delai" data-heures="48">+48h</button>
                <button type="button" class="btn-delai" data-heures="72">+3 jours</button>
                <button type="button" class="btn-delai" data-heures="120">+5 jours</button>
                <button type="button" class="btn-delai" data-heures="168">+7 jours</button>
            </div>
            <input type="datetime-local" name="date_limite" id="date_limite">
            <p style="font-size:12px; color:#6b7280; margin-top:6px;">Date et heure limite pour que le contrôleur rende son analyse.</p>
            @error('date_limite') <span style="color:#dc2626; font-size:12px;">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Affecter</button>
    </form>
</div>

<script>
    document.querySelectorAll('.btn-delai').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const heures = parseInt(btn.dataset.heures, 10);
            const date = new Date(Date.now() + heures * 3600 * 1000);
            date.setSeconds(0, 0);
            const offset = date.getTimezoneOffset();
            const local = new Date(date.getTime() - offset * 60000);
            document.getElementById('date_limite').value = local.toISOString().slice(0, 16);
        });
    });
</script>
@endsection
