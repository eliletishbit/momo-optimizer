@extends('layouts.app')

@section('title', 'Résultat du calcul - MomoOpti')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- En-tête -->
    <div class="mb-8 text-center">
        <div class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">
            MomoOpti
        </div>
        <h1 class="mt-4 text-3xl font-black text-gray-900 sm:text-4xl">Résultat de l'optimisation</h1>
        <p class="mt-3 text-sm text-gray-600">
            Montant analysé : <span class="font-semibold text-gray-900">{{ number_format((float) $amount, 0, ',', ' ') }} FCFA</span>
            @if (isset($country) && $country)
                • Pays : <span class="font-semibold text-gray-900">{{ strtoupper($country) }}</span>
            @endif
        </p>
    </div>

    {{-- Message d'essai restant --}}
    <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-center text-sm text-blue-800">
        @if($hasRemaining)
            🎯 Il vous reste <strong>{{ $remaining }}</strong> essai(s) gratuit(s) sur {{ $maxTrials }}.
        @else
            ⚠️ Vous avez utilisé tous vos essais gratuits. 
            <a href="{{ route('register') }}" class="font-semibold underline text-blue-900 hover:text-blue-700">Créez un compte</a> pour débloquer l'illimité.
        @endif
    </div>

    @if (isset($message) && $message)
        <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
            {{ $message }}
        </div>
    @endif

    @if (isset($summary) && isset($summary['best']))
        <!-- Résumé -->
        <div class="mb-8 grid gap-5 md:grid-cols-3">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Meilleur choix</p>
                <p class="mt-3 text-xl font-black text-emerald-900">{{ $summary['best']['label'] ?? 'N/A' }}</p>
                <p class="mt-2 text-sm text-emerald-700">Frais : {{ number_format((float) ($summary['best']['fee'] ?? 0), 0, ',', ' ') }} FCFA</p>
            </div>

            <div class="rounded-2xl border border-sky-100 bg-sky-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Montant à recevoir</p>
                <p class="mt-2 text-xl font-black text-sky-900">{{ number_format((float) ((isset($summary['best']['net']) && isset($summary['best']['fee'])) ? ($summary['best']['net'] + $summary['best']['fee']) : $amount), 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</p>
                <p class="mt-2 text-sm text-sky-700">Net + frais estimés</p>
            </div>

            <div class="rounded-2xl border border-violet-100 bg-violet-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-violet-700">Économie</p>
                <p class="mt-3 text-xl font-black text-violet-900">{{ number_format((float) ($summary['savings'] ?? 0), 0, ',', ' ') }} FCFA</p>
                <p class="mt-2 text-sm text-violet-700">
                    @php
                        $savingsPercent = isset($amount) && $amount > 0 ? round(($summary['savings'] ?? 0) / $amount * 100, 2) : 0;
                    @endphp
                    Soit {{ $savingsPercent }}% par rapport au pire choix
                </p>
            </div>
        </div>

        <!-- Message de partage -->
        @if (isset($best_option) && isset($share_message) && $share_message)
            <div class="mb-8 rounded-2xl border border-indigo-200 bg-indigo-50 p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-700">📤 Message de partage</p>
                        <p class="mt-2 text-sm text-indigo-900">{{ $share_message }}</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50" onclick="copyToClipboard('{{ addslashes($share_message) }}', this)">
                            <span>Copier</span>
                        </button>
                        <a href="https://wa.me/?text={{ urlencode($share_message) }}" target="_blank" rel="noopener" class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Liste des options -->
    @if (isset($options) && count($options) > 0)
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xl shadow-slate-100/50">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Classement des meilleures options</h2>
                    <p class="text-sm text-gray-500">Trié du moins cher au plus cher.</p>
                </div>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Trié par coûts</span>
            </div>

            <div class="space-y-4">
                @foreach ($options as $index => $option)
                    @php
                        $rank = (($options->currentPage() - 1) * $options->perPage()) + $index + 1;
                    @endphp

                    <div class="rounded-2xl border border-gray-200 bg-slate-50 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/40">
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
                                    À recevoir : {{ number_format((float) (($option['net'] ?? 0) + ($option['fee'] ?? 0) > 0 ? ($option['net'] ?? 0) + ($option['fee'] ?? 0) : $amount), 0, ',', ' ') }} FCFA
                                </div>
                                @if ($rank === 1)
                                    <span class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-white">Meilleur</span>
                                @endif
                            </div>
                        </div>

                        <!-- Détails -->
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
                                            <td class="px-4 py-3 font-semibold text-emerald-700">{{ number_format((float) $detail['fee'], 0, ',', ' ') }} FCFA</td>
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
            <p class="mt-2 text-sm">Essayez un autre montant ou ajoutez de nouveaux moyens de paiement.</p>
        </div>
    @endif

    <!-- Boutons d'action -->
    <div class="mt-8 flex flex-wrap justify-center gap-4">
        <a href="{{ route('public.calculator') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">
            🔄 Nouveau calcul
        </a>

        @if($hasRemaining)
            <span class="inline-flex items-center rounded-xl bg-gray-200 px-6 py-3 text-base font-semibold text-gray-600">
                ⚡ {{ $remaining }} essai(s) restant(s)
            </span>
        @endif

        <a href="{{ route('register') }}" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-6 py-3 text-base font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
            🔐 Créer un compte pour des calculs illimités
        </a>
    </div>

    <div class="mt-12 text-center text-xs text-gray-400">
        © {{ date('Y') }} MomoOpti. Tous droits réservés.
    </div>
</div>
@endsection