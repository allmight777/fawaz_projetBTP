@extends('layouts.entreprise')

@section('title', 'Documents à transférer')

@section('content')
<style>
.btn-transferer {
        background: linear-gradient(135deg, #2563eb, #1a4fc4);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: var(--fs-m);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
.btn-transferer:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37,99,235,0.3);
    }
.structure-choice {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
    }
.structure-choice label {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border: 2px solid #e0e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
.structure-choice label:hover { border-color: #2563eb; background: #f8faff; }
.structure-choice input[type="radio"] {
        width: 18px; height: 18px;
        accent-color: #2563eb;
    }
.structure-choice input[type="radio"]:checked + span {
        font-weight: 700;
        color: #2563eb;
    }
.structure-choice .icon {
        font-size: 20px;
        color: #2563eb;
        width: 24px;
        text-align: center;
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-share-square"></i> Documents à transférer</h2>
    <a href="{{ route('entreprise.documents') }}" class="btn-outline-blue">
        <i class="fas fa-file-alt"></i> Voir tous les documents
    </a>
</div>

@if(session('success'))
<div style="background:#d1fae5; color:#059669; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size: var(--fs-m);">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fee2e2; color:#dc2626; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size: var(--fs-m);">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div class="table-container">
    <div class="table-wrapper">
        <table data-filtres="type,utilisateur,date">
            <thead>
                <tr>
                    <th>Identifiant</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Envoyé par</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dossiers as $dossier)
                <tr>
                    <td><span style="font-family:monospace; font-size: var(--fs-s); color:#2563eb;">{{ $dossier->identifiant ?? '#'.$dossier->id }}</span></td>
                    <td>{{ Str::limit($dossier->titre, 30) }}</td>
                    <td>{{ $dossier->documentType->nom ?? '-' }}</td>
                    <td>{{ $dossier->creePar->full_name ?? '-' }}</td>
                    <td><span class="badge badge-attente"><i class="fas fa-clock"></i> En attente de transfert</span></td>
                    <td style="font-size: var(--fs-m); color:#888;">{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div style="display:flex; gap:8px;">
                            <a href="{{ route('entreprise.dossiers.show', $dossier->id) }}" class="btn-outline-blue" style="padding:8px 14px;">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn-transferer" onclick='openTransferModal({{ $dossier->id }}, @json(Str::limit($dossier->titre, 40)), @json($dossier->structures_souhaitees), @json($dossier->identifiant_affiche))'>
                                <i class="fas fa-share"></i> Viser et envoyer
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            Aucun document en attente de transfert
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">
        {{ $dossiers->links() }}
    </div>
</div>

<!-- Modal de transfert -->
<div class="modal" id="transferModal">
    <div class="modal-content">
        <div class="tm-head">
            <span class="tm-ico"><i class="fas fa-stamp"></i></span>
            <div class="tm-titles">
                <h3>Envoyer en circuit de visa</h3>
                <p>Choisissez les structures dont le visa est demandé.</p>
            </div>
        </div>
        <div class="tm-doc">
            <i class="fas fa-file-lines"></i>
            <span class="tm-ref" id="transferDocRef">—</span>
            <strong id="transferDocTitle">—</strong>
        </div>

        <form id="transferForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="dossier_ref" id="dossierRef" value="{{ old('dossier_ref') }}">
            @error('structures')<div class="visa-err">{{ $message }}</div>@enderror
            <div class="visa-step"><b>1</b> Circuit de visa : structures dont le visa est demandé</div>
            <div style="margin-bottom:10px;">@include('partials.circuit-choix', ['nom' => 'structures[]', 'id' => 'circuitChoix'])</div>
            <p style="color:var(--muted); font-size: var(--fs-m); margin:0 0 10px;">Le document part en même temps chez toutes les structures cochées ; chacune vise à son niveau.</p>

            <p style="color:var(--muted); font-size: var(--fs-m); margin:-6px 0 4px;">Avant l'envoi, vous visez le document : votre cachet et votre signature y sont enregistrés.</p>
            @include('partials.visa-fields', ['debut' => 2])

            <div class="modal-actions">
                <button type="button" class="btn-modal-close" onclick="closeTransferModal()">Annuler</button>
                <button type="submit" class="btn-modal-submit">
                    <i class="fas fa-paper-plane"></i> Confirmer le transfert
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openTransferModal(dossierId, titre, souhaitees, ref) {
        document.getElementById('transferDocTitle').textContent = titre;
        document.getElementById('transferDocRef').textContent = ref || ('#' + dossierId);
        // Structures souhaitées par le collaborateur : pré-cochées, le chef peut les modifier.
        document.querySelectorAll('#circuitChoix input').forEach(function (c) { c.checked = (souhaitees || []).indexOf(c.value) !== -1; });
        document.getElementById('transferForm').action = `/entreprise/dossiers/${dossierId}/transferer`;
        document.getElementById('dossierRef').value = dossierId;
        document.getElementById('transferModal').classList.add('active');
    }

    function closeTransferModal() {
        document.getElementById('transferModal').classList.remove('active');
        document.getElementById('transferForm').reset();
        document.querySelectorAll('#transferForm [data-visa-clear]').forEach(function (b) { b.click(); });
    }

    @if($errors->hasAny(['cachet', 'signature', 'structures']) && old('dossier_ref'))
        window.addEventListener('DOMContentLoaded', function () { openTransferModal({{ (int) old('dossier_ref') }}, 'Document', [], '#{{ (int) old('dossier_ref') }}'); });
    @endif

    document.getElementById('transferModal').addEventListener('click', function(e) {
        if (e.target === this) closeTransferModal();
    });
</script>
@endsection
