@extends('layouts.maitre-ouvrage')

@section('title', 'Documents archivés')

@section('content')
<h1 style="font-size:22px; font-weight:700; color:#1a1a1a; margin-bottom:6px;">
    <i class="fas fa-box-archive" style="color:var(--accent);"></i> Documents archivés
</h1>
<p style="font-size:13px; color:#6b7280; margin-bottom:20px;">Consultation seule : un document archivé ne peut plus être modifié ni supprimé.</p>

@include('maitre_ouvrage._dossiers-table')
@endsection
