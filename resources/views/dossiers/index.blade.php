@extends(auth()->user()->spaceLayout())

@section('title', 'Dossiers')

@section('content')
<div class="page-header">
    <h1>Dossiers</h1>
    <a href="{{ route('dossiers.create') }}" class="btn-submit" style="width:auto; display:inline-block;">Importer un document</a>
</div>

<form method="GET" class="filters">
    <select name="statut">
        <option value="">Tous les statuts</option>
        @foreach(['transmis' => 'Transmis', 'soumis' => 'Soumis', 'en_analyse' => 'En analyse', 'en_cours' => 'En analyse (contrôle)', 'valide' => 'Validé', 'a_corriger' => 'À corriger', 'archive' => 'Archivé'] as $valeur => $libelle)
            <option value="{{ $valeur }}" {{ request('statut') === $valeur ? 'selected' : '' }}>{{ $libelle }}</option>
        @endforeach
    </select>
    <select name="structure_emettrice_id">
        <option value="">Toutes les structures</option>
        @foreach($structures as $structure)
            <option value="{{ $structure->id }}" {{ request('structure_emettrice_id') == $structure->id ? 'selected' : '' }}>{{ $structure->nom }}</option>
        @endforeach
    </select>
    <select name="document_type_id">
        <option value="">Tous les types</option>
        @foreach($documentTypes as $type)
            <option value="{{ $type->id }}" {{ request('document_type_id') == $type->id ? 'selected' : '' }}>[{{ $type->code }}] {{ $type->nom }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn">Filtrer</button>
    <a href="{{ route('dossiers.index') }}" class="btn-back">Réinitialiser</a>
</form>

@include('partials.tableau-dossiers', ['dossiers' => $dossiers, 'filtresTable' => ''])
@endsection
