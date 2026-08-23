<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Checklist — ') }}{{ $documentVersion->dossier->identifiant }} (V{{ $documentVersion->numero_version }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if($items->isEmpty())
                    <p class="text-sm text-gray-600">Ce type de document n'a pas de checklist paramétrée.</p>
                    <form method="POST" action="{{ route('document-versions.checklist.store', $documentVersion) }}" class="mt-4">
                        @csrf
                        <x-primary-button>Soumettre au Bureau de Contrôle</x-primary-button>
                    </form>
                @else
                    <form method="POST" action="{{ route('document-versions.checklist.store', $documentVersion) }}">
                        @csrf
                        <div class="space-y-4">
                            @foreach($items as $item)
                                @php $reponse = $reponses->get($item->id); @endphp
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <label class="flex items-start gap-3">
                                        <input type="checkbox" name="reponses[{{ $item->id }}][coche]" value="1" {{ $reponse?->coche ? 'checked' : '' }}
                                               class="mt-1 rounded border-gray-300 text-emerald-700 focus:ring-emerald-700">
                                        <span class="text-sm font-medium text-gray-800">
                                            {{ $item->libelle }}
                                            @if($item->obligatoire) <span class="text-red-600">*</span> @endif
                                        </span>
                                    </label>
                                    <input type="text" name="reponses[{{ $item->id }}][commentaire]" value="{{ $reponse?->commentaire }}"
                                           placeholder="Commentaire (optionnel)"
                                           class="mt-2 block w-full text-sm border-gray-300 rounded-md shadow-sm">
                                </div>
                            @endforeach
                        </div>

                        @if(session('error'))
                            <p class="text-sm text-red-600 mt-4">{{ session('error') }}</p>
                        @endif

                        <div class="mt-6">
                            <x-primary-button>Valider la checklist et soumettre</x-primary-button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
