@extends('layouts.app')

@section('title', 'Nouvelle opération')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('operations.index') }}" class="text-gray-500 hover:text-gray-700">←</a>
        <h1 class="text-2xl font-bold text-gray-900">➕ Nouvelle opération</h1>
    </div>

    @if($profile && $profile->virtualSubaccounts)
            <div class="grid grid-cols-4 gap-3 mb-4">
                @foreach($profile->virtualSubaccounts as $sub)
                    <div class="bg-blue-50 rounded-xl p-2 text-center border border-blue-200">
                        <p class="text-xs text-blue-700">{{ $sub->reseau }}</p>
                        <p class="text-sm font-bold text-blue-900">{{ number_format($sub->solde, 0, ',', ' ') }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    {{-- Affichage des soldes des caisses --}}
    @if($profile)
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                <p class="text-sm text-emerald-700">💰 Caisse physique</p>
                <p class="text-xl font-bold text-emerald-900">{{ number_format($profile->caisse_physique, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <p class="text-sm text-blue-700">📱 Caisse virtuelle</p>
                <p class="text-xl font-bold text-blue-900">{{ number_format($profile->caisse_virtuelle, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow p-6">
        <form action="{{ route('operations.store') }}" method="POST">
            @csrf

            <div class="space-y-4">
                {{-- Type --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Type d'opération</label>
                    <select name="type_operation_id" class="w-full mt-1 rounded-xl border-gray-300" required>
                        <option value="">Sélectionner...</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ old('type_operation_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('type_operation_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Réseau --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Réseau</label>
                    <select name="reseau" class="w-full mt-1 rounded-xl border-gray-300" required>
                        <option value="">Sélectionner...</option>
                        @foreach($reseaux as $reseau)
                            <option value="{{ $reseau }}" {{ old('reseau') == $reseau ? 'selected' : '' }}>
                                {{ $reseau }}
                            </option>
                        @endforeach
                    </select>
                    @error('reseau')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Direction --}}                
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Direction(mouvement de la caisse physique)</label>
                        <select name="direction" class="w-full mt-1 rounded-xl border-gray-300" required>
                            <option value="">Sélectionner...</option>
                            <option value="entrant" {{ old('direction') == 'entrant' ? 'selected' : '' }}>📥 Entrant (je reçois de l'argent liquide)</option>
                            <option value="sortant" {{ old('direction') == 'sortant' ? 'selected' : '' }}>📤 Sortant (je donne de l'argent liquide)</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-400">
                            💡 <strong>Entrant</strong> = le client dépose de l'argent (vous recevez).<br>
                            💡 <strong>Sortant</strong> = le client retire de l'argent (vous donnez).
                        </p>
                        @error('direction')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                {{-- Montant --}}
               {{-- Montant --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Montant (FCFA)</label>
                    <input type="text" name="montant" value="{{ old('montant', 1000) }}" 
                        inputmode="numeric" pattern="[0-9]*" 
                        class="w-full mt-1 rounded-xl border-gray-300" required>
                    <p class="mt-1 text-xs text-gray-400">Saisissez un nombre entier, sans espaces ni séparateurs (ex: 2000).</p>
                    @error('montant')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Téléphone client --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Téléphone client</label>
                    <input type="text" name="telephone_client" value="{{ old('telephone_client') }}" placeholder="Ex: 90123456" class="w-full mt-1 rounded-xl border-gray-300">
                    @error('telephone_client')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

               <button type="submit" 
                        id="submitBtn"
                        onclick="this.disabled=true; this.innerText='Enregistrement...'; this.form.submit();"
                        class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition disabled:opacity-50">
                    ✅ Enregistrer l'opération
                </button>
            </div>
        </form>
    </div>
</div>
@endsection