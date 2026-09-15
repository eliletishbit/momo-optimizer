@extends('layouts.app')

@section('title', 'Statistiques Globales')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Statistiques Globales</h1>
        <p class="text-sm text-gray-500">Analyse détaillée de l'utilisation de la plateforme.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <!-- Users by Country -->
        <x-card title="Utilisateurs par pays">
            <div class="space-y-4">
                @foreach($stats['users_by_country'] as $stat)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-700">{{ $stat->country_code }}</span>
                        <div class="flex-grow mx-4 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="bg-indigo-600 h-full" style="width: {{ ($stat->total / $stats['users_by_country']->sum('total')) * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $stat->total }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Subscriptions by Type -->
        <x-card title="Répartition des abonnements">
            <div class="space-y-4">
                @foreach($stats['subscriptions_by_type'] as $stat)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-700 capitalize">{{ $stat->subscription }}</span>
                        <div class="flex-grow mx-4 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full" style="width: {{ ($stat->total / $stats['subscriptions_by_type']->sum('total')) * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $stat->total }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    <!-- Optimizations Over Time -->
    <x-card title="Activité (Optimisations sur 30 jours)">
        <div class="h-64 bg-gray-50 rounded-xl flex items-center justify-center border-2 border-dashed border-gray-200">
            <span class="text-gray-400 text-sm">Graphique d'activité temporelle (Chart.js ou autre)</span>
        </div>
        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($stats['optimizations_over_time']->take(4) as $opt)
                <div class="text-center p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-500 font-bold uppercase">{{ $opt->date }}</p>
                    <p class="text-xl font-bold text-indigo-600">{{ $stat->total }}</p>
                </div>
            @endforeach
        </div>
    </x-card>
</div>
@endsection
