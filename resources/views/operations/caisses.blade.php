@extends('layouts.app')

@section('title', 'Gestion des caisses')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('operations.index') }}" class="text-gray-500 hover:text-gray-700">←</a>
        <h1 class="text-2xl font-bold text-gray-900">💰 Gestion des caisses</h1>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        {{-- Soldes actuels --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                <p class="text-sm text-emerald-700">💰 Caisse physique</p>
                <p class="text-2xl font-bold text-emerald-900">{{ number_format($profile->caisse_physique, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <p class="text-sm text-blue-700">📱 Caisse virtuelle</p>
                <p class="text-2xl font-bold text-blue-900">{{ number_format($profile->caisse_virtuelle, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-200">
                <p class="text-sm text-indigo-700">📊 Total</p>
                <p class="text-2xl font-bold text-indigo-900">{{ number_format($profile->caisse_physique + $profile->caisse_virtuelle, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>

        <hr class="my-6">

        <form action="{{ route('operations.caisses.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                {{-- Caisse physique --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Caisse physique (espèces en main)</label>
                    <input type="number" name="caisse_physique" value="{{ old('caisse_physique', $profile->caisse_physique) }}" 
                           class="w-full mt-1 rounded-xl border-gray-300" step="100" min="0" required>
                    @error('caisse_physique')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sous-comptes virtuels par réseau --}}
            <div class="border-t border-gray-200 pt-4 mt-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4">📱 Sous-comptes virtuels par réseau</h3>
                <p class="text-sm text-gray-500 mb-4">Ajustez le solde de chaque sous-compte virtuel si nécessaire.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($profile->virtualSubaccounts as $sub)
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">{{ $sub->reseau }}</label>
                            <input type="number" name="subaccounts[{{ $sub->id }}]" 
                                value="{{ old('subaccounts.'.$sub->id, $sub->solde) }}"
                                class="w-full mt-1 rounded-xl border-gray-300" step="100" min="0">
                        </div>
                    @endforeach
                </div>
            </div>

                {{-- Caisse virtuelle globale (calculée automatiquement) --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <p class="text-sm text-gray-600">📱 Caisse virtuelle globale (calculée automatiquement)</p>
                    <p class="text-xl font-bold text-blue-900">{{ number_format($profile->caisse_virtuelle, 0, ',', ' ') }} FCFA</p>
                    <p class="text-xs text-gray-400">Cette valeur est la somme de tous les sous-comptes virtuels ci-dessus.</p>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition">
                    💾 Mettre à jour les caisses
                </button>
            </div>
        </form>

        <div class="mt-6 bg-gray-50 rounded-xl p-4 text-sm text-gray-600">
            <p class="font-semibold">ℹ️ Information</p>
            <p class="mt-1">La caisse physique est automatiquement mise à jour à chaque opération (entrant ➕, sortant ➖).</p>
            <p class="mt-1">Les sous-comptes virtuels sont automatiquement mis à jour selon le réseau de l'opération.</p>
            <p class="mt-1">Utilisez ce formulaire pour corriger manuellement les soldes si nécessaire.</p>
        </div>
    </div>
</div>
@endsection