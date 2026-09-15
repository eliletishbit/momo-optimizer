@extends('layouts.app')

@section('title', 'Export des données')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wider mb-4">
            💎 Fonctionnalité Premium
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Exporter mes rapports</h1>
        <p class="mt-4 text-gray-500">Téléchargez l'historique de vos optimisations pour votre comptabilité personnelle ou professionnelle.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <x-card title="Rapport PDF">
            <p class="text-sm text-gray-500 mb-6">Idéal pour l'impression ou l'archivage visuel. Inclut des graphiques récapitulatifs.</p>
            <x-button class="w-full" variant="secondary">
                <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6l-4-4H9z"/></svg>
                Télécharger en PDF
            </x-button>
        </x-card>

        <x-card title="Données CSV / Excel">
            <p class="text-sm text-gray-500 mb-6">Idéal pour l'analyse de données ou l'importation dans un logiciel de gestion.</p>
            <x-button class="w-full" variant="secondary">
                <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2zM3 16a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z"/></svg>
                Télécharger en CSV
            </x-button>
        </x-card>
    </div>
</div>
@endsection
