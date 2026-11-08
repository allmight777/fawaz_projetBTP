@extends('layouts.entreprise')

@section('title', 'Gestion des documents')

@section('content')
<style>
.badge-informatif { background: #dbeafe; color: #2563eb; }
.badge-en-attente { background: #fef3c7; color: #d97706; }
.badge-en-cours { background: #dbeafe; color: #2563eb; }
.badge-termine { background: #d1fae5; color: #059669; }
.badge-brouillon { background: #e5e7eb; color: #6b7280; }
@keyframes slideIn {
        from { transform: translateY(-30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
.destinataire-section {
        background: #f8faff;
        border-radius: 8px;
        padding: 16px;
        margin-top: 8px;
        border: 1px solid #e0e8f0;
    }
.destinataire-section .checkbox-group {
        margin-top: 8px;
    }
.destinataire-disabled {
        opacity: 0.6;
        pointer-events: none;
    }
.file-info {
        font-size: var(--fs-s);
        color: #888;
        margin-top: 4px;
    }
.file-info i {
        margin-right: 4px;
    }
.search-wrapper {
        position: relative;
    }
.search-wrapper input {
        padding-left: 32px;
    }
.search-wrapper i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
        font-size: var(--fs-m);
    }
.document-block {
        border: 1px solid #e0e8f0;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
        position: relative;
        background: #fcfdff;
    }
.document-block .remove-doc-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 6px;
        width: 26px;
        height: 26px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
.document-block .remove-doc-btn:hover {
        background: #dc2626;
        color: white;
    }
.destinataires-checkboxes-hidden {
        display: none !important;
    }
.size-display {
        font-size: var(--fs-m);
        color: #888;
        margin-bottom: 12px;
        padding: 8px 12px;
        background: #f8faff;
        border-radius: 6px;
        border: 1px solid #e0e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
.size-display .total-size {
        font-weight: 600;
        color: #2563eb;
    }
.size-display .total-size.over-limit {
        color: #dc2626;
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-file-alt"></i> Gestion des documents</h2>
    <div>
        <button class="btn-primary" onclick="openModal('transmissionModal')">
            <i class="fas fa-paper-plane"></i> Transmettre un document
        </button>
        <a href="{{ route('entreprise.dossiers') }}" class="btn-outline-blue">
            <i class="fas fa-folder-open"></i> Voir les dossiers
        </a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<!-- Filtres -->
<div class="filters">
    <select>
        <option value="">Tous les types</option>
        @foreach($documentTypes as $type)
            <option value="{{ $type->id }}">{{ $type->nom }}</option>
        @endforeach
    </select>
    <select>
        <option value="">Tous les statuts</option>
        <option value="en_attente_checklist">En attente</option>
        <option value="soumis">Soumis</option>
        <option value="en_analyse">En analyse</option>
        <option value="valide">Validé</option>
        <option value="a_corriger">À corriger</option>
    </select>
    <input type="text" placeholder="Rechercher...">
    <button class="btn-outline-blue" style="border:none; background:#eff4ff; padding:8px 16px;">
        <i class="fas fa-search"></i> Filtrer
    </button>
</div>

<!-- Liste des documents -->
<div class="table-container">
    <div class="table-wrapper">
        <table data-filtres="statut,type,transmission,date">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Version</th>
                    <th>Statut</th>
                    <th>Visas</th>
                    <th>Transmission</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                <tr>
                    <td>
                        <a href="{{ route('entreprise.dossiers.show', $doc->dossier_id) }}" class="btn-link" style="font-weight:600;">
                            #{{ $doc->dossier_id }}
                        </a>
                    </td>
                    <td>{{ Str::limit($doc->dossier->titre ?? 'Sans titre', 35) }}</td>
                    <td>{{ $doc->dossier->documentType->nom ?? '-' }}</td>
                    <td>V{{ $doc->numero_version }}</td>
                    <td>
                        <span class="badge
                            @if($doc->statut == 'valide') badge-valide
                            @elseif($doc->statut == 'en_attente_checklist') badge-en-attente
                            @elseif($doc->statut == 'en_analyse') badge-en-cours
                            @elseif($doc->statut == 'a_corriger') badge-rejete
                            @else badge-brouillon @endif">
                            {{ $doc->statut_label }}
                        </span>
                    </td>
                    <td>@php $dossier = $doc->dossier; $version = $doc; @endphp @include('partials.visas-colonne')@php unset($version); @endphp</td>
                    <td>
                        @if($doc->dossier->transmissions->count() > 0)
                            <span class="badge badge-informatif">
                                <i class="fas fa-check-circle"></i> Transmis
                            </span>
                        @else
                            <span class="badge badge-brouillon">
                                <i class="fas fa-clock"></i> Non transmis
                            </span>
                        @endif
                    </td>
                    <td style="font-size: var(--fs-m); color:#888;">
                        {{ $doc->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <a href="{{ route('entreprise.dossiers.telecharger', $doc->dossier_id) }}" class="btn-link" title="Télécharger">
                                <i class="fas fa-download"></i>
                            </a>

                            @if($doc->dossier->isArchived())
                                <span class="badge badge-brouillon" title="Document archivé : lecture seule">
                                    <i class="fas fa-box-archive"></i> Archivé
                                </span>
                            @elseif($doc->dossier->aEteVise())
                                <span class="badge badge-brouillon" title="Ce document a été validé ou visé : il ne peut plus être supprimé">
                                    <i class="fas fa-lock"></i> Visé
                                </span>
                            @else
                            <form action="{{ route('entreprise.dossiers.destroy', $doc->dossier_id) }}" method="POST"
                                  onsubmit="return confirm('Supprimer définitivement ce document ?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-link" style="border:none; background:none; cursor:pointer; color:#dc2626;" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                          
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            Aucun document trouvé
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">
        {{ $documents->links() }}
    </div>
</div>

<!-- Modal de transmission -->
<div class="modal" id="transmissionModal">
    <div class="modal-content">
        <form id="transmissionForm" action="{{ route('entreprise.transmettre') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <div id="documentsContainer"></div>

            <!-- Indicateur de taille totale -->
            <div class="size-display">
                <span><i class="fas fa-weight-hanging"></i> Taille totale des fichiers</span>
                <span class="total-size" id="totalSizeDisplay">0.00 Mo / 7.00 Mo</span>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <button type="button" class="btn-outline-blue" id="addDocumentBtn" onclick="addDocumentBlock()">
                    <i class="fas fa-plus"></i> Ajouter un document
                </button>
                <span style="font-size: var(--fs-m); color:#888;">
                    <span id="documentsRemaining">4</span> document(s) restant(s)
                </span>
            </div>

            @if(auth()->user()->isResponsableOrganisme())
                <div class="visa-step" style="margin-top:6px;"><b>✓</b> Visa du responsable</div>
                <p style="color:var(--muted); font-size: var(--fs-m);">En tant que responsable, vos envois partent directement chez les destinataires, visés de votre cachet et de votre signature.</p>
                @include('partials.visa-fields')
            @endif

            <div class="modal-actions">
                <button type="button" class="btn-modal-close" onclick="closeModal('transmissionModal')">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="submit" class="btn-modal-submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Transmettre
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const MAX_DOCUMENTS = 5;
    const MAX_TOTAL_SIZE = 7 * 1024 * 1024; // 7 Mo en octets

    @php
        $usersData = $users->map(function($u) {
            return [
                'id' => $u->id,
                'full_name' => $u->full_name,
                'email' => $u->email,
                'role' => $u->role,
                'categorie_role' => $u->categorie_role,
            ];
        })->values();
    @endphp

    const ALL_USERS = @json($usersData);

    // Types actifs et leurs catégories : la catégorie choisie filtre la liste des types.
    @php
        $typesDoc = $documentTypes->map(function ($t) {
            return ['id' => $t->id, 'nom' => $t->nom, 'mode' => $t->mode_traitement, 'mode_label' => $t->mode_label, 'categorie' => $t->categorie];
        })->values();
        $categoriesDoc = collect(\App\Models\DocumentType::categories())
            ->map(function ($nom, $cle) { return ['cle' => $cle, 'nom' => $nom]; })
            ->filter(function ($c) use ($documentTypes) { return $documentTypes->contains('categorie', $c['cle']); })
            ->values();
    @endphp
    const TYPES_DOC = @json($typesDoc);
    const CATEGORIES_DOC = @json($categoriesDoc);

    const CATEGORIES_HTML = '<option value="">Sélectionnez une catégorie</option>' +
        CATEGORIES_DOC.map(c => `<option value="${c.cle}">${c.nom}</option>`).join('');

    function optionsTypes(cle) {
        const liste = TYPES_DOC.filter(t => t.categorie === cle);
        return '<option value="">Sélectionnez un type de document</option>' +
            liste.map(t => `<option value="${t.id}" data-mode="${t.mode}">${t.nom} (${t.mode_label})</option>`).join('');
    }

    // Choisir une catégorie ne propose que les types de cette catégorie.
    function filtrerTypes(categorieSelect) {
        const block = categorieSelect.closest('.document-block');
        const typeSelect = block.querySelector('.document-type-select');
        const cle = categorieSelect.value;
        typeSelect.innerHTML = cle ? optionsTypes(cle) : '<option value="">Choisissez d\'abord une catégorie</option>';
        typeSelect.disabled = !cle;
        typeSelect.value = '';
        loadChecklist(typeSelect);
    }
    let documentIndex = 0;
    let checklistState = {};

    const submitBtn = document.getElementById('submitBtn');
    const documentsContainer = document.getElementById('documentsContainer');

    // ============================================================
    // MISE À JOUR DE L'AFFICHAGE DE LA TAILLE TOTALE
    // ============================================================
    function updateTotalSizeDisplay() {
        let totalSize = 0;
        document.querySelectorAll('input[type="file"]').forEach(input => {
            if (input.files[0]) totalSize += input.files[0].size;
        });
        const display = document.getElementById('totalSizeDisplay');
        const mo = (totalSize / 1024 / 1024).toFixed(2);
        display.textContent = `${mo} Mo / 7.00 Mo`;
        display.classList.toggle('over-limit', totalSize > MAX_TOTAL_SIZE);

        validateForm();
    }

    // Écoute les changements sur tous les inputs file, y compris ceux ajoutés dynamiquement
    documentsContainer.addEventListener('change', function(e) {
        if (e.target.type === 'file') {
            updateTotalSizeDisplay();
        }
    });

    // ============================================================
    // RENUMEROTATION DES BLOCS
    // ============================================================
    function renumberDocumentBlocks() {
        const blocks = document.querySelectorAll('.document-block');
        const newChecklistState = {};

        blocks.forEach((block, newIndex) => {
            const oldIndex = block.dataset.index;
            block.dataset.index = newIndex;

            block.querySelectorAll('.destinataires-checkboxes input[type="checkbox"]').forEach(cb => {
                cb.name = `destinataires[${newIndex}][]`;
            });
            block.querySelectorAll('.circuit-choix input[type="checkbox"]').forEach(cb => {
                cb.name = `circuit[${newIndex}][]`;
            });

            const title = block.querySelector('strong');
            if (title) title.innerHTML = `<i class="fas fa-file"></i> Document ${newIndex + 1}`;

            if (checklistState[oldIndex]) {
                newChecklistState[newIndex] = checklistState[oldIndex];
            } else {
                newChecklistState[newIndex] = { items: [], checked: {} };
            }

            const typeSelect = block.querySelector('.document-type-select');
            if (typeSelect) typeSelect.setAttribute('onchange', 'loadChecklist(this)');

            const checkAllBtn = block.querySelector('.checklist-check-all-btn');
            if (checkAllBtn) checkAllBtn.setAttribute('onclick', `toggleAllChecklist(${newIndex}, this)`);
        });

        checklistState = newChecklistState;
    }

    // ============================================================
    // CONSTRUCTION D'UN BLOC
    // ============================================================
    function buildDocumentBlock(index) {
        const block = document.createElement('div');
        block.className = 'document-block';
        block.dataset.index = index;

        block.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <strong class="doc-block-title">Document ${index + 1}</strong>
                ${index > 0 ? `<button type="button" onclick="removeDocumentBlock(this)" class="remove-doc-btn"><i class="fas fa-times"></i></button>` : ''}
            </div>

            <div class="form-group">
                <label>Fichier <span class="required">*</span></label>
                <input type="file" name="fichiers[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar">
                <div class="file-info"><i class="fas fa-info-circle"></i> Formats acceptés : PDF, Word, Excel, PowerPoint, TXT, ZIP, RAR. Taille max : 5 Mo par fichier</div>
            </div>

            <div class="doc-row2">
                <div class="form-group">
                    <label>Catégorie <span class="required">*</span></label>
                    <select class="categorie-select" required onchange="filtrerTypes(this)">
                        ${CATEGORIES_HTML}
                    </select>
                </div>
                <div class="form-group">
                    <label>Type de document <span class="required">*</span></label>
                    <select name="document_type_id[]" class="document-type-select" required disabled onchange="loadChecklist(this)">
                        <option value="">Choisissez d'abord une catégorie</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Titre du document <span class="required">*</span></label>
                <input type="text" name="titre[]" placeholder="Titre du document" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description[]" placeholder="Description du document..."></textarea>
            </div>

            <div class="form-group">
                <label>Type de transmission <span class="required">*</span></label>
                <select name="mode[]" class="mode-select" required onchange="toggleDestinataires(this)">
                    <option value="simple">Informatif</option>
                    <option value="validation">Circuit de visa</option>
                </select>
                <div style="font-size: var(--fs-s); color:#888; margin-top:4px;">
                    <i class="fas fa-info-circle"></i>
                    <span class="mode-description">Informatif : Transmission simple sans validation préalable.</span>
                </div>
            </div>

            <div class="form-group checklist-section" style="display:none;">
                <label>Vérifications requises <span class="required">*</span></label>
                <div class="checklist-info" style="font-size: var(--fs-s); color:#888; margin-bottom:10px; padding:8px 12px; background:#eff4ff; border-radius:6px; display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="fas fa-list-check"></i> Critères à valider</span>
                    <span style="display:flex; align-items:center; gap:12px;">
                        <button type="button" class="checklist-check-all-btn" onclick="toggleAllChecklist(${index}, this)" style="border:1px solid #2563eb; color:#2563eb; background:#fff; border-radius:6px; padding:2px 10px; font-size: var(--fs-s); font-weight:600; cursor:pointer;"><i class="fas fa-check-double"></i> Tout cocher</button>
                        <span class="progress checklist-progress" style="font-weight:600; color:#2563eb;">0/0 validés</span>
                    </span>
                </div>
                <div class="checklist-container checklist-items" style="background:#f8faff; border-radius:8px; padding:16px; border:1px solid #e0e8f0; max-height:200px; overflow-y:auto;"></div>
                <div class="checklist-error" style="display:none; color:#dc2626; font-size: var(--fs-s); margin-top:8px; padding:8px 12px; background:#fee2e2; border-radius:6px;">
                    <i class="fas fa-exclamation-circle"></i> Vous devez valider tous les critères obligatoires avant de soumettre.
                </div>
            </div>

            <div class="form-group circuit-section" style="display:none;">
                <label>Circuit de visa <span class="required">*</span></label>
                @include('partials.circuit-choix', ['nom' => 'circuit[${index}][]', 'coches' => ['bureau_controle']])
                <div style="font-size: var(--fs-s); color:#888; margin-top:8px;">
                    <i class="fas fa-info-circle"></i> Le document part en même temps chez toutes les structures cochées ; chacune vérifie et vise à son niveau. Il est validé lorsque toutes ont visé.
                </div>
            </div>

            <div class="form-group destinataire-section">
                <label>Destinataires <span class="required destinataire-required">*</span></label>
                <div class="destinataire-section-inner" style="background:#f8faff; border-radius:8px; padding:16px; border:1px solid #e0e8f0;">
                    <select class="destinataire-type-select" onchange="updateDestinataires(this)">
                        <option value="tout_le_monde">Tout le monde</option>
                        <option value="specifique">Spécifique (individus)</option>
                    </select>
                    <div class="search-wrapper" style="margin-top:8px; position:relative;">
                        <i class="fas fa-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#888; font-size: var(--fs-m);"></i>
                        <input type="text" class="destinataire-search" placeholder="Rechercher un destinataire..." style="padding-left:32px;" oninput="filterDestinataires(this)">
                    </div>
                    <div class="checkbox-group destinataires-checkboxes" style="margin-top:8px;"></div>
                    <div class="destinataire-info" style="font-size: var(--fs-s); color:#888; margin-top:8px; padding:8px 12px; background:#eff4ff; border-radius:6px;">
                        <i class="fas fa-info-circle"></i> Sélectionnez les destinataires pour la transmission.
                    </div>
                </div>
            </div>
        `;

        documentsContainer.appendChild(block);
        renderDestinataires(block, ALL_USERS, index);
        checklistState[index] = { items: [], checked: {} };

        block.querySelectorAll('.destinataires-checkboxes input[type="checkbox"]').forEach(cb => cb.checked = true);
        block.querySelector('.destinataires-checkboxes').style.display = 'none';
        block.querySelector('.search-wrapper').style.display = 'none';

        // Mettre à jour l'affichage de la taille
        updateTotalSizeDisplay();

        return block;
    }

    // ============================================================
    // DESTINATAIRES
    // ============================================================
    function renderDestinataires(block, users, index) {
        const container = block.querySelector('.destinataires-checkboxes');
        container.innerHTML = users.map(user => `
            <label data-search="${user.full_name.toLowerCase()} ${user.email.toLowerCase()} ${user.role.toLowerCase()}">
                <input type="checkbox" name="destinataires[${index}][]" value="${user.id}">
                <span class="user-info">
                    <span class="user-name">${user.full_name}</span>
                    <span class="user-detail">${user.email} · ${user.role}</span>
                </span>
            </label>
        `).join('');
    }

    function updateDestinataires(typeSelect) {
        const block = typeSelect.closest('.document-block');
        const index = block.dataset.index;
        const type = typeSelect.value;
        const filtered = ALL_USERS;

        renderDestinataires(block, filtered, index);
        block.querySelector('.destinataire-search').value = '';

        const listWrapper = block.querySelector('.destinataires-checkboxes');
        const searchWrapper = block.querySelector('.search-wrapper');

        if (type === 'specifique') {
            listWrapper.style.display = 'flex';
            searchWrapper.style.display = 'block';
            block.querySelectorAll('.destinataires-checkboxes input[type="checkbox"]').forEach(cb => cb.checked = false);
        } else if (type === 'aucun') {
            // Circuit de visa seul : personne d'autre ne reçoit le document
            listWrapper.style.display = 'none';
            searchWrapper.style.display = 'none';
            block.querySelectorAll('.destinataires-checkboxes input[type="checkbox"]').forEach(cb => cb.checked = false);
        } else {
            listWrapper.style.display = 'none';
            searchWrapper.style.display = 'none';
            block.querySelectorAll('.destinataires-checkboxes input[type="checkbox"]').forEach(cb => cb.checked = true);
        }
    }

    function filterDestinataires(input) {
        const search = input.value.toLowerCase();
        const block = input.closest('.document-block');
        block.querySelectorAll('.destinataires-checkboxes label').forEach(label => {
            const data = label.getAttribute('data-search') || '';
            label.style.display = data.includes(search) ? '' : 'none';
        });
    }

    // ============================================================
    // MODE TRANSMISSION
    // ============================================================
    function toggleDestinataires(modeSelect) {
        const block = modeSelect.closest('.document-block');
        const index = block.dataset.index;
        const mode = modeSelect.value;
        const destinataireInfo = block.querySelector('.destinataire-info');
        const destinataireRequired = block.querySelector('.destinataire-required');
        const modeDescription = block.querySelector('.mode-description');
        const checklistSection = block.querySelector('.checklist-section');
        const circuitSection = block.querySelector('.circuit-section');
        circuitSection.style.display = mode === 'validation' ? 'block' : 'none';

        if (mode === 'validation') {
            modeDescription.textContent = 'Circuit de visa : le document est visé par les structures que vous choisissez, chacune à son niveau. Des vérifications sont requises avant la transmission.';
            destinataireRequired.textContent = '(Optionnel)';
            destinataireInfo.innerHTML = '<i class="fas fa-info-circle"></i> En circuit de visa, le document est automatiquement envoyé aux structures cochées ci-dessus. Vous pouvez en plus le partager à des collègues (optionnel).';
            // Le partage à des collègues reste possible en plus du circuit : choix « Aucun » par défaut.
            const selectDest = block.querySelector('.destinataire-type-select');
            if (selectDest) selectDest.querySelector('option[value="specifique"]').textContent = 'Spécifique (partager à un collègue)';
            if (selectDest && !selectDest.querySelector('option[value="aucun"]')) {
                selectDest.insertBefore(new Option('Aucun (circuit de visa uniquement)', 'aucun'), selectDest.firstChild);
            }
            if (selectDest && selectDest.value !== 'specifique') {
                selectDest.value = 'aucun';
                updateDestinataires(selectDest);
            }
            block.querySelectorAll('.destinataires-checkboxes input[type="checkbox"]').forEach(cb => cb.disabled = false);
            loadChecklist(block.querySelector('.document-type-select'));
        } else {
            modeDescription.textContent = 'Informatif : Transmission simple sans validation préalable.';
            destinataireRequired.textContent = '*';
            destinataireInfo.innerHTML = '<i class="fas fa-info-circle"></i> Sélectionnez les destinataires pour la transmission.';
            block.querySelectorAll('.destinataires-checkboxes input[type="checkbox"]').forEach(cb => cb.disabled = false);
            checklistSection.style.display = 'none';
            const typeSelect = block.querySelector('.destinataire-type-select');
            if (typeSelect) {
                typeSelect.querySelector('option[value="specifique"]').textContent = 'Spécifique (individus)';
                const optionAucun = typeSelect.querySelector('option[value="aucun"]');
                if (optionAucun) {
                    if (typeSelect.value === 'aucun') typeSelect.value = 'tout_le_monde';
                    optionAucun.remove();
                }
                if (typeSelect.value !== 'specifique') {
                    updateDestinataires(typeSelect);
                }
            }
        }
        validateForm();
    }

    // ============================================================
    // CHECKLIST
    // ============================================================
    function loadChecklist(typeSelect) {
        const block = typeSelect.closest('.document-block');
        const index = block.dataset.index;
        const mode = block.querySelector('.mode-select').value;
        const checklistSection = block.querySelector('.checklist-section');
        const checklistItemsEl = block.querySelector('.checklist-items');
        const checklistProgress = block.querySelector('.checklist-progress');
        const typeId = typeSelect.value;

        if (mode !== 'validation' || !typeId) {
            checklistSection.style.display = 'none';
            validateForm();
            return;
        }

        checklistSection.style.display = 'block';
        checklistItemsEl.innerHTML = `<p style="font-size: var(--fs-m); color:#888; text-align:center; padding:16px 0;"><i class="fas fa-spinner fa-spin"></i> Chargement...</p>`;
        checklistState[index] = { items: [], checked: {} };

        fetch(`/entreprise/document-types/${typeId}/checklist`)
            .then(r => r.json())
            .then(data => {
                checklistState[index].items = data;

                if (data.length === 0) {
                    checklistItemsEl.innerHTML = `<p style="font-size: var(--fs-m); color:#888; text-align:center; padding:16px 0;"><i class="fas fa-check-circle" style="color:#00a86b;"></i> Aucune vérification requise.</p>`;
                    checklistProgress.textContent = '0/0 validés';
                    validateForm();
                    return;
                }

                checklistItemsEl.innerHTML = data.map(item => `
                    <div class="checklist-item" id="checklist-item-${index}-${item.id}" style="display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:1px solid #f0eeea;">
                        <input type="checkbox" onchange="toggleChecklistItem(${index}, ${item.id}, this.checked)">
                        <span style="flex:1; font-size: var(--fs-m);">${item.libelle}</span>
                        <span style="font-size: var(--fs-s); padding:2px 10px; border-radius:12px; ${item.obligatoire ? 'color:#dc2626; background:#fee2e2;' : 'color:#888; background:#f0f0f0;'}">${item.obligatoire ? 'Obligatoire' : 'Facultatif'}</span>
                    </div>
                `).join('');

                updateChecklistProgress(index);
                validateForm();
            })
            .catch(() => {
                checklistItemsEl.innerHTML = `<p style="font-size: var(--fs-m); color:#dc2626; text-align:center; padding:16px 0;">Erreur de chargement.</p>`;
                validateForm();
            });
    }

    function toggleChecklistItem(index, itemId, checked) {
        checklistState[index].checked[itemId] = checked;
        updateChecklistProgress(index);
        validateForm();
    }

    function toggleAllChecklist(index, btn) {
        const block = document.querySelector(`.document-block[data-index="${index}"]`);
        const state = checklistState[index];
        if (!state || !state.items.length) return;

        const shouldCheck = state.items.some(i => !state.checked[i.id]);

        state.items.forEach(item => {
            state.checked[item.id] = shouldCheck;
            const cb = block.querySelector(`#checklist-item-${index}-${item.id} input[type="checkbox"]`);
            if (cb) cb.checked = shouldCheck;
        });

        btn.innerHTML = shouldCheck
            ? '<i class="fas fa-times"></i> Tout décocher'
            : '<i class="fas fa-check-double"></i> Tout cocher';

        updateChecklistProgress(index);
        validateForm();
    }

    function updateChecklistProgress(index) {
        const block = document.querySelector(`.document-block[data-index="${index}"]`);
        const state = checklistState[index];
        const total = state.items.length;
        const checkedCount = state.items.filter(i => state.checked[i.id]).length;
        block.querySelector('.checklist-progress').textContent = `${checkedCount}/${total} validés`;

        const btn = block.querySelector('.checklist-check-all-btn');
        if (btn) {
            btn.innerHTML = (total > 0 && checkedCount === total)
                ? '<i class="fas fa-times"></i> Tout décocher'
                : '<i class="fas fa-check-double"></i> Tout cocher';
        }
    }

    function validateForm() {
        let allValid = true;

        // Vérification checklist
        document.querySelectorAll('.document-block').forEach(block => {
            const index = block.dataset.index;
            const mode = block.querySelector('.mode-select').value;
            const errorEl = block.querySelector('.checklist-error');

            if (mode === 'validation') {
                const state = checklistState[index] || { items: [], checked: {} };
                const obligatoires = state.items.filter(i => i.obligatoire);
                const ok = obligatoires.every(i => state.checked[i.id]);
                if (!ok && obligatoires.length > 0) {
                    allValid = false;
                    if (errorEl) errorEl.style.display = 'block';
                } else if (errorEl) {
                    errorEl.style.display = 'none';
                }
            }
        });

        // Vérification taille totale
        let totalSize = 0;
        document.querySelectorAll('input[type="file"]').forEach(input => {
            if (input.files[0]) totalSize += input.files[0].size;
        });
        if (totalSize > MAX_TOTAL_SIZE) {
            allValid = false;
        }

        submitBtn.disabled = !allValid;
        submitBtn.title = !allValid ? 'Veuillez corriger les erreurs avant de soumettre' : '';
    }

    // ============================================================
    // AJOUT / SUPPRESSION DE BLOCS
    // ============================================================
    function addDocumentBlock() {
        const count = document.querySelectorAll('.document-block').length;
        if (count >= MAX_DOCUMENTS) return;
        buildDocumentBlock(count);
        renumberDocumentBlocks();
        updateDocumentsCounter();
    }

    function removeDocumentBlock(btn) {
        const block = btn.closest('.document-block');
        if (document.querySelectorAll('.document-block').length > 1) {
            block.remove();
            renumberDocumentBlocks();
            updateDocumentsCounter();
            updateTotalSizeDisplay();
        } else {
            alert('Vous devez garder au moins un document.');
        }
    }

    function updateDocumentsCounter() {
        const count = document.querySelectorAll('.document-block').length;
        document.getElementById('documentsRemaining').textContent = MAX_DOCUMENTS - count;
        document.getElementById('addDocumentBtn').style.display = count >= MAX_DOCUMENTS ? 'none' : 'inline-flex';
    }

    // ============================================================
    // MODAL
    // ============================================================
    function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden';
        document.getElementById('transmissionForm').reset();
        documentsContainer.innerHTML = '';
        checklistState = {};
        documentIndex = 0;
        buildDocumentBlock(0);
        renumberDocumentBlocks();
        updateDocumentsCounter();
        submitBtn.disabled = false;
        updateTotalSizeDisplay();
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = '';
    }

    // ============================================================
    // SOUMISSION AVEC VALIDATION DÉTAILLÉE + TAILLE
    // ============================================================
    document.getElementById('transmissionForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Vérification taille totale (7 Mo max pour l'ensemble des documents)
        let totalSize = 0;
        const blocks = document.querySelectorAll('.document-block');

        blocks.forEach(block => {
            const fileInput = block.querySelector('input[type="file"]');
            if (fileInput.files[0]) {
                totalSize += fileInput.files[0].size;
            }
        });

        if (totalSize > MAX_TOTAL_SIZE) {
            alert(`La taille totale des fichiers (${(totalSize / 1024 / 1024).toFixed(2)} Mo) dépasse la limite autorisée de 7 Mo. Veuillez réduire la taille ou le nombre de fichiers.`);
            return;
        }

        let firstInvalidBlock = null;
        let errorMessage = '';

        for (const block of blocks) {
            const docNum = parseInt(block.dataset.index) + 1;

            const fileInput = block.querySelector('input[type="file"]');
            if (!fileInput.files || fileInput.files.length === 0) {
                errorMessage = `Document ${docNum} : veuillez sélectionner un fichier.`;
                firstInvalidBlock = block;
                break;
            }

            const typeSelect = block.querySelector('.document-type-select');
            if (!typeSelect.value) {
                errorMessage = `Document ${docNum} : veuillez choisir une catégorie puis un type de document.`;
                firstInvalidBlock = block;
                break;
            }

            const titreInput = block.querySelector('input[name="titre[]"]');
            if (!titreInput.value.trim()) {
                errorMessage = `Document ${docNum} : veuillez saisir un titre.`;
                firstInvalidBlock = block;
                break;
            }

            const mode = block.querySelector('.mode-select').value;
            const checkedBoxes = block.querySelectorAll('.destinataires-checkboxes input[type="checkbox"]:checked');
            if (mode === 'simple' && checkedBoxes.length === 0) {
                errorMessage = `Document ${docNum} : veuillez sélectionner au moins un destinataire.`;
                firstInvalidBlock = block;
                break;
            }

            if (mode === 'validation') {
                if (block.querySelectorAll('.circuit-choix input[type="checkbox"]:checked').length === 0) {
                    errorMessage = `Document ${docNum} : veuillez choisir au moins une structure dans le circuit de visa.`;
                    firstInvalidBlock = block;
                    break;
                }
                const index = block.dataset.index;
                const state = checklistState[index] || { items: [], checked: {} };
                const obligatoires = state.items.filter(i => i.obligatoire);
                const allChecked = obligatoires.every(i => state.checked[i.id]);
                if (!allChecked && obligatoires.length > 0) {
                    errorMessage = `Document ${docNum} : veuillez valider tous les critères obligatoires.`;
                    firstInvalidBlock = block;
                    break;
                }
            }
        }

        if (errorMessage) {
            alert(errorMessage);
            if (firstInvalidBlock) {
                firstInvalidBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalidBlock.style.border = '2px solid #dc2626';
                setTimeout(() => { firstInvalidBlock.style.border = ''; }, 3000);
            }
            return;
        }

        // Visa du responsable : renseigne la signature avant la soumission réelle
        if (window.visaPrepare && !window.visaPrepare(this)) return;

        // Tout est valide → soumission réelle
        this.submit();
    });

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
</script>
@endsection
