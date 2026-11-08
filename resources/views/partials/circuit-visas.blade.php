{{-- Carte « Circuit de visa » : un visa par structure sollicitée, obtenu à son niveau. Attend $dossier (circuitVisas chargé). --}}
@php
    $derniereLigneVersion = $dossier->circuitVisas->max('document_version_id');
    $lignesCircuit = $dossier->circuitVisas->where('document_version_id', $derniereLigneVersion)
        ->sortBy(fn ($l) => array_search($l->structure_type, \App\Support\Espaces::TYPES_VISA, true));
@endphp
@if($lignesCircuit->isNotEmpty())
<div class="card">
    <h3 class="card-title">Circuit de visa</h3>
    <p class="card-hint">Chaque structure vérifie et vise à son niveau, en même temps que les autres. Le document est validé lorsque toutes ont visé.</p>
    <div class="table-wrapper">
        <table data-no-filter>
            <thead><tr><th>Structure</th><th>État</th><th>Décision de</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($lignesCircuit as $ligne)
                    <tr>
                        <td><strong>{{ $ligne->sigle }}</strong> · {{ $ligne->nom_structure }}</td>
                        <td><span class="visa-pill {{ $ligne->statut }}">{{ $ligne->statut_label }}</span></td>
                        <td>{{ $ligne->decideur?->full_name ?? '—' }}</td>
                        <td>{{ $ligne->decide_le?->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                    @if($ligne->statut === 'a_corriger' && $ligne->commentaire)
                        <tr><td colspan="4" class="visa-commentaire">{{ $ligne->commentaire }}</td></tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
