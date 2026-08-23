@extends('layouts.entreprise')

@section('title', 'Documents à transférer')

@section('content')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .page-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }
    .page-header h2 i { color: #2563eb; margin-right: 10px; }
    .btn-outline-blue {
        background: transparent;
        color: #2563eb;
        border: 2px solid #2563eb;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }
    .btn-outline-blue:hover { background: #2563eb; color: white; }

    .table-container {
        background: white;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .table-wrapper { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th {
        text-align: left;
        padding: 12px 14px;
        background: #eff4ff;
        color: #1a1a1a;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 2px solid #dbeafe;
    }
    td { padding: 12px 14px; border-bottom: 1px solid #f0eeea; color: #333; }
    tr:hover td { background: #f8faff; }

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }
    .badge-attente { background: #fef3c7; color: #d97706; }

    .btn-transferer {
        background: linear-gradient(135deg, #2563eb, #1a4fc4);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-transferer:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37,99,235,0.3);
    }

    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: #888;
    }
    .empty-state i {
        font-size: 48px;
        display: block;
        margin-bottom: 16px;
        opacity: 0.3;
        color: #2563eb;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    .modal.active { display: flex; }
    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 28px;
        max-width: 480px;
        width: 90%;
    }
    .modal-content h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .modal-content p {
        font-size: 13px;
        color: #888;
        margin-bottom: 20px;
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
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    .btn-modal-close {
        background: #e5e7eb;
        color: #333;
        border: none;
        padding: 10px 22px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
    }
    .btn-modal-submit {
        background: linear-gradient(135deg, #2563eb, #1a4fc4);
        color: white;
        border: none;
        padding: 10px 22px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-share-square"></i> Documents à transférer</h2>
    <a href="{{ route('entreprise.documents') }}" class="btn-outline-blue">
        <i class="fas fa-file-alt"></i> Voir tous les documents
    </a>
</div>

@if(session('success'))
<div style="background:#d1fae5; color:#059669; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fee2e2; color:#dc2626; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div class="table-container">
    <div class="table-wrapper">
        <table>
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
                    <td><span style="font-family:monospace; font-size:12px; color:#2563eb;">{{ $dossier->identifiant ?? '#'.$dossier->id }}</span></td>
                    <td>{{ Str::limit($dossier->titre, 30) }}</td>
                    <td>{{ $dossier->documentType->nom ?? '-' }}</td>
                    <td>{{ $dossier->creePar->full_name ?? '-' }}</td>
                    <td><span class="badge badge-attente"><i class="fas fa-clock"></i> En attente de transfert</span></td>
                    <td style="font-size:13px; color:#888;">{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div style="display:flex; gap:8px;">
                            <a href="{{ route('entreprise.dossiers.show', $dossier->id) }}" class="btn-outline-blue" style="padding:8px 14px;">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn-transferer" onclick="openTransferModal({{ $dossier->id }}, '{{ Str::limit($dossier->titre, 40) }}')">
                                <i class="fas fa-share"></i> Transférer
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
        <h3><i class="fas fa-share-square" style="color:#2563eb;"></i> Transférer le document</h3>
        <p id="transferDocTitle">Choisissez la structure destinataire.</p>

        <form id="transferForm" method="POST" action="">
            @csrf
            <div class="structure-choice">
                <label>
                    <input type="radio" name="structure_cible" value="bureau_controle" required>
                    <span class="icon"><i class="fas fa-clipboard-check"></i></span>
                    <span>Bureau de Contrôle</span>
                </label>
                <label>
                    <input type="radio" name="structure_cible" value="bureau_etudes" required>
                    <span class="icon"><i class="fas fa-drafting-compass"></i></span>
                    <span>Bureau d'Études</span>
                </label>
                <label>
                    <input type="radio" name="structure_cible" value="maitre_ouvrage" required>
                    <span class="icon"><i class="fas fa-building-shield"></i></span>
                    <span>Maître d'Ouvrage</span>
                </label>
            </div>
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
    function openTransferModal(dossierId, titre) {
        document.getElementById('transferDocTitle').textContent = `Document : ${titre}`;
        document.getElementById('transferForm').action = `/entreprise/dossiers/${dossierId}/transferer`;
        document.getElementById('transferModal').classList.add('active');
    }

    function closeTransferModal() {
        document.getElementById('transferModal').classList.remove('active');
        document.getElementById('transferForm').reset();
    }

    document.getElementById('transferModal').addEventListener('click', function(e) {
        if (e.target === this) closeTransferModal();
    });
</script>
@endsection
