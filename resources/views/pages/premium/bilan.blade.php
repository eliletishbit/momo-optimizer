@extends('layouts.app')

@section('title', 'Bilan d\'Aide à la Décision Pro - MomoOpti')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- En-tête de rapport (Visible à l'écran) --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 print:hidden">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wider mb-2">
                <span>⭐ Espace Pro & Business — Arbitrage Financier</span>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900">Bilan Mensuel d'Aide à la Décision</h1>
            <p class="text-sm text-gray-500 mt-1">
                Analyse d'impact financier, optimisation des frais de transfert et audit des gains du 
                <span class="font-semibold text-gray-700">{{ $monthDate->startOfMonth()->format('d/m/Y') }}</span> au 
                <span class="font-semibold text-gray-700">{{ $monthDate->endOfMonth()->format('d/m/Y') }}</span>.
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

            <button type="button" 
                    onclick="window.print()" 
                    class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimer / PDF</span>
            </button>

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

    {{-- En-tête exclusif pour l'impression / PDF --}}
    <div class="hidden print:block mb-8 border-b border-gray-300 pb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-black text-indigo-700 tracking-tight">MomoOpti</h1>
                <p class="text-xs uppercase tracking-widest text-gray-500 font-semibold">Plateforme d'Optimisation des Coûts Mobile Money</p>
                <h2 class="text-xl font-bold text-gray-900 mt-3">Bilan Mensuel d'Aide à la Décision Financière</h2>
                <p class="text-sm text-gray-600">Période auditée : {{ $monthDate->translatedFormat('F Y') }}</p>
            </div>
            <div class="text-right text-xs text-gray-500">
                <p><strong class="text-gray-800">Bénéficiaire :</strong> {{ Auth::user()->name }}</p>
                <p><strong class="text-gray-800">Email :</strong> {{ Auth::user()->email }}</p>
                <p><strong class="text-gray-800">Forfait :</strong> Abonnement Pro / Entreprise</p>
                <p><strong class="text-gray-800">Date d'édition :</strong> {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- Synthèse Exécutive KPI --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Volume Total Optimisé</p>
            <p class="text-2xl font-black text-gray-900 mt-2">{{ number_format($optVolume, 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-gray-500 mt-1">{{ $optCount }} transaction(s) auditée(s)</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-emerald-200 bg-gradient-to-br from-white to-emerald-50/40">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Économies Nettes Réalisées</p>
            <p class="text-2xl font-black text-emerald-600 mt-2">+ {{ number_format($optSavings, 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-emerald-700 mt-1">Gains conservés grâce à l'arbitrage</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-indigo-200 bg-gradient-to-br from-white to-indigo-50/40">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Frais Optimisés Payés</p>
            <p class="text-2xl font-black text-indigo-950 mt-2">{{ number_format($optFees, 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-indigo-700 mt-1">Total des coûts de transaction réels</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-violet-200 bg-gradient-to-br from-white to-violet-50/40">
            <p class="text-xs font-semibold text-violet-700 uppercase tracking-wider">Taux d'Économie Global</p>
            <p class="text-2xl font-black text-violet-900 mt-2">{{ $avgSavingsRate }}%</p>
            <p class="text-xs text-violet-700 mt-1">Réduction moyenne des frais de transfert</p>
        </div>
    </div>

    {{-- Synthèse d'Arbitrage et Recommandations Stratégiques --}}
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl mb-10 print:bg-white print:text-gray-900 print:border print:border-gray-300 print:shadow-none">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/10 print:border-gray-200 pb-5 mb-5">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 print:text-indigo-600">Aide à la Décision Financière</span>
                <h2 class="text-xl font-black mt-1">Synthèse & Perspectives d'Arbitrage</h2>
            </div>
            <div class="text-xs bg-white/10 print:bg-gray-100 print:text-gray-800 px-3 py-1.5 rounded-xl font-medium">
                Période : {{ $monthDate->translatedFormat('F Y') }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            <div class="bg-white/5 print:bg-gray-50 print:border print:border-gray-200 rounded-2xl p-4">
                <p class="text-xs font-bold uppercase text-slate-400 print:text-gray-500">Rentabilité Immédiate</p>
                <p class="mt-2 text-slate-200 print:text-gray-700 leading-relaxed">
                    Sur un volume total de <strong class="text-white print:text-gray-900">{{ number_format($optVolume, 0, ',', ' ') }} FCFA</strong>, votre structure a préservé <strong class="text-emerald-400 print:text-emerald-700">{{ number_format($optSavings, 0, ',', ' ') }} FCFA</strong> de trésorerie qui auraient été prélevés par des grilles tarifaires non optimisées.
                </p>
            </div>

            <div class="bg-white/5 print:bg-gray-50 print:border print:border-gray-200 rounded-2xl p-4">
                <p class="text-xs font-bold uppercase text-slate-400 print:text-gray-500">Projection Annuelle</p>
                <p class="mt-2 text-slate-200 print:text-gray-700 leading-relaxed">
                    À ce rythme de transaction, l'économie nette projetée sur 12 mois s'élève à environ <strong class="text-emerald-400 print:text-emerald-700">{{ number_format($optSavings * 12, 0, ',', ' ') }} FCFA</strong>, rentabilisant très largement votre abonnement professionnel.
                </p>
            </div>

            <div class="bg-white/5 print:bg-gray-50 print:border print:border-gray-200 rounded-2xl p-4">
                <p class="text-xs font-bold uppercase text-slate-400 print:text-gray-500">Recommandation Opérationnelle</p>
                <p class="mt-2 text-slate-200 print:text-gray-700 leading-relaxed">
                    Pour les montants supérieurs à 50 000 FCFA, privilégiez le découpage intelligent ou le réseau aux paliers fixes les plus bas pour réduire encore votre coût marginal de transaction.
                </p>
            </div>
        </div>
    </div>

    {{-- Matrice de Performance par Réseau Mobile Money --}}
    @if(!empty($networkBreakdown))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-10 print:shadow-none">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900">📈 Répartition et Performance par Opérateur Mobile Money</h2>
                    <p class="text-xs text-gray-500">Analyse comparée des volumes traités et des économies générées par canal</p>
                </div>
                <span class="text-xs font-semibold text-gray-600 bg-gray-200/60 px-3 py-1 rounded-full">
                    {{ count($networkBreakdown) }} réseau(x) sollicité(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-3 text-left">Réseau / Opérateur</th>
                            <th class="px-6 py-3 text-center">Transactions</th>
                            <th class="px-6 py-3 text-right">Volume Traité</th>
                            <th class="px-6 py-3 text-right">Frais Payés</th>
                            <th class="px-6 py-3 text-right">Économie Générée</th>
                            <th class="px-6 py-3 text-right">Part du Volume</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($networkBreakdown as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3.5 font-bold text-gray-900">{{ $row['name'] }}</td>
                                <td class="px-6 py-3.5 text-center text-gray-600">{{ $row['count'] }}</td>
                                <td class="px-6 py-3.5 text-right font-semibold text-gray-900">{{ number_format($row['volume'], 0, ',', ' ') }} FCFA</td>
                                <td class="px-6 py-3.5 text-right text-gray-600">{{ number_format($row['fees'], 0, ',', ' ') }} FCFA</td>
                                <td class="px-6 py-3.5 text-right font-bold text-emerald-600">+ {{ number_format($row['savings'], 0, ',', ' ') }} FCFA</td>
                                <td class="px-6 py-3.5 text-right">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                        {{ $row['percentage'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Journal d'Audit Détaillé des Optimisations du Mois --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden print:shadow-none">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">📋 Registre d'Audit des Transactions Optimisées</h2>
                <p class="text-xs text-gray-500">Historique complet et certifié des arbitrages de frais sur la période</p>
            </div>
            <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full">
                {{ $optimizations->count() }} transaction(s)
            </span>
        </div>

        @if($optimizations->isEmpty())
            <div class="p-12 text-center text-gray-400 text-sm">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="font-medium text-gray-600">Aucune optimisation enregistrée pour le mois de {{ $monthDate->translatedFormat('F Y') }}.</p>
                <p class="text-xs text-gray-400 mt-1">Utilisez le calculateur pour comparer et auditer vos prochaines transactions.</p>
                <a href="{{ route('calculator') }}" class="mt-4 inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700 transition">
                    Lancer un calcul d'optimisation →
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-3 text-left">Date & Heure</th>
                            <th class="px-6 py-3 text-left">Opération</th>
                            <th class="px-6 py-3 text-left">Canal / Moyen Retenu</th>
                            <th class="px-6 py-3 text-right">Montant</th>
                            <th class="px-6 py-3 text-right">Frais Payés</th>
                            <th class="px-6 py-3 text-right">Économie Nette</th>
                            <th class="px-6 py-3 text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($optimizations as $opt)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3.5 text-gray-500 font-mono text-xs">
                                    {{ $opt->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-3.5 font-medium">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $opt->type === 'sending' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $opt->type === 'sending' ? '📤 Envoi' : '📥 Retrait' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 font-semibold text-gray-900">
                                    {{ $opt->selectedMethod ? $opt->selectedMethod->name : 'Optimisation combinée' }}
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-gray-900">
                                    {{ number_format($opt->amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-3.5 text-right text-gray-700 font-medium">
                                    {{ number_format($opt->total_fee, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-emerald-600">
                                    + {{ number_format($opt->savings, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ✓ Optimisé
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Pied de page certifié (Visible uniquement à l'impression / PDF) --}}
    <div class="hidden print:block mt-12 pt-6 border-t border-gray-300 text-center text-xs text-gray-500">
        <p>Document généré automatiquement par la plateforme <strong>MomoOpti</strong> — Tous droits réservés.</p>
        <p class="mt-1">Ce bilan constitue une synthèse d'aide à la décision financière et un audit d'arbitrage de frais de transfert.</p>
    </div>
</div>

<style>
@media print {
    nav, footer, .sticky, form, button, a[href*="analytics"], a[href*="csv"] {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 11pt;
    }
    .shadow-sm, .shadow-xl {
        box-shadow: none !important;
    }
    .rounded-2xl, .rounded-3xl {
        border-radius: 6px !important;
    }
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
}
</style>
@endsection