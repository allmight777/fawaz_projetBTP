<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transmettre — ') }}{{ $dossier->identifiant }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('dossiers.transmettre', $dossier) }}">
                    @csrf

                    <div class="mb-4">
                        <x-input-label value="Structures destinataires" />
                        <div class="mt-2 space-y-2">
                            @foreach($structures as $structure)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="structures[]" value="{{ $structure->id }}" class="rounded border-gray-300 text-emerald-700">
                                    {{ $structure->nom }} ({{ $structure->libelle_type }})
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-input-label value="Utilisateurs destinataires" />
                        <div class="mt-2 space-y-2 max-h-48 overflow-y-auto">
                            @foreach($users as $destinataire)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="users[]" value="{{ $destinataire->id }}" class="rounded border-gray-300 text-emerald-700">
                                    {{ $destinataire->full_name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <x-input-error class="mt-2" :messages="$errors->get('destinataires')" />

                    <div class="mb-4">
                        <x-input-label for="commentaire" value="Commentaire" />
                        <textarea id="commentaire" name="commentaire" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>

                    <x-primary-button>Transmettre</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
