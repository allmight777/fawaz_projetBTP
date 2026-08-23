@extends('layouts.maitre-ouvrage')

@section('title', 'Documents archivés')

@section('content')
<h1 style="font-size:22px; font-weight:700; color:#1a1a1a; margin-bottom:20px;">
    <i class="fas fa-box-archive" style="color:#E91E8C;"></i> Documents archivés
</h1>

@include('maitre_ouvrage._dossiers-table')
@endsection
