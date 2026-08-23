<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dossiers documentaires') }}
            </h2>
            <a href="{{ route('dossiers.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-800">
                Importer un document
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <x-input-label for="statut" value="Statut" />
                        <select id="statut" name="statut" class="mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="">Tous</option>
                            @foreach(['transmis','soumis','en_analyse','valide','a_corriger','archive'] as $statut)
                                <option value="{{ $statut }}" {{ request('statut') === $statut ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $statut)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="structure_emettrice_id" value="Structure" />
                        <select id="structure_emettrice_id" name="structure_emettrice_id" class="mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="">Toutes</option>
                            @foreach($structures as $structure)
                                <option value="{{ $structure->id }}" {{ request('structure_emettrice_id') == $structure->id ? 'selected' : '' }}>{{ $structure->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="document_type_id" value="Type" />
                        <select id="document_type_id" name="document_type_id" class="mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="">Tous</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type->id }}" {{ request('document_type_id') == $type->id ? 'selected' : '' }}>[{{ $type->code }}] {{ $type->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-secondary-button type="submit">Filtrer</x-secondary-button>
                    <a href="{{ route('dossiers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Réinitialiser</a>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Identifiant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Structure</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Version</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dossiers as $dossier)
                            <tr>
                                <td class="px-6 py-4 text-sm font-mono text-gray-700">{{ $dossier->identifiant_affiche }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $dossier->titre }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $dossier->documentType->code }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $dossier->structureEmettrice?->nom ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">V{{ $dossier->version_courante }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ $dossier->libelle_statut }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right">
                                    <a href="{{ route('dossiers.show', $dossier) }}" class="text-emerald-700 hover:text-emerald-900">Voir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Aucun dossier pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">{{ $dossiers->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
