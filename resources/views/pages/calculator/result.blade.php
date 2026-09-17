{{-- @extends('layouts.app')

@section('title', 'Résultat du calcul')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" x-data="{
    selected: null,
    search: '',
    page: 1
}">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-700">
                Optimisation
            </div>
            <h1 class="mt-4 text-3xl font-black text-gray-900">Résultats de calcul</h1>
            <p class="mt-2 text-sm text-gray-600">
                Montant analysé : <span class="font-semibold text-gray-900">{{ number_format((float) $amount, 0, ',', ' ') }} FCFA</span>
                @if ($country)
                    • Pays : <span class="font-semibold text-gray-900">{{ strtoupper($country) }}</span>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('calculator') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50">
                Nouveau calcul
            </a>
            @if ($can_export)
                <a href="{{ route('calculator.result', ['amount' => $amount, 'country' => $country, 'type' => $type, 'download' => 1]) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-emerald-700">
                    Exporter CSV
                </a>
            @endif
        </div>
    </div>

    @if ($message)
        <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
            {{ $message }}
        </div>
    @endif

    @if ($summary && ! empty($summary['options']))
        <div class="mb-8 grid gap-5 md:grid-cols-3">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Meilleur choix</p>
                <p class="mt-3 text-xl font-black text-emerald-900">{{ $summary['best'] }}</p>
                <p class="mt-2 text-sm text-emerald-700">Frais : {{ number_format((float) $summary['options'][0]['total_fee'], 0, ',', ' ') }} FCFA</p>
            </div>

            <div class="rounded-2xl border border-sky-100 bg-sky-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Montant net</p>
                <p class="mt-3 text-xl font-black text-sky-900">{{ number_format((float) $summary['options'][0]['net_amount'], 0, ',', ' ') }} FCFA</p>
                <p class="mt-2 text-sm text-sky-700">Après frais estimés</p>
            </div>

            <div class="rounded-2xl border border-violet-100 bg-violet-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-violet-700">Économie</p>
                <p class="mt-3 text-xl font-black text-violet-900">{{ number_format((float) $summary['savings'], 0, ',', ' ') }} FCFA</p>
                <p class="mt-2 text-sm text-violet-700">Soit {{ $summary['savings_percent'] }}% par rapport au pire choix</p>
            </div>
        </div>
    @endif

    @if ($best_option && $share_message)
        <div class="mb-8 rounded-2xl border border-indigo-200 bg-indigo-50 p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-700">Message de partage</p>
                    <p class="mt-2 text-sm text-indigo-900">{{ $share_message }}</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50" onclick="copyToClipboard('{{ addslashes($share_message) }}', this)">
                        <span>Copier</span>
                    </button>
                    <a href="https://wa.me/?text={{ urlencode($share_message) }}" target="_blank" rel="noopener" class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                        Envoyer via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if ($summary && ! empty($summary['options']))
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xl shadow-slate-100/50">
            <div class="mb-5 flex flex-col gap-3 border-b border-gray-200 pb-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Classement des meilleures options</h2>
                    <p class="text-sm text-gray-500">Trié du moins cher au plus cher.</p>
                </div>

                <div class="relative w-full max-w-xs">
                    <input type="search" x-model="search" placeholder="Rechercher une option" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 outline-none ring-0 transition focus:border-indigo-300 focus:bg-white">
                </div>
            </div>

            <div class="space-y-4">
                @foreach ($options as $index => $option)
                    @php
                        $rank = (($options->currentPage() - 1) * $options->perPage()) + $index + 1;
                    @endphp

                    <div x-show="search === '' || '{{ strtolower($option['label']) }}'.includes(search.toLowerCase())" class="rounded-2xl border border-gray-200 bg-slate-50 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-lg font-black text-indigo-600 shadow-sm">
                                    {{ $rank }}
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-gray-900">{{ $option['label'] }}</p>
                                    <p class="text-sm text-gray-500">Frais total : {{ number_format((float) $option['total_fee'], 0, ',', ' ') }} FCFA</p>
                                </div>
                            </div>

                            <div class="flex flex-col items-start gap-1 sm:flex-row sm:items-center sm:gap-4">
                                <div class="rounded-full bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700 shadow-sm">
                                    Net : {{ number_format((float) $option['net_amount'], 0, ',', ' ') }} FCFA
                                </div>
                                @if ($rank === 1)
                                    <span class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-white">Meilleur</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white">
                            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Réseau</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Montant</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Frais</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($option['details'] as $detail)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $detail['network'] }}</td>
                                            <td class="px-4 py-3">{{ number_format((float) $detail['amount'], 0, ',', ' ') }} FCFA</td>
                                            <td class="px-4 py-3 text-emerald-700 font-semibold">{{ number_format((float) $detail['fee'], 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($options->hasPages())
                <div class="mt-6 flex justify-center">
                    {{ $options->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="rounded-3xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-500">
            <p class="text-lg font-semibold text-gray-700">Aucune option disponible pour ce montant.</p>
            <p class="mt-2 text-sm">Essayez un autre montant ou ajoutez de nouveaux moyens de paiement dans vos paramètres.</p>
        </div>
    @endif
</div>
@endsection --}}
@extends('layouts.app')

@section('title', 'Résultat du calcul')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" x-data="{
    selected: null,
    search: '',
    page: 1
}">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-700">
                Optimisation
            </div>
            <h1 class="mt-4 text-3xl font-black text-gray-900">Résultats de calcul</h1>
            <p class="mt-2 text-sm text-gray-600">
                Montant analysé : <span class="font-semibold text-gray-900">{{ number_format((float) $amount, 0, ',', ' ') }} FCFA</span>
                @if ($country)
                    • Pays : <span class="font-semibold text-gray-900">{{ strtoupper($country) }}</span>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('calculator') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50">
                Nouveau calcul
            </a>
            
            {{-- ✅ Bouton d'export conditionnel (Premium/Pro uniquement) --}}
            @if ($can_export)
                <a href="{{ route('calculator.result', ['amount' => $amount, 'country' => $country, 'type' => $type, 'download' => 1]) }}" 
                   class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-emerald-700">
                    📥 Exporter CSV
                </a>
            @else
                <button disabled 
                        class="inline-flex items-center justify-center rounded-xl bg-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-500 cursor-not-allowed">
                    📥 Exporter CSV 🔒
                    <span class="ml-1 text-xs">(Premium)</span>
                </button>
            @endif
        </div>
    </div>

    {{-- ✅ Affichage du message d'erreur --}}
    @if ($message)
        <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
            {{ $message }}
        </div>
    @endif

    {{-- ✅ Affichage de l'état de l'abonnement --}}
    @if(isset($subscription_status) && $subscription_status === 'trial_expiring_soon')
        <div class="mb-8 rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-sm text-yellow-800">
            ⚠️ Votre essai gratuit expire dans <strong>{{ $trial_days_left }}</strong> jours ! 
            <a href="{{ route('pricing') }}" class="font-semibold text-yellow-900 underline hover:text-yellow-700">Souscrire maintenant</a>
        </div>
    @endif

    @if(isset($subscription_status) && $subscription_status === 'expired_trial')
        <div class="mb-8 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">
            🚫 Votre essai gratuit a expiré. 
            <a href="{{ route('pricing') }}" class="font-semibold text-red-900 underline hover:text-red-700">Souscrire à un abonnement</a>
        </div>
    @endif

    @if(isset($subscription_message) && $subscription_status === 'active')
        <div class="mb-8 rounded-2xl border border-green-200 bg-green-50 p-5 text-sm text-green-800">
            ✅ {{ $subscription_message }}
        </div>
    @endif

    {{-- ✅ Résumé des résultats --}}
    @if ($summary && ! empty($summary['best']))
        <div class="mb-8 grid gap-5 md:grid-cols-3">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Meilleur choix</p>
                <p class="mt-3 text-xl font-black text-emerald-900">{{ $summary['best']['label'] ?? 'N/A' }}</p>
                <p class="mt-2 text-sm text-emerald-700">Frais : {{ number_format((float) ($summary['best']['fee'] ?? 0), 0, ',', ' ') }} FCFA</p>
            </div>

            <div class="rounded-2xl border border-sky-100 bg-sky-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Montant net</p>
                <p class="mt-3 text-xl font-black text-sky-900">{{ number_format((float) ($summary['best']['net'] ?? $amount), 0, ',', ' ') }} FCFA</p>
                <p class="mt-2 text-sm text-sky-700">Après frais estimés</p>
            </div>

            <div class="rounded-2xl border border-violet-100 bg-violet-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-violet-700">Économie</p>
                <p class="mt-3 text-xl font-black text-violet-900">{{ number_format((float) ($summary['savings'] ?? 0), 0, ',', ' ') }} FCFA</p>
                <p class="mt-2 text-sm text-violet-700">
                    @php
                        $savingsPercent = $amount > 0 ? round(($summary['savings'] ?? 0) / $amount * 100, 2) : 0;
                    @endphp
                    Soit {{ $savingsPercent }}% par rapport au pire choix
                </p>
            </div>
        </div>
    @endif

    {{-- ✅ Message de partage --}}
    @if ($best_option && $share_message)
        <div class="mb-8 rounded-2xl border border-indigo-200 bg-indigo-50 p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-700">Message de partage</p>
                    <p class="mt-2 text-sm text-indigo-900">{{ $share_message }}</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50" onclick="copyToClipboard('{{ addslashes($share_message) }}', this)">
                        <span>Copier</span>
                    </button>
                    <a href="https://wa.me/?text={{ urlencode($share_message) }}" target="_blank" rel="noopener" class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                        Envoyer via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ✅ Liste des options --}}
    @if ($options && count($options) > 0)
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xl shadow-slate-100/50">
            <div class="mb-5 flex flex-col gap-3 border-b border-gray-200 pb-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Classement des meilleures options</h2>
                    <p class="text-sm text-gray-500">Trié du moins cher au plus cher.</p>
                </div>

                <div class="relative w-full max-w-xs">
                    <input type="search" x-model="search" placeholder="Rechercher une option" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 outline-none ring-0 transition focus:border-indigo-300 focus:bg-white">
                </div>
            </div>

            <div class="space-y-4">
                @foreach ($options as $index => $option)
                    @php
                        $rank = (($options->currentPage() - 1) * $options->perPage()) + $index + 1;
                    @endphp

                    <div x-show="search === '' || '{{ strtolower($option['label']) }}'.includes(search.toLowerCase())" class="rounded-2xl border border-gray-200 bg-slate-50 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-lg font-black text-indigo-600 shadow-sm">
                                    {{ $rank }}
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-gray-900">{{ $option['label'] }}</p>
                                    <p class="text-sm text-gray-500">Frais total : {{ number_format((float) ($option['fee'] ?? 0), 0, ',', ' ') }} FCFA</p>
                                </div>
                            </div>

                            <div class="flex flex-col items-start gap-1 sm:flex-row sm:items-center sm:gap-4">
                                <div class="rounded-full bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700 shadow-sm">
                                    Net : {{ number_format((float) ($option['net'] ?? 0), 0, ',', ' ') }} FCFA
                                </div>
                                @if ($rank === 1)
                                    <span class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-white">Meilleur</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white">
                            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Réseau</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Montant</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Frais</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($option['details'] as $detail)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $detail['network'] }}</td>
                                            <td class="px-4 py-3">{{ number_format((float) $detail['amount'], 0, ',', ' ') }} FCFA</td>
                                            <td class="px-4 py-3 text-emerald-700 font-semibold">{{ number_format((float) $detail['fee'], 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($options->hasPages())
                <div class="mt-6 flex justify-center">
                    {{ $options->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="rounded-3xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-500">
            <p class="text-lg font-semibold text-gray-700">Aucune option disponible pour ce montant.</p>
            <p class="mt-2 text-sm">Essayez un autre montant ou ajoutez de nouveaux moyens de paiement dans vos paramètres.</p>
        </div>
    @endif
</div>
@endsection