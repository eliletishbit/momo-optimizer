@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Paramètres de paiement</h1>
        <p class="mt-2 text-gray-500 text-sm">Gérez vos moyens de paiement préférés pour des résultats personnalisés.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Section -->
        <div class="lg:col-span-1">
            <x-card title="Ajouter un moyen">
                <!-- Filtre par catégorie -->
                <div class="mb-4">
                    <label for="category-filter" class="block text-sm font-medium text-gray-700">Filtrer par catégorie</label>
                    <select id="category-filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" onchange="window.location.href = this.value">
                        <option value="{{ route('settings') }}">Toutes</option>
                        @foreach($categories as $cat)
                            <option value="{{ route('settings', ['category' => $cat]) }}" {{ $selectedCategory == $cat ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $cat)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <form action="{{ route('settings.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <x-form.select name="method_id" label="Réseau Mobile">
                        @foreach($availableMethods as $method)
                            <option value="{{ $method->id }}">
                                {{ $method->name }}
                                @if($method->country_code)
                                    ({{ $method->country_code }})
                                @endif
                            </option>
                        @endforeach
                    </x-form.select>

                    <div class="space-y-3 pt-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_preferred_sending" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Préféré pour l'envoi</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_preferred_receipt" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Préféré pour le retrait</span>
                        </label>
                    </div>

                    <x-button type="submit" class="w-full mt-4">Enregistrer</x-button>
                </form>
            </x-card>
        </div>

        <!-- List Section -->
        <div class="lg:col-span-2">
            <x-card title="Mes moyens enregistrés">
                <div class="space-y-4">
                    @forelse($userMethods as $userMethod)
                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl hover:bg-gray-50 transition">
                            <div class="flex items-center">
                                <x-method-badge :method="$userMethod->method" />
                                <div class="ml-4 space-x-2">
                                    @php
                                        $type = $userMethod->type ?? 'both';
                                    @endphp
                                    @if(in_array($type, ['send', 'both']))
                                        <span class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded font-bold uppercase">Envoi</span>
                                    @endif
                                    @if(in_array($type, ['receipt', 'both']))
                                        <span class="text-[10px] bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded font-bold uppercase">Retrait</span>
                                    @endif
                                    @if($userMethod->method->country_code)
                                        <span class="text-xs text-gray-400">({{ $userMethod->method->country_code }})</span>
                                    @endif
                                </div>
                            </div>
                            <form action="{{ route('settings.destroy', $userMethod) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-600 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            Aucun moyen de paiement enregistré.
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection