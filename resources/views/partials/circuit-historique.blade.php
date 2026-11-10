{{-- Historique du circuit, regroupé par structure : tout ce que chacune a fait ou demandé. Attend $dossier. --}}
@php
    $evenements = $dossier->evenements()->with('user', 'version')->get();
    $mienne = auth()->user()->structureVisa();
    $ordre = \App\Support\Espaces::TYPES_VISA;
    if ($mienne) { $ordre = array_merge([$mienne], array_diff($ordre, [$mienne])); }
    $parStructure = $evenements->whereNotNull('structure_type')->groupBy('structure_type');
    $generaux = $evenements->whereNull('structure_type');
    $icones = ['envoi' => 'paper-plane', 'visa' => 'circle-check', 'correction_demandee' => 'circle-exclamation', 'decision_modifiee' => 'pen-to-square', 'nouvelle_version' => 'file-circle-plus', 'structure_ajoutee' => 'share-nodes', 'rendu_applicable' => 'stamp', 'applicable_retire' => 'ban'];
@endphp
@if($evenements->isNotEmpty())
<div class="card">
    <h3 class="card-title">Historique du circuit</h3>
    <p class="card-hint">Chaque structure retrouve ici ce qu'elle a fait ou demandé sur le document, avec la date et l'heure.</p>
    <div class="hist-grid">
        @foreach($ordre as $type)
            @continue(!isset($parStructure[$type]))
            <section class="hist-col {{ $type === $mienne ? 'mine' : '' }}">
                <header><span class="hist-sigle">{{ \App\Support\Espaces::sigle($type) }}</span> {{ \App\Support\Espaces::nom($type) }}@if($type === $mienne) <em>votre structure</em>@endif</header>
                <ol class="hist-tl">
                    @foreach($parStructure[$type] as $e)
                        <li class="{{ $e->action }}">
                            <span class="hist-dot"><i class="fas fa-{{ $icones[$e->action] ?? 'circle' }}"></i></span>
                            <div>
                                <strong>{{ $e->libelle }}</strong>@if($e->version) <small>V{{ $e->version->numero_version }}</small>@endif
                                <div class="hist-meta">{{ $e->user?->full_name ?? '—' }} · {{ $e->created_at->format('d/m/Y à H:i') }}</div>
                                @if($e->motif)<blockquote>{{ $e->motif }}</blockquote>@endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endforeach

        @if($generaux->isNotEmpty())
            <section class="hist-col">
                <header><span class="hist-sigle">DOC</span> Émetteur et applicabilité</header>
                <ol class="hist-tl">
                    @foreach($generaux as $e)
                        <li class="{{ $e->action }}">
                            <span class="hist-dot"><i class="fas fa-{{ $icones[$e->action] ?? 'circle' }}"></i></span>
                            <div>
                                <strong>{{ $e->libelle }}</strong>@if($e->version) <small>V{{ $e->version->numero_version }}</small>@endif
                                <div class="hist-meta">{{ $e->user?->full_name ?? '—' }} · {{ $e->created_at->format('d/m/Y à H:i') }}</div>
                                @if($e->motif)<blockquote>{{ $e->motif }}</blockquote>@endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif
    </div>
</div>
@endif
