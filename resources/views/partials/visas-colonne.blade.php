{{-- Colonne « Visas » : état du visa de chaque structure sollicitée (BC, BE, MOE). Attend $dossier, ou $version pour une ligne de version. --}}
@php
    $visasResume = isset($version) ? $version->visas_resume : $dossier->visas_resume;
    $glyphes = ['valide' => '✓', 'a_corriger' => '✕', 'en_analyse' => '…', 'en_attente' => '·'];
    $libelles = ['valide' => 'a visé', 'a_corriger' => 'demande une correction', 'en_analyse' => 'en analyse', 'en_attente' => 'en attente'];
@endphp
@if($visasResume)
    <span class="visas">
        @foreach($visasResume as $visa)
            <span class="visa-pill {{ $visa['statut'] }}" title="{{ $visa['nom'] }} : {{ $libelles[$visa['statut']] ?? $visa['statut'] }}">{{ $visa['sigle'] }} {{ $glyphes[$visa['statut']] ?? '·' }}</span>
        @endforeach
    </span>
@else
    <span class="visas-vide">—</span>
@endif
