@extends(auth()->user()->spaceLayout())

@section('title', 'Analyse du document')

@section('content')
<style>
.version-table {
        width: 100%;
        border-collapse: collapse;
        font-size: var(--fs-m);
    }
.version-table th {
        text-align: left;
        padding: 10px 12px;
        background: var(--accent-s);
        color: var(--accent-d);
        font-weight: 600;
        font-size: var(--fs-s);
        text-transform: uppercase;
    }
.version-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f0eeea;
    }
.btn-download {
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
        font-size: var(--fs-m);
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.3s ease;
    }
.btn-download:hover { color: var(--accent-d); gap: 8px; }
.btn-preview {
        color: #7c3aed;
        text-decoration: none;
        font-weight: 600;
        font-size: var(--fs-m);
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
.decision-label { font-size: var(--fs-m); font-weight: 600; color: #333; }
.error-text { color: #dc2626; font-size: var(--fs-s); margin-top: 6px; }
.badge-soumis { background: #dbeafe; color: #2563eb; }
.badge-en_cours { background: #fef3c7; color: #d97706; }
.badge-a_corriger { background: #fee2e2; color: #dc2626; }
.already-decided {
        padding: 16px;
        background: var(--accent-s);
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        color: var(--accent-d);
        font-size: var(--fs-m);
    }
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
@keyframes slideIn {
        from { transform: translateY(-30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
.btn-delai-quick {
        background: var(--accent-s);
        color: var(--accent);
        border: 1px solid var(--accent-3);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: var(--fs-s);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
.btn-delai-quick:hover { background: var(--accent); color: white; }
@media (max-width: 768px) {
.decision-buttons {
            flex-direction: column;
        }
}
</style>

@php
    $moi = auth()->user();
    $circuitService = app(\App\Services\CircuitVisaService::class);
    $versionCourante = $dossier->versions->sortByDesc('numero_version')->first();
    $peutDecider = $versionCourante && $circuitService->peutDeciderDirectement($moi, $versionCourante);
    $maLigne = $versionCourante ? $circuitService->ligneDe($versionCourante, $moi) : null;
@endphp
<div class="page-header">
    <h2><i class="fas fa-search"></i> Analyse du document</h2>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        @if($peutDecider)
        <button class="btn-primary" onclick="openAssignModal()">
            <i class="fas fa-user-plus"></i> Assigner
        </button>
        @endif
        @php $version = $versionCourante; @endphp
        @include('partials.modifier-decision')
        <a href="{{ sroute('cheflot.documents.recus') }}" class="btn-back">
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
        <span class="value"><span class="badge badge-{{ $dossier->statut }}">{{ $dossier->statut_label }}</span></span>
    </div>
    @if($dossier->description)
    <div class="info-row">
        <span class="label">Description</span>
        <span class="value">{{ $dossier->description }}</span>
    </div>
    @endif
</div>

@include('partials.applicable')
@include('partials.circuit-visas')
@include('partials.circuit-historique')

<!-- Historique des versions -->
<div class="card">
    <h3><i class="fas fa-history"></i> Versions du document</h3>
    <table class="version-table" data-no-filter>
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
                    <a href="{{ sroute('cheflot.documents.recus.apercu', ['dossier' => $dossier->id, 'version' => $version->id]) }}" target="_blank" class="btn-preview">
                        <i class="fas fa-eye"></i> Aperçu
                    </a>
                    <a href="{{ sroute('cheflot.documents.recus.telecharger', ['dossier' => $dossier->id, 'version' => $version->id]) }}" class="btn-download">
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ===================================================================== --}}
{{-- SECTION : Analyses des collaborateurs                                  --}}
{{-- À insérer dans cheflot/document-recu-detail.blade.php,                 --}}
{{-- entre la carte "Versions du document" et la carte "Décision"           --}}
{{-- ===================================================================== --}}

@php
    // On regroupe toutes les affectations par collaborateur (controleur_id)
    // et on ne garde que la plus récente par personne, en privilégiant
    // celles qui ont déjà des observations ou qui sont terminées.
    $toutesAffectations = $dossier->versions
        ->flatMap(fn($v) => $v->affectations)
        ->filter(fn($a) => $a->controleur?->structureVisa() === $moi->structureVisa()) // analyses de sa propre structure
        ->sortByDesc('created_at');

    $affectationsParCollaborateur = $toutesAffectations
        ->groupBy('controleur_id')
        ->map(function ($groupe) {
            // Priorité 1 : une affectation terminée
            $terminee = $groupe->firstWhere('statut', 'termine');
            if ($terminee) {
                return $terminee;
            }

            // Priorité 2 : une affectation avec au moins une observation
            $avecObs = $groupe->first(fn($a) => $a->observations->count() > 0);
            if ($avecObs) {
                return $avecObs;
            }

            // Sinon : la plus récente
            return $groupe->sortByDesc('created_at')->first();
        })
        ->sortByDesc(fn($a) => $a->observations->count() > 0 ? 1 : 0)
        ->values();
@endphp

@if($affectationsParCollaborateur->count() > 0)
<div class="card">
    <h3><i class="fas fa-user-check"></i> Analyses des collaborateurs</h3>

    @foreach($affectationsParCollaborateur as $affectation)
    @php
        // Toutes les observations de ce collaborateur sur ce dossier
        // (peu importe la version / l'affectation d'origine)
        $obsDuCollaborateur = $toutesAffectations
            ->where('controleur_id', $affectation->controleur_id)
            ->flatMap(fn($a) => $a->observations)
            ->sortByDesc('created_at')
            ->values();

        // La dernière version analysée par ce collaborateur (pour la checklist)
        $versionAnalysee = $affectation->documentVersion;

        $checklist = $versionAnalysee->checklistReponses ?? collect();

        $statutLabel = $affectation->statut === 'termine'
            ? 'Terminé'
            : ($affectation->statut === 'en_cours' ? 'En cours' : 'En attente');
        $statutBg = $affectation->statut === 'termine' ? 'var(--accent-3)' : '#fef3c7';
        $statutColor = $affectation->statut === 'termine' ? 'var(--accent-d)' : '#d97706';
        $borderColor = $affectation->statut === 'termine' ? 'var(--accent)' : '#f59e0b';
    @endphp

    <div style="background:#f9fafb; border-radius:12px; padding:16px 20px; margin-bottom:14px; border-left:4px solid {{ $borderColor }};">

        {{-- En-tête affectation --}}
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
            <div>
                <strong style="color:#1a1a1a; font-size: var(--fs-m);">
                    {{ $affectation->controleur->full_name ?? 'Collaborateur inconnu' }}
                </strong>
                @if($affectation->specialite)
                    <span style="color:#888; font-size: var(--fs-s);"> — {{ $affectation->specialite }}</span>
                @endif
            </div>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <span class="badge" style="background:{{ $statutBg }}; color:{{ $statutColor }};">
                    {{ $statutLabel }}
                </span>
                @if($affectation->date_limite)
                    <span style="font-size: var(--fs-s); color:#888;">
                        Échéance : {{ $affectation->date_limite->format('d/m/Y H:i') }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Observations du collaborateur (toutes versions confondues) --}}
        @if($obsDuCollaborateur->count() > 0)
            <div style="margin-top:12px;">
                <p style="font-size: var(--fs-s); font-weight:600; color:var(--accent-d); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.3px;">
                    Observations ({{ $obsDuCollaborateur->count() }})
                </p>
                @foreach($obsDuCollaborateur as $obs)
                <div style="background:white; border-radius:8px; padding:10px 14px; margin-bottom:8px; border-left:3px solid {{ $obs->type === 'non_conformite' ? '#dc2626' : 'var(--accent)' }};">
                    <div style="display:flex; justify-content:space-between; font-size: var(--fs-s); color:#888; margin-bottom:4px;">
                        <span>
                            <strong style="color:{{ $obs->type === 'non_conformite' ? '#dc2626' : 'var(--accent)' }};">
                                {{ $obs->type === 'non_conformite' ? 'Non-conformité' : 'Observation' }}
                            </strong>
                        </span>
                        <span>{{ $obs->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <p style="font-size: var(--fs-m); color:#333; margin:0; line-height:1.5;">
                        {!! nl2br(e($obs->contenu)) !!}
                    </p>
                </div>
                @endforeach
            </div>
        @else
            <p style="font-size: var(--fs-s); color:#888; margin-top:8px; font-style:italic;">
                Aucune observation rédigée pour le moment.
            </p>
        @endif

        {{-- Vérifications checklist --}}
        @if($checklist->count() > 0)
            <div style="margin-top:12px;">
                <p style="font-size: var(--fs-s); font-weight:600; color:var(--accent-d); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.3px;">
                    Vérifications
                </p>
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    @foreach($checklist as $rep)
                        <span style="font-size: var(--fs-s); padding:3px 10px; border-radius:12px; background:{{ $rep->valeur ? 'var(--accent-3)' : '#f3f4f6' }}; color:{{ $rep->valeur ? 'var(--accent-d)' : '#6b7280' }};">
                            {{ $rep->valeur ? '✓' : '✗' }} {{ $rep->checklistItem->libelle ?? '' }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    @endforeach
</div>
@endif

<!-- Décision -->
<div class="card">
    <h3><i class="fas fa-gavel"></i> Décision de {{ \App\Support\Espaces::nom($moi->structureVisa() ?? 'bureau_controle') }}</h3>

    @if(!$peutDecider)
        <div class="already-decided">
            <i class="fas fa-check-circle"></i>
            @if($maLigne && $maLigne->statut === 'valide')
                Votre structure a déjà visé ce document. Il sera validé lorsque toutes les structures sollicitées auront visé.
            @elseif($maLigne && $maLigne->statut === 'a_corriger')
                Votre structure a demandé une correction de ce document.
            @elseif(!$maLigne && $circuitService->aCircuit($versionCourante))
                Le visa de votre structure n'est pas demandé sur ce document.
            @else
                Une décision a déjà été rendue pour ce dossier :
                <strong>{{ $dossier->statut === 'valide' ? 'Validé' : ($dossier->statut === 'a_corriger' ? 'À corriger' : $dossier->statut_label) }}</strong>.
            @endif
        </div>
    @else
        <form action="{{ sroute('cheflot.documents.recus.decision', $dossier->id) }}" method="POST" enctype="multipart/form-data">
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

            @include('partials.visa-validation')

            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Envoyer la décision
            </button>
        </form>
    @endif
</div>

<!-- Modal d'assignation -->
<div id="assignModal" class="modal-overlay asg">
    <div class="modal-content">
        <div class="asg-head">
            <h3>Assigner à un collaborateur</h3>
            <p>{{ $dossier->identifiant_affiche }} · {{ Str::limit($dossier->titre, 60) }}</p>
        </div>

        <form action="{{ sroute('cheflot.documents.recus.assigner', $dossier->id) }}" method="POST">
            @csrf

            <div class="asg-step"><b>1</b> Choisir le collaborateur</div>
            <div id="collaborateursList" class="asg-grid">
                <p class="asg-empty">Chargement des collaborateurs…</p>
            </div>

            <div class="asg-step"><b>2</b> Spécialité demandée <small>facultatif</small></div>
            <input type="text" name="specialite" class="asg-input" placeholder="Ex : Structure, Électricité…">

            <div class="asg-step"><b>3</b> Délai de traitement <small>facultatif</small></div>
            <div class="asg-chips">
                <button type="button" class="btn-delai-quick" data-heures="24">24 heures</button>
                <button type="button" class="btn-delai-quick" data-heures="48">48 heures</button>
                <button type="button" class="btn-delai-quick" data-heures="72">3 jours</button>
                <button type="button" class="btn-delai-quick" data-heures="168">7 jours</button>
            </div>
            <input type="datetime-local" name="date_limite" id="assignDateLimite" class="asg-input">
            <p class="asg-help">Date et heure limite pour rendre l'analyse.</p>

            <div class="asg-actions">
                <button type="button" class="asg-cancel" onclick="closeAssignModal()">Annuler</button>
                <button type="submit" class="asg-ok">Confirmer l'assignation</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAssignModal() {
        const modal = document.getElementById('assignModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        fetch(`{{ sroute('cheflot.documents.recus.collaborateurs', $dossier->id) }}`)
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('collaborateursList');
                if (data.collaborateurs.length === 0) {
                    container.innerHTML = `<p class="asg-empty">Aucun collaborateur disponible.</p>`;
                    return;
                }
                const echapper = t => String(t ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
                container.innerHTML = data.collaborateurs.map(c => {
                    const initiales = echapper(c.full_name.split(' ').map(m => m[0]).slice(0, 2).join('').toUpperCase());
                    return `
                    <label class="asg-card">
                        <input type="radio" name="controleur_id" value="${c.id}" required>
                        <span class="asg-av">${initiales}</span>
                        <span class="asg-who"><strong>${echapper(c.full_name)}</strong><small>${echapper(c.fonction || 'Aucune fonction renseignée')}</small></span>
                    </label>`;
                }).join('');
            })
            .catch(() => {
                document.getElementById('collaborateursList').innerHTML = `
                    <p class="asg-empty">Erreur lors du chargement.</p>
                `;
            });
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.btn-delai-quick').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-delai-quick').forEach(function (b) { b.classList.toggle('on', b === btn); });
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
