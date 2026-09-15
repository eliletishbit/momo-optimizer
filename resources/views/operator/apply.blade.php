@extends('layouts.app')

@section('title', 'Devenir opérateur')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">📱 Devenir opérateur Mobile Money</h1>
        <p class="text-gray-600 mt-2">Gérez vos opérations en temps réel et générez des bilans journaliers</p>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if(auth()->user()->isOperator())
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl mb-4">
                ✅ Vous êtes déjà opérateur ! 
                <a href="{{ route('operations.index') }}" class="font-semibold underline">Accéder à votre espace</a>
            </div>
        @else
            <form action="{{ route('operator.apply.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nom de l'entreprise</label>
                        <input type="text" name="business_name" value="{{ old('business_name') }}" 
                               class="w-full mt-1 rounded-xl border-gray-300" required>
                        @error('business_name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" 
                               class="w-full mt-1 rounded-xl border-gray-300" required>
                        @error('phone')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Adresse</label>
                        <input type="text" name="address" value="{{ old('address') }}" 
                               class="w-full mt-1 rounded-xl border-gray-300">
                        @error('address')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Ville</label>
                        <input type="text" name="city" value="{{ old('city') }}" 
                               class="w-full mt-1 rounded-xl border-gray-300">
                        @error('city')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-blue-50 rounded-xl p-4 text-sm text-blue-700">
                        <p class="font-semibold">💡 En devenant opérateur, vous pourrez :</p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            <li>Enregistrer vos opérations en temps réel</li>
                            <li>Gérer les retraits, envois, recharges</li>
                            <li>Générer des bilans journaliers</li>
                            <li>Exporter vos données</li>
                        </ul>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition">
                        🚀 Devenir opérateur
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection