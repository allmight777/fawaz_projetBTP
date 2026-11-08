@extends(auth()->user()->spaceLayout())

@section('title', 'Canevas')

@section('content')
<div class="page-header">
    <h1>Canevas</h1>
</div>

<div class="cat-filter" id="catFilter">
    <button type="button" class="cat-chip on" data-cat="">Toutes <b>{{ $types->count() }}</b></button>
    @foreach($groupes as $cle => $liste)
        <button type="button" class="cat-chip" data-cat="{{ $cle }}">{{ $categories[$cle] ?? ucfirst(str_replace('_', ' ', $cle)) }} <b>{{ $liste->count() }}</b></button>
    @endforeach
</div>

@forelse($groupes as $cle => $liste)
    <section class="cat-group" data-cat="{{ $cle }}">
        <div class="card">
            <div class="table-header">
                <h3>{{ $categories[$cle] ?? ucfirst(str_replace('_', ' ', $cle)) }}</h3>
                <span class="cat-count">{{ $liste->count() }} type{{ $liste->count() > 1 ? 's' : '' }}</span>
            </div>

            <div class="cnv-list">
                @foreach($liste as $type)
                    <details class="cnv">
                        <summary>
                            <span class="cnv-code">{{ $type->code }}</span>
                            <span class="cnv-nom">{{ $type->nom }}</span>
                            <span class="badge {{ $type->mode_traitement === 'validation' ? 'badge-validation' : 'badge-simple' }}">{{ $type->mode_label }}</span>
                            <span class="badge badge-controle">{{ $type->checklistItems->count() }} critère{{ $type->checklistItems->count() > 1 ? 's' : '' }}</span>
                        </summary>
                        <div class="cnv-body">
                            @if($type->checklistItems->isEmpty())
                                <p class="cnv-vide">Pas de liste de contrôle pour ce type de document.</p>
                            @else
                                <ol class="cnv-items">
                                    @foreach($type->checklistItems as $item)
                                        <li>
                                            <span>{{ $item->libelle }}</span>
                                            @if($item->obligatoire)<em class="an-req">Obligatoire</em>@else<em class="cnv-opt">Facultatif</em>@endif
                                        </li>
                                    @endforeach
                                </ol>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@empty
    <div class="empty-state">Aucun type de document pour le moment.</div>
@endforelse

<script>
    (function () {
        var chips = document.querySelectorAll('#catFilter .cat-chip');
        var groupes = document.querySelectorAll('.cat-group');
        function filtrer(cat) {
            chips.forEach(function (c) { c.classList.toggle('on', c.dataset.cat === cat); });
            groupes.forEach(function (g) { g.hidden = cat !== '' && g.dataset.cat !== cat; });
        }
        chips.forEach(function (c) { c.addEventListener('click', function () { filtrer(c.dataset.cat); }); });
    })();
</script>
@endsection
