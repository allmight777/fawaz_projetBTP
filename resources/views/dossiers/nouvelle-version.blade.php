<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importer une correction — ') }}{{ $dossier->identifiant }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-4">
                    Ce dossier a été renvoyé pour correction. L'import d'une nouvelle version relancera un cycle
                    d'analyse complet (V{{ $dossier->version_courante + 1 }}).
                </p>
                <form method="POST" action="{{ route('dossiers.versions.store', $dossier) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="fichier" :value="__('Fichier corrigé')" />
                        <input id="fichier" name="fichier" type="file" class="mt-1 block w-full text-sm text-gray-600" />
                        <x-text-input id="fichier_url" name="fichier_url" type="url" class="mt-2 block w-full" :value="old('fichier_url')" placeholder="ou lien externe" />
                        <x-input-error class="mt-2" :messages="$errors->get('fichier')" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="commentaire" :value="__('Commentaire')" />
                        <textarea id="commentaire" name="commentaire" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('commentaire') }}</textarea>
                    </div>

                    <x-primary-button>Importer la nouvelle version</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
