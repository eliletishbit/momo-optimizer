@extends('layouts.app')

@section('title', 'Bilan Mensuel Pro - MomoOpti')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- En-tête --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wider mb-2">
                <span>⭐ Espace Pro & Business</span>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900">Bilan Financier Mensuel</h1>
            <p class="text-sm text-gray-500 mt-1">
                Période du {{ $monthDate->startOfMonth()->format('d/m/Y') }} au {{ $monthDate->endOfMonth()->format('d/m/Y') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('pro.bilan') }}" class="flex items-center gap-2">
                <input type="month" 
                       name="month" 
                       value="{{ $selectedMonth }}" 
                       class="rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-sm">
                    Filtrer
                </button>
            </form>

            <a href="{{ route('pro.bilan', ['month' => $selectedMonth, 'export' => 'csv']) }}" 
               class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Exporter CSV</span>
            </a>

            <a href="{{ route('pro.analytics') }}" 
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold transition">
                <span>Dashboard Décisionnel →</span>
            </a>
        </div>
    </div>

    {{-- Cartes de synthèse --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Volume Analysé</p>
            <p class="text-2xl font-black text-gray-900 mt-2">{{ number_format($optVolume, 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-gray-500 mt-1">{{ $optimizations->count() }} transaction(s) analysée(s)</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-emerald-100 bg-gradient-to-br from-white to-emerald-50/40">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Économies Réalisées</p>
            <p class="text-2xl font-black text-emerald-600 mt-2">+ {{ number_format($optSavings, 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-emerald-700 mt-1">Frais optimisés évités</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-indigo-100 bg-gradient-to-br from-white to-indigo-50/40">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Frais Payés Estimés</p>
            <p class="text-2xl font-black text-indigo-900 mt-2">{{ number_format($optFees, 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-indigo-700 mt-1">Tarification la plus basse</p>
        </div>

        @if($operatorProfile)
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-amber-100 bg-gradient-to-br from-white to-amber-50/40">
                <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Flux Net Opérateur</p>
                <p class="text-2xl font-black text-amber-900 mt-2">{{ number_format($opNet, 0, ',', ' ') }} FCFA</p>
                <p class="text-xs text-amber-700 mt-1">{{ $operatorOperations->count() }} opération(s) caisse</p>
            </div>
        @else
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Taux d'Économie Moyen</p>
                <p class="text-2xl font-black text-gray-900 mt-2">
                    {{ $optVolume > 0 ? round(($optSavings / $optVolume) * 100, 1) : 0 }}%
                </p>
                <p class="text-xs text-gray-500 mt-1">Sur l'ensemble du mois</p>
            </div>
        @endif
    </div>

    {{-- Section Opérations Opérateur (si applicable) --}}
    @if($operatorProfile && $operatorOperations->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-10">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">📱 Opérations de l'Agence (Point de vente)</h2>
                    <p class="text-xs text-gray-500">Flux d'espèces et unités enregistrés durant le mois</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold">
                    <span class="text-emerald-600">📥 Entrants : {{ number_format($opTotalEntrant, 0, ',', ' ') }} FCFA</span>
                    <span class="text-orange-600">📤 Sortants : {{ number_format($opTotalSortant, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Réseau</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Sens</th>
                            <th class="px-6 py-3 text-left">Client</th>
                            <th class="px-6 py-3 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($operatorOperations->take(30) as $op)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 text-gray-500">{{ $op->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-3 font-semibold text-gray-900">{{ $op->reseau }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $op->typeoperateur ? $op->typeoperateur->nom : 'Opération' }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $op->direction === 'entrant' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' }}">
                                        {{ $op->direction === 'entrant' ? '📥 Entrant' : '📤 Sortant' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-500 font-mono text-xs">{{ $op->telephone_client ?? '-' }}</td>
                                <td class="px-6 py-3 text-right font-bold text-gray-900">{{ number_format($op->montant, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Section Détail des Optimisations --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">📊 Détail des Optimisations & Économies</h2>
                <p class="text-xs text-gray-500">Toutes les comparaisons et simulations effectuées sur la période</p>
            </div>
            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">
                {{ $optimizations->count() }} simulation(s)
            </span>
        </div>

        @if($optimizations->isEmpty())
            <div class="p-12 text-center text-gray-400 text-sm">
                <p>Aucune transaction enregistrée pour ce mois.</p>
                <a href="{{ route('calculator') }}" class="mt-3 inline-block text-indigo-600 font-semibold hover:underline">
                    Effectuer un calcul d'optimisation →
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Moyen Retenu</th>
                            <th class="px-6 py-3 text-right">Montant</th>
                            <th class="px-6 py-3 text-right">Frais Payés</th>
                            <th class="px-6 py-3 text-right">Économie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($optimizations as $opt)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3.5 text-gray-500">{{ $opt->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-3.5 font-medium">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $opt->type === 'sending' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $opt->type === 'sending' ? 'Envoi' : 'Retrait' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 font-semibold text-gray-800">
                                    {{ $opt->selectedMethod ? $opt->selectedMethod->name : 'Optimisation combinée' }}
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-gray-900">
                                    {{ number_format($opt->amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-3.5 text-right text-gray-600">
                                    {{ number_format($opt->total_fee, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-emerald-600">
                                    + {{ number_format($opt->savings, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection