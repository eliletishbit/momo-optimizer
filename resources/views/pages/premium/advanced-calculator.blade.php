@extends('layouts.app')

@section('title', 'Calculateur Avancé')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wider mb-4">
            💎 Fonctionnalité Premium
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Calculateur Multi-Réseaux</h1>
        <p class="mt-4 text-gray-500">Optimisez vos transactions complexes en combinant plusieurs réseaux pour un maximum d'économies.</p>
    </div>

    <div class="max-w-4xl mx-auto">
        <x-card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-2">Paramètres avancés</h3>
                    <x-form.input type="number" name="total_amount" label="Montant total à transférer" placeholder="Ex: 500 000" />
                    
                    <div class="space-y-3">
                        <p class="text-sm font-medium text-gray-700">Réseaux à inclure :</p>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" checked class="rounded text-indigo-600 focus:ring-indigo-500 mr-3">
                                <span class="text-sm font-bold">MTN</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" checked class="rounded text-indigo-600 focus:ring-indigo-500 mr-3">
                                <span class="text-sm font-bold">MOOV</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" checked class="rounded text-indigo-600 focus:ring-indigo-500 mr-3">
                                <span class="text-sm font-bold">CELTIIS</span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" checked class="rounded text-indigo-600 focus:ring-indigo-500 mr-3">
                                <span class="text-sm font-bold">WAVE</span>
                            </label>
                        </div>
                    </div>

                    <x-button class="w-full">Lancer l'analyse avancée</x-button>
                </div>

                <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100">
                    <h4 class="font-bold text-indigo-900 mb-4 italic">Le saviez-vous ?</h4>
                    <p class="text-sm text-indigo-700 leading-relaxed mb-4">
                        Pour un transfert de 500 000 FCFA, diviser le montant sur deux réseaux différents peut vous faire économiser jusqu'à <span class="font-bold">4 500 FCFA</span> de frais supplémentaires par rapport à un envoi unique.
                    </p>
                    <div class="flex items-center text-indigo-600 text-sm font-bold">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Analyse intelligente activée
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection
