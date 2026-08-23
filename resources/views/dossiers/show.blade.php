@php
    $derniereVersion = $dossier->versions->last();
    $user = Auth::user();
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $dossier->identifiant_affiche }} — {{ $dossier->titre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="space-x-2">
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ $dossier->libelle_statut }}</span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">{{ $dossier->mode_traitement === 'validation' ? 'Validation documentaire' : 'Transmission simple' }}</span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">V{{ $dossier->version_courante }}</span>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        @if($dossier->statut === 'a_corriger' && $dossier->cree_par === $user->id)
                            <a href="{{ route('dossiers.versions.create', $dossier) }}" class="inline-flex items-center px-3 py-2 bg-amber-600 text-white text-xs font-semibold rounded-md hover:bg-amber-700">Importer la correction</a>
                        @endif
                        @if($dossier->mode_traitement === 'simple' && $dossier->statut === 'transmis' && $dossier->transmissions->isEmpty())
                            <a href="{{ route('dossiers.transmettre.form', $dossier) }}" class="inline-flex items-center px-3 py-2 bg-emerald-700 text-white text-xs font-semibold rounded-md hover:bg-emerald-800">Transmettre</a>
                        @endif
                        @if($user->role === 'CHEF LOT' && $derniereVersion && $derniereVersion->statut === 'soumis')
                            <a href="{{ route('cheflot.dossiers.affecter', $derniereVersion) }}" class="inline-flex items-center px-3 py-2 bg-emerald-700 text-white text-xs font-semibold rounded-md hover:bg-emerald-800">Affecter un contrôleur</a>
                        @endif
                        @if($user->role === 'CHEF LOT' && $derniereVersion && $derniereVersion->statut === 'en_analyse')
                            <a href="{{ route('cheflot.dossiers.decision', $derniereVersion) }}" class="inline-flex items-center px-3 py-2 bg-emerald-700 text-white text-xs font-semibold rounded-md hover:bg-emerald-800">Consolidation / Décision</a>
                        @endif
                        @if($user->role === 'CHEF LOT' && $dossier->statut === 'valide')
                            <a href="{{ route('cheflot.dossiers.diffusion', $dossier) }}" class="inline-flex items-center px-3 py-2 bg-emerald-700 text-white text-xs font-semibold rounded-md hover:bg-emerald-800">Diffuser</a>
                        @endif
                        @if(($user->role === 'CHEF LOT' || $dossier->cree_par === $user->id) && in_array($dossier->statut, ['valide', 'transmis']))
                            <button
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-archive-{{ $dossier->id }}')"
                                type="button"
                                class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-xs font-semibold rounded-md hover:bg-gray-700"
                            >Archiver</button>

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
                                            <x-danger-button>Confirmer l'archivage</x-danger-button>
                                        </form>
                                    </div>
                                </div>
                            </x-modal>
                        @endif
                    </div>
                </div>

                <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 text-sm">
                    <div><dt class="text-gray-500">Type</dt><dd class="font-medium">[{{ $dossier->documentType->code }}] {{ $dossier->documentType->nom }}</dd></div>
                    <div><dt class="text-gray-500">Structure émettrice</dt><dd class="font-medium">{{ $dossier->structureEmettrice?->nom ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Lot</dt><dd class="font-medium">{{ $dossier->lot?->nom ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Créé par</dt><dd class="font-medium">{{ $dossier->creePar->full_name }}</dd></div>
                </dl>
                @if($dossier->description)
                    <p class="mt-4 text-sm text-gray-600">{{ $dossier->description }}</p>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Historique des versions</h3>
                <div class="space-y-4">
                    @foreach($dossier->versions as $version)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">V{{ $version->numero_version }}</span>
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ $version->libelle_statut }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Importée par {{ $version->importePar->full_name }} le {{ $version->date_import->format('d/m/Y H:i') }}</p>
                            @if($version->hasFichier())
                                <a href="{{ route('dossiers.telecharger', [$dossier, $version]) }}" class="text-sm text-emerald-700 hover:underline">Télécharger le fichier</a>
                            @endif
                            @if($version->statut === 'en_attente_checklist' && $dossier->cree_par === $user->id)
                                <a href="{{ route('document-versions.checklist', $version) }}" class="block text-sm text-amber-700 hover:underline mt-1">Compléter la checklist</a>
                            @endif

                            @if($peutVoirAnalyse)
                                @foreach($version->affectations as $affectation)
                                    <div class="mt-3 ml-4 border-l-2 border-gray-200 pl-4">
                                        <p class="text-xs font-medium text-gray-700">Contrôleur : {{ $affectation->controleur->full_name }} @if($affectation->specialite) ({{ $affectation->specialite }}) @endif — {{ $affectation->statut }}</p>
                                        @foreach($affectation->observations as $observation)
                                            <div class="text-xs text-gray-600 mt-1 {{ $observation->retenue ? 'font-semibold text-amber-700' : '' }}">
                                                <span class="uppercase">{{ $observation->type === 'non_conformite' ? 'Non-conformité' : 'Observation' }}</span>
                                                — {{ $observation->auteur->full_name }} : {{ $observation->contenu }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach

                                @foreach($version->decisions as $decision)
                                    <div class="mt-3 text-sm {{ $decision->estValidee() ? 'text-emerald-700' : 'text-red-700' }}">
                                        Décision : <strong>{{ $decision->estValidee() ? 'Validé' : 'À corriger' }}</strong>
                                        par {{ $decision->validateur->full_name }} le {{ $decision->date_decision->format('d/m/Y H:i') }}
                                        @if($decision->commentaires) — {{ $decision->commentaires }} @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if($dossier->transmissions->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Transmissions</h3>
                    <div class="space-y-3">
                        @foreach($dossier->transmissions as $transmission)
                            <div class="text-sm border-b border-gray-100 pb-2">
                                <span class="font-medium">{{ $transmission->mode === 'simple' ? 'Transmission simple' : 'Diffusion post-validation' }}</span>
                                par {{ $transmission->emetteur->full_name }} le {{ $transmission->date_transmission->format('d/m/Y H:i') }}
                                <div class="text-xs text-gray-500">
                                    Destinataires :
                                    {{ $transmission->destinataires->map(fn($d) => $d->structure?->nom ?? $d->user?->full_name)->filter()->implode(', ') ?: '—' }}
                                </div>
                                @if($transmission->commentaire)
                                    <div class="text-xs text-gray-600 mt-1">{{ $transmission->commentaire }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
