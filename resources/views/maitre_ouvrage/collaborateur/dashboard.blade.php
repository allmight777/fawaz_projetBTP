@extends('layouts.maitre-ouvrage')

@section('title', 'Documents de mon lot')

@section('content')
<h1 style="font-size:22px; font-weight:700; color:#1a1a1a; margin-bottom:20px;">
    <i class="fas fa-folder-open" style="color:#E91E8C;"></i> Documents de mon lot
</h1>

@include('maitre_ouvrage._stats-charts')

@include('maitre_ouvrage._dossiers-table')
@endsection
