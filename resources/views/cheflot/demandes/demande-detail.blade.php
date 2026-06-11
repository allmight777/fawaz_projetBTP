@extends('layouts.cheflot')

@section('title', 'Détail de la demande')

@section('content')
<style>
    .detail-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    .detail-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
        flex-wrap: wrap;
        gap: 15px;
    }
    .detail-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: #064e3b;
        margin: 0;
    }
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-attente { background: #fef3c7; color: #d97706; }
    .badge-controle { background: #dbeafe; color: #2563eb; }
    .badge-valide { background: #d1fae5; color: #059669; }
    .badge-rejete { background: #fee2e2; color: #dc2626; }
    .badge-basse { background: #d1fae5; color: #059669; }
    .badge-moyenne { background: #fef3c7; color: #d97706; }
    .badge-haute { background: #fee2e2; color: #dc2626; }
    .badge-urgente { background: #fecaca; color: #b91c1c; }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    .info-item {
        margin-bottom: 15px;
    }
    .info-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .info-value {
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
    }
    .btn-primary {
        background: linear-gradient(135deg, #047857, #065f46);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        cursor: pointer;
    }
    .btn-primary:hover { transform: translateY(-1px); }
    .btn-secondary {
        background: #6b7280;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .fichier-card {
        background: #f0fdf4;
        border: 1px solid #047857;
        border-radius: 12px;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }
    .controleur-card {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border: 2px solid #047857;
        border-radius: 16px;
        padding: 20px;
        margin-top: 10px;
    }
    .controleur-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #bbf7d0;
    }
    .controleur-avatar {
        width: 50px;
        height: 50px;
        background: #047857;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 20px;
    }
    .controleur-info h4 {
        font-size: 16px;
        font-weight: 700;
        color: #064e3b;
        margin: 0 0 5px 0;
    }
    .controleur-info p {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }
    .controleur-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .controleur-detail-item {
        background: white;
        padding: 10px;
        border-radius: 10px;
    }
    .controleur-detail-label {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 3px;
    }
    .controleur-detail-value {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
    }
    .non-assigne-card {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        color: #d97706;
    }
    .assign-form {
        background: #f9fafb;
        padding: 20px;
        border-radius: 12px;
        margin-top: 20px;
    }
    select {
        padding: 10px 15px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        min-width: 250px;
        font-size: 14px;
    }
    .text-danger { color: #dc2626; }
    .text-success { color: #059669; }
    @media (max-width: 768px) {
        .info-grid { grid-template-columns: 1fr; }
        .detail-card { padding: 20px; }
        .controleur-details { grid-template-columns: 1fr; }
    }
</style>

<div class="detail-container">
    <!-- Informations principales -->
    <div class="detail-card">
        <div class="detail-header">
            <h3><i class="fas fa-file-alt"></i> Demande #{{ $demande->numero_demande }}</h3>
            <div>
                <span class="badge @if($demande->statut == 'EN ATTENTE') badge-attente @elseif($demande->statut == 'EN CONTROLE') badge-controle @elseif($demande->statut == 'VALIDE') badge-valide @else badge-rejete @endif">
                    {{ $demande->statut }}
                </span>
                <span class="badge badge-{{ strtolower($demande->priorite) }}">{{ $demande->priorite }}</span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label"><i class="fas fa-building"></i> Entreprise</div>
                <div class="info-value">{{ $demande->entreprise }}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-layer-group"></i> Lot</div>
                <div class="info-value">{{ $demande->lot->nom ?? 'Non affecté' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-user"></i> Soumis par</div>
                <div class="info-value">{{ $demande->soumisPar->full_name ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-calendar"></i> Date de soumission</div>
                <div class="info-value">{{ $demande->date_soumission->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-tag"></i> Type de document</div>
                <div class="info-value">{{ $demande->type_document }}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-code-branch"></i> Version</div>
                <div class="info-value">{{ $demande->version }}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-calendar-alt"></i> Échéance</div>
                <div class="info-value">{{ $demande->echeance_controle ? $demande->echeance_controle->format('d/m/Y') : 'Non définie' }}</div>
            </div>
        </div>

        <div class="info-item" style="grid-column: span 2;">
            <div class="info-label"><i class="fas fa-align-left"></i> Description</div>
            <div class="info-value">{{ $demande->description ?? 'Aucune description' }}</div>
        </div>
    </div>

    <!-- Fichier joint -->
    @if($demande->hasFichier())
    <div class="detail-card">
        <h4 style="margin-bottom: 15px;"><i class="fas fa-paperclip"></i> Fichier joint</h4>
        <div class="fichier-card">
            <div>
                <i class="fas fa-file-alt" style="color: #047857;"></i>
                <span>{{ $demande->fichier_nom ?? 'Document' }}</span>
            </div>
            <a href="{{ $demande->fichier }}" target="_blank" class="btn-primary">
                <i class="fas fa-download"></i> Télécharger / Voir
            </a>
        </div>
    </div>
    @endif

    <!-- Section Contrôleur en charge -->
    <div class="detail-card">
        <h4><i class="fas fa-user-check"></i> Contrôleur en charge</h4>
        <br>

        @if($demande->controleur)
            <!-- Affichage des détails du contrôleur qui a pris en charge -->
            <div class="controleur-card">
                <div class="controleur-header">
                    <div class="controleur-avatar">
                        {{ strtoupper(substr($demande->controleur->nom, 0, 1)) }}
                    </div>
                    <div class="controleur-info">
                        <h4>{{ $demande->controleur->full_name }}</h4>
                        <p><i class="fas fa-envelope"></i> {{ $demande->controleur->email }}</p>
                    </div>
                </div>
                <div class="controleur-details">
                    <div class="controleur-detail-item">
                        <div class="controleur-detail-label"><i class="fas fa-tag"></i> Rôle</div>
                        <div class="controleur-detail-value">{{ $demande->controleur->role }}</div>
                    </div>
                    <div class="controleur-detail-item">
                        <div class="controleur-detail-label"><i class="fas fa-layer-group"></i> Lot affecté</div>
                        <div class="controleur-detail-value">{{ $demande->controleur->lot->nom ?? 'Aucun lot' }}</div>
                    </div>
                    <div class="controleur-detail-item">
                        <div class="controleur-detail-label"><i class="fas fa-calendar-alt"></i> Date de prise en charge</div>
                        <div class="controleur-detail-value">{{ $demande->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="controleur-detail-item">
                        <div class="controleur-detail-label"><i class="fas fa-hourglass-half"></i> Délai de traitement</div>
                        <div class="controleur-detail-value">
                            @if($demande->echeance_controle)
                                @if($demande->echeance_controle < now())
                                    <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Délai dépassé</span>
                                @else
                                    <span class="text-success"><i class="fas fa-clock"></i> {{ now()->diffInDays($demande->echeance_controle) }} jours restants</span>
                                @endif
                            @else
                                Non défini
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Aucun contrôleur n'a encore pris en charge -->
            <div class="non-assigne-card">
                <i class="fas fa-user-clock" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                <p><strong><i class="fas fa-hourglass-start"></i> Pas encore pris en charge</strong></p>
                <p style="font-size: 14px; margin-top: 10px;">Aucun contrôleur n'a encore été assigné à cette demande.</p>
                <p style="font-size: 13px; margin-top: 5px;">La demande est en attente d'assignation par le chef de lot.</p>
            </div>

            <!-- Formulaire d'assignation visible uniquement pour le chef de lot -->
            @if(Auth::user()->role == 'CHEF LOT')
            <div class="assign-form">
                <form action="{{ route('cheflot.demandes.assigner', $demande) }}" method="POST">
                    @csrf
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <select name="controleur_id" required style="flex: 1;">
                            <option value="">-- Sélectionner un contrôleur --</option>
                            @foreach($controleurs as $controleur)
                                <option value="{{ $controleur->id }}">
                                    {{ $controleur->full_name }} - {{ $controleur->lot->nom ?? 'Aucun lot' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check-circle"></i> Assigner ce contrôleur
                        </button>
                    </div>
                </form>
            </div>
            @endif
        @endif
    </div>

    <!-- Historique des versions -->
    @if($demande->versions->count() > 0)
    <div class="detail-card">
        <h4><i class="fas fa-history"></i> Versions antérieures</h4>
        <div style="overflow-x: auto;">
            <table style="width:100%; margin-top:15px;">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demande->versions as $version)
                    <tr>
                        <td>{{ $version->version }}</td>
                        <td>{{ $version->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="badge badge-valide">Validée</span></td>
                        <td>
                            <a href="{{ route('cheflot.demandes.show', $version->id) }}" class="btn-primary" style="padding:4px 12px; font-size:12px;">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Boutons retour -->
    <div style="display: flex; gap: 15px; justify-content: flex-end;">
        <a href="{{ route('cheflot.demandes') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>
@endsection
