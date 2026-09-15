@extends('layouts.app')

@section('title', 'Bilan journalier')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📈 Bilan journalier</h1>
            <p class="text-sm text-gray-500">Bilan des opérations du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('operations.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-300 transition">
                ← Retour
            </a>
            <a href="{{ route('operations.caisses.edit') }}" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">
                💰 Gérer les caisses
            </a>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}" class="rounded-xl border-gray-300">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition">
                    Voir
                </button>
            </form>
        </div>
    </div>

    {{-- Cartes des caisses --}}
    @if($profile)
       <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-emerald-500">
            <p class="text-sm text-gray-500">💰 Caisse physique</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($profile->caisse_physique ?? 0, 0, ',', ' ') }} FCFA</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">📱 Caisse virtuelle</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($profile->caisse_virtuelle ?? 0, 0, ',', ' ') }} FCFA</p>
        </div>
        {{-- ✅ TOTAL DES CAISSES --}}
        <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-indigo-500">
            <p class="text-sm text-gray-500">📊 Total des caisses</p>
            <p class="text-2xl font-bold text-indigo-900">
                {{ number_format(($profile->caisse_physique ?? 0) + ($profile->caisse_virtuelle ?? 0), 0, ',', ' ') }} FCFA
            </p>
        </div>
    </div>
    @endif

    {{-- Sous-comptes virtuels par réseau --}}
    @if($profile && $profile->virtualSubaccounts)
        <div class="bg-white rounded-2xl shadow p-6 mt-6 mb-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4">📱 Détail des sous-comptes virtuels</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($profile->virtualSubaccounts as $sub)
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                        <p class="text-sm text-blue-700">{{ $sub->reseau }}</p>
                        <p class="text-xl font-bold text-blue-900">{{ number_format($sub->solde, 0, ',', ' ') }} FCFA</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($totalOperations == 0)
        <div class="bg-white rounded-2xl shadow p-8 text-center text-gray-500">
            Aucune opération pour cette date.
        </div>
    @else
        {{-- Résumé global --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl shadow p-4 text-center">
                <p class="text-sm text-gray-500">Total opérations</p>
                <p class="text-2xl font-bold text-gray-900">{{ $grandTotals['count'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-4 text-center border-l-4 border-emerald-500">
                <p class="text-sm text-gray-500">📥 Entrant</p>
                <p class="text-2xl font-bold text-emerald-600">{{ number_format($grandTotals['total_entrant'], 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-4 text-center border-l-4 border-orange-500">
                <p class="text-sm text-gray-500">📤 Sortant</p>
                <p class="text-2xl font-bold text-orange-600">{{ number_format($grandTotals['total_sortant'], 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-4 text-center border-l-4 border-blue-500">
                <p class="text-sm text-gray-500">Net</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($grandTotals['net'], 0, ',', ' ') }} FCFA</p>
            </div>
        </div>

        {{-- Bilan détaillé par réseau et type --}}
        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">📋 Détail par réseau et type d'opération</h2>
            </div>

            <div class="p-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Réseau</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Type</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Nb</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Entrant</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Sortant</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Net</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $currentReseau = null;
                            $sousTotalCount = 0;
                            $sousTotalEntrant = 0;
                            $sousTotalSortant = 0;
                            $sousTotalNet = 0;
                            $sousTotalTotal = 0;
                        @endphp

                        @foreach($report as $item)
                            @php
                                $isNewReseau = ($currentReseau !== $item['reseau']);
                                $currentReseau = $item['reseau'];
                            @endphp

                            @if($isNewReseau && !$loop->first)
                                {{-- Sous-total du réseau précédent --}}
                                <tr class="bg-gray-50">
                                    <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-900">Sous-total {{ $currentReseau }}</td>
                                    <td class="px-4 py-3 text-sm text-center font-bold">{{ $sousTotalCount }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-emerald-700">{{ number_format($sousTotalEntrant, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-orange-700">{{ number_format($sousTotalSortant, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-blue-700">{{ number_format($sousTotalNet, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">{{ number_format($sousTotalTotal, 0, ',', ' ') }}</td>
                                </tr>
                                @php
                                    $sousTotalCount = 0;
                                    $sousTotalEntrant = 0;
                                    $sousTotalSortant = 0;
                                    $sousTotalNet = 0;
                                    $sousTotalTotal = 0;
                                @endphp
                            @endif

                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    @if($isNewReseau)
                                        {{ $item['reseau'] }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $item['type']->nom }}</td>
                                <td class="px-4 py-3 text-sm text-center">{{ $item['count'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-emerald-600">{{ number_format($item['total_entrant'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-orange-600">{{ number_format($item['total_sortant'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-blue-600">{{ number_format($item['net'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold">{{ number_format($item['montant_total'], 0, ',', ' ') }}</td>
                            </tr>

                            @php
                                $sousTotalCount += $item['count'];
                                $sousTotalEntrant += $item['total_entrant'];
                                $sousTotalSortant += $item['total_sortant'];
                                $sousTotalNet += $item['net'];
                                $sousTotalTotal += $item['montant_total'];
                            @endphp

                            @if($loop->last)
                                {{-- Dernier sous-total --}}
                                <tr class="bg-gray-50">
                                    <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-900">Sous-total {{ $item['reseau'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center font-bold">{{ $sousTotalCount }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-emerald-700">{{ number_format($sousTotalEntrant, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-orange-700">{{ number_format($sousTotalSortant, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-blue-700">{{ number_format($sousTotalNet, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">{{ number_format($sousTotalTotal, 0, ',', ' ') }}</td>
                                </tr>
                            @endif
                        @endforeach

                        {{-- TOTAL GÉNÉRAL --}}
                        <tr class="bg-indigo-50 border-t-2 border-indigo-200">
                            <td colspan="2" class="px-4 py-4 text-sm font-bold text-indigo-900">📊 TOTAL GÉNÉRAL</td>
                            <td class="px-4 py-4 text-sm text-center font-bold text-indigo-900">{{ $grandTotals['count'] }}</td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-emerald-700">{{ number_format($grandTotals['total_entrant'], 0, ',', ' ') }}</td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-orange-700">{{ number_format($grandTotals['total_sortant'], 0, ',', ' ') }}</td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-blue-700">{{ number_format($grandTotals['net'], 0, ',', ' ') }}</td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-indigo-900">{{ number_format($grandTotals['montant_total'], 0, ',', ' ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection