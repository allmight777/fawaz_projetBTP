@extends('layouts.cheflot')

@section('title', 'Historique des modifications')

@section('content')
<style>
    .historique-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    .historique-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .historique-header {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
    }
    .historique-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: #064e3b;
        margin: 0;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f3f4f6;
    }
    .timeline-icon {
        position: absolute;
        left: -22px;
        top: 0;
        width: 24px;
        height: 24px;
        background: #047857;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
    }
    .timeline-content {
        padding-left: 20px;
    }
    .timeline-title {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 5px;
    }
    .timeline-date {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 8px;
    }
    .timeline-details {
        background: #f9fafb;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 14px;
        margin-top: 8px;
    }
    .badge-change {
        background: #e5e7eb;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        display: inline-block;
        margin: 2px;
    }
    .btn-back {
        background: #6b7280;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    @media (max-width: 768px) {
        .historique-card { padding: 20px; }
        .timeline { padding-left: 20px; }
        .timeline-icon { left: -17px; width: 20px; height: 20px; font-size: 10px; }
    }
</style>

<div class="historique-container">
    <div class="historique-card">
        <div class="historique-header">
            <h3><i class="fas fa-history"></i> Historique de la demande #{{ $demande->numero_demande }}</h3>
            <p style="color: #6b7280; margin-top: 5px;">{{ $demande->titre_document }}</p>
        </div>

        <div class="timeline">
            @forelse($demande->historiques->sortByDesc('created_at') as $historique)
            <div class="timeline-item">
                <div class="timeline-icon">
                    @if($historique->action == 'CREATION')
                        <i class="fas fa-plus"></i>
                    @elseif($historique->action == 'ASSIGNATION')
                        <i class="fas fa-user-check"></i>
                    @elseif($historique->action == 'VALIDATION')
                        <i class="fas fa-check"></i>
                    @elseif($historique->action == 'REJET')
                        <i class="fas fa-times"></i>
                    @else
                        <i class="fas fa-edit"></i>
                    @endif
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">
                        @if($historique->action == 'CREATION') Demande créée
                        @elseif($historique->action == 'ASSIGNATION') Contrôleur assigné
                        @elseif($historique->action == 'VALIDATION') Demande validée
                        @elseif($historique->action == 'REJET') Demande rejetée
                        @else {{ $historique->action }}
                        @endif
                    </div>
                    <div class="timeline-date">
                        <i class="fas fa-calendar-alt"></i> {{ $historique->created_at->format('d/m/Y H:i') }}
                        par <strong>{{ $historique->user->full_name ?? 'Système' }}</strong>
                    </div>
                    @if($historique->details)
                        <div class="timeline-details">
                            <i class="fas fa-comment"></i> {{ $historique->details }}
                        </div>
                    @endif
                    @if($historique->ancien_statut || $historique->nouveau_statut)
                        <div style="margin-top: 8px;">
                            <span class="badge-change">Statut: {{ $historique->ancien_statut }} → {{ $historique->nouveau_statut }}</span>
                        </div>
                    @endif
                    @if($historique->ancienne_version || $historique->nouvelle_version)
                        <div style="margin-top: 5px;">
                            <span class="badge-change">Version: {{ $historique->ancienne_version }} → {{ $historique->nouvelle_version }}</span>
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-history" style="font-size: 48px; color: #ccc;"></i>
                <p style="margin-top: 10px;">Aucun historique trouvé</p>
            </div>
            @endforelse
        </div>

        <div style="margin-top: 30px; text-align: right;">
            <a href="{{ route('cheflot.demandes.show', $demande->id) }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour à la demande
            </a>
        </div>
    </div>
</div>
@endsection
