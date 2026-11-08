@extends(auth()->user()->spaceLayout())

@section('title', 'Archives')

@section('content')
<div class="card" style="background:white; border-radius:20px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
    <h3>
        <i class="fas fa-box-archive"></i> Documents validés &amp; archives
    </h3>

    <div style="overflow-x:auto;">
        <table data-filtres="statut" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <th style="padding:10px; text-align:left; font-size: var(--fs-s); text-transform:uppercase; color:#6b7280;">Identifiant</th>
                    <th style="padding:10px; text-align:left; font-size: var(--fs-s); text-transform:uppercase; color:#6b7280;">Titre</th>
                    <th style="padding:10px; text-align:left; font-size: var(--fs-s); text-transform:uppercase; color:#6b7280;">Versions</th>
                    <th style="padding:10px; text-align:left; font-size: var(--fs-s); text-transform:uppercase; color:#6b7280;">Statut</th>
                    <th style="padding:10px; text-align:left; font-size: var(--fs-s); text-transform:uppercase; color:#6b7280;">Visas</th>
                    <th style="padding:10px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($dossiers as $dossier)
                <tr style="border-bottom:1px solid #f9fafb;">
                    <td style="padding:12px 10px; font-family:monospace; font-size: var(--fs-m);">{{ $dossier->identifiant_affiche }}</td>
                    <td style="padding:12px 10px; font-size: var(--fs-m);">{{ $dossier->titre }}</td>
                    <td style="padding:12px 10px; font-size: var(--fs-m); color:#6b7280;">
                        @foreach($dossier->versions as $version)
                            <span style="display:inline-block; margin-right:6px;">V{{ $version->numero_version }}</span>
                        @endforeach
                    </td>
                    <td style="padding:12px 10px;">
                        @if($dossier->statut === 'archive')
                            <span style="padding:4px 12px; border-radius:20px; font-size: var(--fs-s); font-weight:600; background:#e5e7eb; color:#374151;">
                                <i class="fas fa-box-archive"></i> Archivé
                            </span>
                        @else
                            <span style="padding:4px 12px; border-radius:20px; font-size: var(--fs-s); font-weight:600; background:var(--accent-3); color:var(--accent-d);">
                                <i class="fas fa-check-circle"></i> Validé
                            </span>
                        @endif
                    </td>
                    <td style="padding:12px 10px;">@include('partials.visas-colonne')</td>
                    <td style="padding:12px 10px; text-align:right; white-space:nowrap;">
                        <a href="{{ route('dossiers.show', $dossier) }}" style="color:var(--accent); font-weight:600; font-size: var(--fs-m); text-decoration:none; margin-right:14px;">Consulter</a>

                        @if($dossier->archivablePar(auth()->user()))
                            <button
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-archive-{{ $dossier->id }}')"
                                type="button"
                                style="background:#fee2e2; color:#b91c1c; border:none; padding:8px 14px; border-radius:8px; font-weight:600; font-size: var(--fs-s); cursor:pointer;"
                            >
                                <i class="fas fa-box-archive"></i> Archiver
                            </button>

                            <x-modal name="confirm-archive-{{ $dossier->id }}" maxWidth="md">
                                <div class="p-6">
                                    <h2 class="text-lg font-medium text-gray-900">
                                        Archiver ce document ?
                                    </h2>
                                    <p class="mt-2 text-sm text-gray-600">
                                        Le document <strong>{{ $dossier->identifiant_affiche }}</strong> — « {{ $dossier->titre }} » sera figé : plus aucune modification ne sera possible (ni par l'émetteur, ni par le Bureau de Contrôle). Il restera consultable et téléchargeable. Toutes les parties prenantes (émetteur, contrôleurs, Maître d'Ouvrage du lot) seront prévenues par email.
                                    </p>

                                    <div class="mt-6 flex justify-end gap-3">
                                        <x-secondary-button x-on:click="$dispatch('close')">
                                            Annuler
                                        </x-secondary-button>

                                        <form method="POST" action="{{ route('dossiers.archiver', $dossier) }}">
                                            @csrf
                                            <x-danger-button>
                                                <i class="fas fa-box-archive"></i> Confirmer l'archivage
                                            </x-danger-button>
                                        </form>
                                    </div>
                                </div>
                            </x-modal>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:32px; text-align:center; color:#9ca3af; font-size: var(--fs-m);">Aucun document validé pour le moment.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $dossiers->links() }}
    </div>
</div>
@endsection
