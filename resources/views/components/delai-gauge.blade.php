@props(['assignment', 'variant' => 'bar'])

@php
    $debut = $assignment->date_affectation ?? $assignment->created_at;
    $limite = $assignment->date_limite;
@endphp

@if($limite)
<div class="delai-gauge delai-gauge-{{ $variant }}"
     data-debut="{{ $debut?->toIso8601String() }}"
     data-limite="{{ $limite->toIso8601String() }}">
    @if($variant === 'circle')
        <div class="delai-gauge-circle-wrap">
            <svg viewBox="0 0 100 100" class="delai-gauge-svg">
                <circle cx="50" cy="50" r="42" class="delai-gauge-track-circle"></circle>
                <circle cx="50" cy="50" r="42" class="delai-gauge-fill-circle"></circle>
            </svg>
            <div class="delai-gauge-circle-center">
                <span class="delai-gauge-percent">--%</span>
                <span class="delai-gauge-remaining">...</span>
            </div>
        </div>
        <div class="delai-gauge-deadline">
            <i class="fas fa-calendar-alt"></i> Échéance : {{ $limite->translatedFormat('d/m/Y à H:i') }}
        </div>
    @else
        <div class="delai-gauge-bar-header">
            <span class="delai-gauge-remaining"><i class="fas fa-hourglass-half"></i> ...</span>
            <span class="delai-gauge-percent">--%</span>
        </div>
        <div class="delai-gauge-track">
            <div class="delai-gauge-fill"></div>
        </div>
    @endif
</div>

@once
<style>
    .delai-gauge-bar { min-width: 160px; }
    .delai-gauge-bar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 4px;
        font-weight: 600;
    }
    .delai-gauge-track {
        width: 100%;
        height: 8px;
        background: #e5e7eb;
        border-radius: 20px;
        overflow: hidden;
    }
    .delai-gauge-fill {
        height: 100%;
        width: 0%;
        background: #10b981;
        border-radius: 20px;
        transition: width 0.6s ease, background 0.4s ease;
    }
    .delai-gauge-bar.delai-critique .delai-gauge-fill {
        background: #dc2626;
        animation: delaiBlink 1s ease-in-out infinite;
    }
    .delai-gauge-bar.delai-critique .delai-gauge-remaining { color: #dc2626; }

    @keyframes delaiBlink {
        0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(220,38,38,0.5); }
        50% { opacity: 0.55; box-shadow: 0 0 8px 1px rgba(220,38,38,0.6); }
    }

    .delai-gauge-circle-wrap {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0 auto;
    }
    .delai-gauge-svg { width: 100%; height: 100%; transform: rotate(-90deg); }
    .delai-gauge-track-circle {
        fill: none;
        stroke: #e5e7eb;
        stroke-width: 8;
    }
    .delai-gauge-fill-circle {
        fill: none;
        stroke: #10b981;
        stroke-width: 8;
        stroke-linecap: round;
        stroke-dasharray: 264;
        stroke-dashoffset: 264;
        transition: stroke-dashoffset 0.6s ease, stroke 0.4s ease;
    }
    .delai-gauge-circle.delai-critique .delai-gauge-fill-circle {
        stroke: #dc2626;
        animation: delaiBlinkCircle 1s ease-in-out infinite;
    }
    @keyframes delaiBlinkCircle {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.45; }
    }
    .delai-gauge-circle-center {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .delai-gauge-circle-center .delai-gauge-percent {
        font-size: 22px;
        font-weight: 800;
        color: #1a1a1a;
    }
    .delai-gauge-circle-center .delai-gauge-remaining {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
        max-width: 100px;
    }
    .delai-gauge-circle.delai-critique .delai-gauge-circle-center .delai-gauge-percent,
    .delai-gauge-circle.delai-critique .delai-gauge-circle-center .delai-gauge-remaining {
        color: #dc2626;
        animation: delaiBlink 1s ease-in-out infinite;
    }
    .delai-gauge-deadline {
        text-align: center;
        font-size: 12px;
        color: #6b7280;
        margin-top: 10px;
    }
</style>
<script>
    (function () {
        function formatRemaining(ms) {
            if (ms <= 0) return 'Délai dépassé';
            const totalMinutes = Math.floor(ms / 60000);
            const jours = Math.floor(totalMinutes / 1440);
            const heures = Math.floor((totalMinutes % 1440) / 60);
            const minutes = totalMinutes % 60;
            if (jours > 0) return `Reste ${jours}j ${heures}h`;
            if (heures > 0) return `Reste ${heures}h ${minutes}min`;
            return `Reste ${minutes}min`;
        }

        function updateGauges() {
            document.querySelectorAll('.delai-gauge').forEach(function (el) {
                const debut = new Date(el.dataset.debut).getTime();
                const limite = new Date(el.dataset.limite).getTime();
                const now = Date.now();
                const total = limite - debut;
                const ecoule = now - debut;
                let pourcentage = total > 0 ? Math.round((ecoule / total) * 100) : 100;
                pourcentage = Math.max(0, Math.min(100, pourcentage));

                const remainingMs = limite - now;
                const remainingText = formatRemaining(remainingMs);
                const heuresRestantes = remainingMs / 3600000;
                const critique = remainingMs <= 0 || pourcentage >= 80 || heuresRestantes <= 24;

                el.classList.toggle('delai-critique', critique);
                el.querySelectorAll('.delai-gauge-percent').forEach(n => n.textContent = pourcentage + '%');
                el.querySelectorAll('.delai-gauge-remaining').forEach(n => {
                    n.innerHTML = n.classList.contains('delai-gauge-remaining') && n.closest('.delai-gauge-bar-header')
                        ? '<i class="fas fa-hourglass-half"></i> ' + remainingText
                        : remainingText;
                });

                const fillBar = el.querySelector('.delai-gauge-fill');
                if (fillBar) fillBar.style.width = pourcentage + '%';

                const fillCircle = el.querySelector('.delai-gauge-fill-circle');
                if (fillCircle) {
                    const circumference = 264;
                    fillCircle.style.strokeDashoffset = circumference - (circumference * pourcentage / 100);
                }
            });
        }

        updateGauges();
        setInterval(updateGauges, 30000);
    })();
</script>
@endonce
@endif
