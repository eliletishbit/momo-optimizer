@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wider mb-4">
            💎 Fonctionnalité Premium
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Statistiques Avancées</h1>
        <p class="mt-4 text-gray-500">Analysez vos habitudes de transaction et visualisez vos économies réelles sur la durée.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <x-card class="lg:col-span-2" title="Évolution de vos économies">
            <div class="h-64 bg-gray-50 rounded-xl flex items-center justify-center border-2 border-dashed border-gray-200">
                <span class="text-gray-400 text-sm">Graphique des économies mensuelles (Interactive Chart)</span>
            </div>
        </x-card>

        <div class="space-y-6">
            <x-card title="Répartition par réseau">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600">MTN Benin</span>
                        <span class="text-sm font-bold text-gray-900">45%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: 45%"></div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600">Moov Africa</span>
                        <span class="text-sm font-bold text-gray-900">35%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 35%"></div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600">Celtiis</span>
                        <span class="text-sm font-bold text-gray-900">20%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: 20%"></div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <x-card title="Top 3 des économies">
            <ul class="space-y-4">
                <li class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                    <div>
                        <p class="text-sm font-bold text-emerald-900">Simulation du 12/08</p>
                        <p class="text-xs text-emerald-700">Retrait de 250 000 FCFA</p>
                    </div>
                    <span class="text-lg font-bold text-emerald-600">+ 1 200 FCFA</span>
                </li>
                <li class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl">
                    <div>
                        <p class="text-sm font-bold text-gray-900">Simulation du 05/08</p>
                        <p class="text-xs text-gray-500">Envoi de 100 000 FCFA</p>
                    </div>
                    <span class="text-lg font-bold text-gray-600">+ 800 FCFA</span>
                </li>
            </ul>
        </x-card>
    </div>
</div>
@endsection
