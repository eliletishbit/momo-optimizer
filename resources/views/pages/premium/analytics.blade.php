@extends('layouts.app')

@section('title', 'Tableau de Bord Décisionnel - MomoOpti Pro')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- En-tête --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wider mb-2">
                <span>💎 Espace Pro & Décisionnel</span>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900">Tableau de Bord Décisionnel</h1>
            <p class="text-sm text-gray-500 mt-1">
                Visualisez vos flux, analysez la performance de vos réseaux et maximisez votre rentabilité.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('pro.bilan') }}" 
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Accéder au Bilan Mensuel</span>
            </a>

            <a href="{{ route('calculator') }}" 
               class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm">
                <span>Calculateur →</span>
            </a>
        </div>
    </div>

    {{-- Synthèse KPI Principaux --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Volume Total Analysé</p>
            <p class="text-2xl font-black text-gray-900 mt-2">{{ number_format($totalVolume, 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-gray-500 mt-1">{{ $totalOptimizations }} optimisation(s) effectuée(s)</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-emerald-100 bg-gradient-to-br from-white to-emerald-50/40">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Économies Cumulées</p>
            <p class="text-2xl font-black text-emerald-600 mt-2">+ {{ number_format($totalSavings, 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-emerald-700 mt-1">Gains conservés grâce à l'optimisation</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-indigo-100 bg-gradient-to-br from-white to-indigo-50/40">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Taux d'Économie Moyen</p>
            <p class="text-2xl font-black text-indigo-900 mt-2">{{ $avgSavingsRate }}%</p>
            <p class="text-xs text-indigo-700 mt-1">Réduction moyenne des frais de transfert</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut du Compte</p>
            <div class="mt-2 flex items-center gap-2">
                @if(Auth::user()->is_admin)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 uppercase">
                        👑 Administrateur
                    </span>
                @elseif(Auth::user()->subscription === 'business')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 uppercase">
                        Actif Business
                    </span>
                @elseif(Auth::user()->subscription === 'pro')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 uppercase">
                        Actif Pro
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 uppercase">
                        {{ ucfirst(Auth::user()->subscription ?? 'Free') }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-500 mt-2">
                @if(Auth::user()->is_admin)
                    Accès complet administrateur
                @elseif(in_array(Auth::user()->subscription, ['pro', 'business']))
                    Accès illimité aux rapports avancés
                @else
                    Accès restreint
                @endif
            </p>
        </div>
    </div>

    {{-- Si profil opérateur actif : métriques de caisse en temps réel --}}
    @if($isOperator && $operatorStats)
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl mb-10">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-6 mb-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Point de Vente & Agence Mobile Money</span>
                    <h2 class="text-2xl font-black mt-1">État des Caisses en Temps Réel</h2>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('operations.index') }}" class="bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition">
                        Gérer les opérations
                    </a>
                    <a href="{{ route('operations.bilan') }}" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition">
                        Bilan journalier caisse
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                    <p class="text-xs text-slate-400 font-semibold uppercase">Caisse Physique (Espèces)</p>
                    <p class="text-2xl font-extrabold text-white mt-2">{{ number_format($operatorStats['caisse_physique'], 0, ',', ' ') }} FCFA</p>
                    <p class="text-xs text-emerald-400 mt-1">Liquidités disponibles au guichet</p>
                </div>

                <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                    <p class="text-xs text-slate-400 font-semibold uppercase">Caisse Virtuelle (Réseaux)</p>
                    <p class="text-2xl font-extrabold text-white mt-2">{{ number_format($operatorStats['caisse_virtuelle'], 0, ',', ' ') }} FCFA</p>
                    <p class="text-xs text-blue-400 mt-1">Solde cumulé sur vos puces MoMo</p>
                </div>

                <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                    <p class="text-xs text-slate-400 font-semibold uppercase">Actif Total de l'Agence</p>
                    <p class="text-2xl font-extrabold text-emerald-300 mt-2">{{ number_format($operatorStats['total_caisses'], 0, ',', ' ') }} FCFA</p>
                    <p class="text-xs text-slate-400 mt-1">Physique + Virtuel synchronisés</p>
                </div>
            </div>
        </div>
    @else
        {{-- Pour un utilisateur Pro non-opérateur (Entreprise / Particulier) : Carte Stratégique de Décision --}}
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl mb-10">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-5 mb-5">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Arbitrage Financier & Stratégie Trésorerie</span>
                    <h2 class="text-2xl font-black mt-1">Performance Globale d'Optimisation</h2>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('pro.bilan') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition">
                        Bilan d'Aide à la Décision →
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                    <p class="text-xs text-slate-400 font-semibold uppercase">Économies Conservées</p>
                    <p class="text-2xl font-extrabold text-emerald-400 mt-2">+ {{ number_format($totalSavings, 0, ',', ' ') }} FCFA</p>
                    <p class="text-xs text-slate-400 mt-1">Directement préservés dans votre trésorerie</p>
                </div>

                <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                    <p class="text-xs text-slate-400 font-semibold uppercase">Volume Total Traité</p>
                    <p class="text-2xl font-extrabold text-white mt-2">{{ number_format($totalVolume, 0, ',', ' ') }} FCFA</p>
                    <p class="text-xs text-indigo-300 mt-1">Analysé et comparé sur tous les réseaux</p>
                </div>

                <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                    <p class="text-xs text-slate-400 font-semibold uppercase">Rentabilité de l'Abonnement</p>
                    <p class="text-2xl font-extrabold text-amber-300 mt-2">
                        {{ $totalSavings >= 5000 ? 'Amorti à ' . round(($totalSavings / 5000) * 100) . '%' : 'En cours d\'amortissement' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Gains nets générés vs coût d'abonnement</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Graphiques et Répartition --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        {{-- Évolution mensuelle (Barres interactives) --}}
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Évolution de l'Activité (6 derniers mois)</h3>
                    <p class="text-xs text-gray-500">Volume traité et économies générées au fil du temps</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-600"></span> Volume</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Économie</span>
                </div>
            </div>

            <div class="grid grid-cols-6 gap-3 items-end h-64 pt-8 pb-2 border-b border-gray-100">
                @php
                    $maxVol = max(array_merge([100000], array_column($monthlyTrends, 'volume')));
                @endphp
                @foreach($monthlyTrends as $trend)
                    @php
                        $volHeight = max(12, min(100, round(($trend['volume'] / $maxVol) * 100)));
                        $savHeight = max(8, min(100, round(($trend['savings'] / ($trend['volume'] ?: 1)) * 300)));
                    @endphp
                    <div class="flex flex-col items-center gap-2 h-full justify-end group relative">
                        {{-- Tooltip au survol --}}
                        <div class="opacity-0 group-hover:opacity-100 transition absolute -top-12 z-20 bg-gray-900 text-white text-[10px] rounded-lg p-2 pointer-events-none whitespace-nowrap shadow-lg">
                            <div>Vol: {{ number_format($trend['volume'], 0, ',', ' ') }} F</div>
                            <div class="text-emerald-300">Éco: +{{ number_format($trend['savings'], 0, ',', ' ') }} F</div>
                        </div>

                        <div class="w-full flex items-end justify-center gap-1.5 h-full">
                            <div class="w-3 sm:w-4 bg-indigo-600 rounded-t-md transition-all duration-300 group-hover:bg-indigo-500" style="height: {{ $volHeight }}%;"></div>
                            <div class="w-3 sm:w-4 bg-emerald-500 rounded-t-md transition-all duration-300 group-hover:bg-emerald-400" style="height: {{ $savHeight }}%;"></div>
                        </div>
                        <span class="text-[11px] font-semibold text-gray-500">{{ $trend['month'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Répartition par Réseau --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Répartition par Réseau</h3>
                <p class="text-xs text-gray-500 mb-6">Moyens et opérateurs configurés</p>

                <div class="space-y-4">
                    @foreach($networkDistribution as $item)
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                <span class="text-gray-700">{{ $item['name'] }}</span>
                                <span class="text-gray-900 font-bold">{{ $item['percentage'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="{{ $item['class'] }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $item['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 mt-6">
                <a href="{{ route('settings') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center justify-between">
                    <span>Gérer mes moyens de paiement</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Dernières Optimisations --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Dernières Optimisations Effectuées</h3>
                <p class="text-xs text-gray-500">Historique récent des simulations</p>
            </div>
            <a href="{{ route('history') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                Voir tout l'historique →
            </a>
        </div>

        @if($recentOptimizations->isEmpty())
            <div class="p-8 text-center text-gray-400 text-sm">
                Aucune optimisation récente.
                <a href="{{ route('calculator') }}" class="text-indigo-600 font-semibold underline ml-1">Lancer un calcul</a>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($recentOptimizations as $opt)
                    <div class="px-6 sm:px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50/70 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ $opt->type === 'sending' ? '📤' : '📥' }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $opt->selectedMethod ? $opt->selectedMethod->name : 'Optimisation' }} — {{ number_format($opt->amount, 0, ',', ' ') }} FCFA
                                </p>
                                <p class="text-xs text-gray-400">{{ $opt->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                Économie : + {{ number_format($opt->savings, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection