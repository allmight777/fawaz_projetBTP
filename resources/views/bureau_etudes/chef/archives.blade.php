@extends('layouts.bureau_etudes_chef')

@section('title', 'Documents archivés')

@section('content')
<h1>
    <i class="fas fa-box-archive" style="color:var(--accent);"></i> Documents archivés
</h1>
<div style="height:18px"></div>

@include('maitre_ouvrage._dossiers-table')
@endsection
