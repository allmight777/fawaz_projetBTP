@extends('layouts.cheflot')

@section('title', 'Analyse du document')

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
    .btn-back {
        background: #e5e7eb;
        color: #333;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }
    .btn-back:hover { background: #d1d5db; }

    .btn-primary {
        background: #047857;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background: #065f46;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(4,120,87,0.3);
    }

    .card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        margin-bottom: 20px;
    }
    .card h3 {
        font-size: 15px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .card h3 i { color: #047857; }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0eeea;
        font-size: 14px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .label { color: #888; }
    .info-row .value { font-weight: 600; color: #1a1a1a; }

    .version-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .version-table th {
        text-align: left;
        padding: 10px 12px;
        background: #f0fdf4;
        color: #064e3b;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
    }
    .version-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f0eeea;
    }
    .btn-download {
        color: #047857;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.3s ease;
    }
    .btn-download:hover { color: #064e3b; gap: 8px; }
    .btn-preview {
        color: #7c3aed;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.3s ease;
        margin-right: 12px;
    }
    .btn-preview:hover { color: #5b21b6; gap: 8px; }

    .decision-buttons {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }
    .decision-option {
        flex: 1;
        border: 2px solid #e0e8f0;
        border-radius: 12px;
        padding: 16px;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
    }
    .decision-option:hover { border-color: #047857; background: #f0fdf4; }
    .decision-option input[type="radio"] { display: none; }
    .decision-option i { font-size: 24px; display: block; margin-bottom: 8px; }
    .decision-option.valide i { color: #059669; }
    .decision-option.corriger i { color: #dc2626; }
    .decision-option input[type="radio"]:checked + .decision-label {
        font-weight: 700;
    }
    .decision-option:has(input[type="radio"]:checked) {
        border-color: #047857;
        background: #f0fdf4;
        box-shadow: 0 0 0 3px rgba(4,120,87,0.1);
    }
    .decision-label { font-size: 14px; font-weight: 600; color: #333; }

    .form-group { margin-bottom: 16px; }
    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 13px;
        color: #333;
        margin-bottom: 6px;
    }
    .form-group textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e0e8f0;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        min-height: 100px;
        resize: vertical;
    }
    .form-group textarea:focus {
        outline: none;
        border-color: #047857;
        box-shadow: 0 0 0 3px rgba(4,120,87,0.1);
    }
    .error-text { color: #dc2626; font-size: 12px; margin-top: 6px; }

    .btn-submit {
        background: linear-gradient(135deg, #047857, #064e3b);
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(4,120,87,0.3); }

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-soumis { background: #dbeafe; color: #2563eb; }
    .badge-en_cours { background: #fef3c7; color: #d97706; }
    .badge-valide { background: #d1fae5; color: #059669; }
    .badge-a_corriger { background: #fee2e2; color: #dc2626; }

    .already-decided {
        padding: 16px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        color: #064e3b;
        font-size: 14px;
    }

    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 28px;
        max-width: 450px;
        width: 90%;
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from { transform: translateY(-30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .modal-content h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 16px;
        color: #1a1a1a;
    }
    .modal-content h3 i { color: #047857; }
    .modal-content .collaborateur-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border: 1px solid #e0e8f0;
        border-radius: 8px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .modal-content .collaborateur-item:hover {
        border-color: #047857;
        background: #f0fdf4;
    }
    .modal-content .collaborateur-item input[type="radio"] {
        width: 16px;
        height: 16px;
        accent-color: #047857;
        flex-shrink: 0;
    }
    .modal-content .collaborateur-item .info strong {
        display: block;
        font-size: 13px;
        color: #1a1a1a;
    }
    .modal-content .collaborateur-item .info span {
        font-size: 12px;
        color: #888;
    }
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #f0eeea;
    }
    .modal-actions .btn-cancel {
        background: #e5e7eb;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .modal-actions .btn-cancel:hover { background: #d1d5db; }
    .modal-actions .btn-confirm {
        background: linear-gradient(135deg, #047857, #064e3b);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .modal-actions .btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(4,120,87,0.3);
    }
    .btn-delai-quick {
        background: #f0fdf4;
        color: #047857;
        border: 1px solid #d1fae5;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-delai-quick:hover { background: #047857; color: white; }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .decision-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-search"></i> Analyse du document</h2>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        @if(!in_array($dossier->statut, ['valide']))
        <button class="btn-primary" onclick="openAssignModal()">
            <i class="fas fa-user-plus"></i> Assigner
        </button>
        @endif
        <a href="{{ route('cheflot.documents.recus') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<!-- Infos dossier -->
<div class="card">
    <h3><i class="fas fa-folder"></i> Informations du dossier</h3>
    <div class="info-row">
        <span class="label">Identifiant</span>
        <span class="value">{{ $dossier->identifiant ?? '#'.$dossier->id }}</span>
    </div>
    <div class="info-row">
        <span class="label">Titre</span>
        <span class="value">{{ $dossier->titre }}</span>
    </div>
    <div class="info-row">
        <span class="label">Type de document</span>
        <span class="value">{{ $dossier->documentType->nom ?? '-' }}</span>
    </div>
    <div class="info-row">
        <span class="label">Envoyé par</span>
        <span class="value">{{ $dossier->creePar->full_name ?? '-' }} ({{ $dossier->creePar->structure->nom ?? '-' }})</span>
    </div>
    <div class="info-row">
        <span class="label">Statut actuel</span>
        <span class="value"><span class="badge badge-{{ $dossier->statut }}">{{ $dossier->statut }}</span></span>
    </div>
    @if($dossier->description)
    <div class="info-row">
        <span class="label">Description</span>
        <span class="value">{{ $dossier->description }}</span>
    </div>
    @endif
</div>

<!-- Historique des versions -->
<div class="card">
    <h3><i class="fas fa-history"></i> Versions du document</h3>
    <table class="version-table">
        <thead>
            <tr>
                <th>Version</th>
                <th>Fichier</th>
                <th>Importé par</th>
                <th>Date</th>
                <th>Commentaire</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dossier->versions()->orderByDesc('numero_version')->get() as $version)
            <tr>
                <td><strong>V{{ $version->numero_version }}</strong></td>
                <td>{{ $version->nom_affiche }}</td>
                <td>{{ $version->importePar->full_name ?? '-' }}</td>
                <td>{{ $version->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $version->commentaire ?? '-' }}</td>
                <td>
                    <a href="{{ route('cheflot.documents.recus.apercu', ['dossier' => $dossier->id, 'version' => $version->id]) }}" target="_blank" class="btn-preview">
                        <i class="fas fa-eye"></i> Aperçu
                    </a>
                    <a href="{{ route('cheflot.documents.recus.telecharger', ['dossier' => $dossier->id, 'version' => $version->id]) }}" class="btn-download">
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Décision -->
<div class="card">
    <h3><i class="fas fa-gavel"></i> Décision</h3>

    @if(in_array($dossier->statut, ['valide', 'a_corriger']))
        <div class="already-decided">
            <i class="fas fa-check-circle"></i>
            Une décision a déjà été rendue pour ce dossier :
            <strong>{{ $dossier->statut === 'valide' ? 'Validé' : 'À corriger' }}</strong>.
        </div>
    @else
        <form action="{{ route('cheflot.documents.recus.decision', $dossier->id) }}" method="POST">
            @csrf

            <div class="decision-buttons">
                <label class="decision-option valide">
                    <input type="radio" name="decision" value="valide" required>
                    <i class="fas fa-check-circle"></i>
                    <div class="decision-label">Valider le document</div>
                </label>
                <label class="decision-option corriger">
                    <input type="radio" name="decision" value="a_corriger" required>
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="decision-label">Demander une correction</div>
                </label>
            </div>
            @error('decision')
                <div class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
            @enderror

            <div class="form-group">
                <label>Commentaire / Observations</label>
                <textarea name="commentaires" placeholder="Expliquez votre décision, notamment en cas de demande de correction..."></textarea>
                @error('commentaires')
                    <div class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Envoyer la décision
            </button>
        </form>
    @endif
</div>

<!-- Modal d'assignation -->
<div id="assignModal" class="modal-overlay">
    <div class="modal-content">
        <h3><i class="fas fa-user-plus"></i> Assigner à un collaborateur</h3>

        <form action="{{ route('cheflot.documents.recus.assigner', $dossier->id) }}" method="POST">
            @csrf
            <div id="collaborateursList" style="max-height:250px; overflow-y:auto; margin-bottom:16px;">
                <p style="color:#888; font-size:13px; text-align:center; padding:20px 0;">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des collaborateurs...
                </p>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; font-weight:600; font-size:13px; margin-bottom:6px;">Spécialité / note (optionnel)</label>
                <input type="text" name="specialite" placeholder="Ex: Structure, Électricité..." style="width:100%; padding:10px 14px; border:1px solid #e0e8f0; border-radius:8px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; font-weight:600; font-size:13px; margin-bottom:6px;">Délai de traitement (optionnel)</label>
                <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px;">
                    <button type="button" class="btn-delai-quick" data-heures="24">+24h</button>
                    <button type="button" class="btn-delai-quick" data-heures="48">+48h</button>
                    <button type="button" class="btn-delai-quick" data-heures="72">+3j</button>
                    <button type="button" class="btn-delai-quick" data-heures="168">+7j</button>
                </div>
                <input type="datetime-local" name="date_limite" id="assignDateLimite" style="width:100%; padding:10px 14px; border:1px solid #e0e8f0; border-radius:8px; font-family:inherit;">
                <p style="font-size:11px; color:#888; margin-top:6px;">Date et heure limite pour rendre l'analyse.</p>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeAssignModal()">Annuler</button>
                <button type="submit" class="btn-confirm">
                    <i class="fas fa-check"></i> Confirmer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAssignModal() {
        const modal = document.getElementById('assignModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        fetch(`/cheflot/documents-recus/{{ $dossier->id }}/collaborateurs`)
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('collaborateursList');
                if (data.collaborateurs.length === 0) {
                    container.innerHTML = `<p style="color:#888; font-size:13px; text-align:center; padding:20px 0;">Aucun collaborateur disponible.</p>`;
                    return;
                }
                container.innerHTML = data.collaborateurs.map(c => `
                    <label class="collaborateur-item">
                        <input type="radio" name="controleur_id" value="${c.id}" required>
                        <div class="info">
                            <strong>${c.full_name}</strong>
                            <span>${c.specialite || 'Aucune spécialité renseignée'}</span>
                        </div>
                    </label>
                `).join('');
            })
            .catch(() => {
                document.getElementById('collaborateursList').innerHTML = `
                    <p style="color:#dc2626; font-size:13px; text-align:center; padding:20px 0;">
                        <i class="fas fa-exclamation-circle"></i> Erreur lors du chargement.
                    </p>
                `;
            });
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.btn-delai-quick').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const heures = parseInt(btn.dataset.heures, 10);
            const date = new Date(Date.now() + heures * 3600 * 1000);
            date.setSeconds(0, 0);
            const offset = date.getTimezoneOffset();
            const local = new Date(date.getTime() - offset * 60000);
            document.getElementById('assignDateLimite').value = local.toISOString().slice(0, 16);
        });
    });

    // Fermer en cliquant en dehors
    document.getElementById('assignModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAssignModal();
        }
    });
</script>

@endsection
