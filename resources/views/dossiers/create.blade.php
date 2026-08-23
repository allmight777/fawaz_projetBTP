<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importer un document') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('dossiers.store') }}" enctype="multipart/form-data"
                      x-data="{
                          typeId: '{{ old('document_type_id') }}',
                          modeTraitement: null,
                          checklist: [],
                          async chargerChecklist() {
                              if (!this.typeId) { this.checklist = []; this.modeTraitement = null; return; }
                              const type = @js($documentTypes->keyBy('id'));
                              this.modeTraitement = type[this.typeId]?.mode_traitement ?? null;
                              const res = await fetch('/document-types/' + this.typeId + '/checklist');
                              this.checklist = await res.json();
                          }
                      }" x-init="chargerChecklist()">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="document_type_id" :value="__('Type de document')" />
                        <select id="document_type_id" name="document_type_id" x-model="typeId" @change="chargerChecklist()"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="">Sélectionner un type</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type->id }}" {{ old('document_type_id') == $type->id ? 'selected' : '' }}>
                                    [{{ $type->code }}] {{ $type->nom }} — {{ $type->libelle_categorie }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('document_type_id')" />
                    </div>

                    <div class="mb-4" x-show="modeTraitement" x-cloak>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                              :class="modeTraitement === 'validation' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'">
                            <span x-text="modeTraitement === 'validation' ? 'Soumis à validation par le Bureau de Contrôle' : 'Transmission simple (sans validation)'"></span>
                        </span>
                    </div>

                    <div class="mb-4" x-show="checklist.length > 0" x-cloak>
                        <p class="text-sm font-medium text-gray-700 mb-2">Checklist requise pour ce type de document :</p>
                        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                            <template x-for="item in checklist" :key="item.id">
                                <li x-text="item.libelle + (item.obligatoire ? ' (obligatoire)' : '')"></li>
                            </template>
                        </ul>
                        <p class="text-xs text-gray-500 mt-1">Elle sera à compléter juste après l'import.</p>
                    </div>

                    <div class="mb-4">
                        <x-input-label :value="__('Structure émettrice')" />
                        <p class="mt-1 text-sm text-gray-600">
                            {{ Auth::user()->structure?->nom ?? 'Aucune structure associée à votre compte — le dossier sera importé sans structure émettrice.' }}
                        </p>
                        <p class="text-xs text-gray-400">Déterminée automatiquement à partir de votre compte, non modifiable ici.</p>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="lot_id" :value="__('Lot')" />
                        <select id="lot_id" name="lot_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Sélectionner</option>
                            @foreach($lots as $lot)
                                <option value="{{ $lot->id }}" {{ old('lot_id') == $lot->id ? 'selected' : '' }}>{{ $lot->code }} - {{ $lot->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="titre" :value="__('Titre du document')" />
                        <x-text-input id="titre" name="titre" type="text" class="mt-1 block w-full" :value="old('titre')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('titre')" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="fichier" :value="__('Fichier')" />
                        <input id="fichier" name="fichier" type="file" class="mt-1 block w-full text-sm text-gray-600" />
                        <p class="text-xs text-gray-500 mt-1">ou renseignez un lien externe ci-dessous</p>
                        <x-text-input id="fichier_url" name="fichier_url" type="url" class="mt-2 block w-full" :value="old('fichier_url')" placeholder="https://..." />
                        <x-input-error class="mt-2" :messages="$errors->get('fichier')" />
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        <x-primary-button>{{ __('Importer') }}</x-primary-button>
                        <a href="{{ route('dossiers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
