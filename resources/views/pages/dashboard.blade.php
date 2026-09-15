{{-- @extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Bienvenue, {{ auth()->user()->name }} !</h1>
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500">Abonnement :</span>
            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold uppercase">
                {{ auth()->user()->subscription ?? 'Gratuit' }}
            </span>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-indigo-100 text-indigo-600 rounded-xl mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Optimisations effectuées</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $recentOptimizations->count() }}</p>
                </div>
            </div>
        </x-card>
        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Économies estimées</p>
                    <p class="text-2xl font-bold text-gray-900">-- FCFA</p>
                </div>
            </div>
        </x-card>
        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-amber-100 text-amber-600 rounded-xl mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Moyens enregistrés</p>
                    <p class="text-2xl font-bold text-gray-900">{{ auth()->user()->userMethods->count() }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Actions -->
        <div class="lg:col-span-2 space-y-8">
            <x-card title="Dernières optimisations">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Économie</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentOptimizations as $opt)
                                <tr>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $opt->created_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-4 text-sm font-bold text-gray-900">{{ number_format($opt->amount, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-4 text-sm text-emerald-600 font-bold">+ {{ number_format($opt->savings, 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">
                                        Aucune optimisation récente. <a href="{{ route('calculator') }}" class="text-indigo-600 underline">Commencer maintenant</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-6">
            <x-card title="Actions rapides">
                <div class="grid grid-cols-1 gap-4">
                    <x-button :href="route('calculator')" tag="a" class="w-full">Nouvelle optimisation</x-button>
                    <x-button :href="route('settings')" tag="a" variant="secondary" class="w-full">Mes moyens préférés</x-button>
                </div>
            </x-card>

            @if(!auth()->user()->hasActiveSubscription())
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg">
                    <h4 class="text-lg font-bold mb-2">Passez au Premium !</h4>
                    <p class="text-indigo-100 text-sm mb-6">Débloquez les options avancées tel que, le tableau analytique de bilan, les exports PDF et plus.</p>
                    <x-button :href="route('pricing')" tag="a" variant="secondary" class="w-full border-none text-indigo-700 hover:bg-white">
                        Voir les avantages
                    </x-button>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection --}}
@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Bienvenue, {{ $user->name }} !</h1>
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500">Abonnement :</span>
            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold uppercase">
                {{ $user->subscription ?? 'Gratuit' }}
            </span>
        </div>
    </div>

    <!-- ✅ Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-indigo-100 text-indigo-600 rounded-xl mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Optimisations effectuées</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalOptimizations }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Économies estimées</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalSavings, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-amber-100 text-amber-600 rounded-xl mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Moyens enregistrés</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $methodCount }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Actions -->
        <div class="lg:col-span-2 space-y-8">
            <x-card title="Dernières optimisations">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Économie</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentOptimizations as $opt)
                                <tr>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $opt->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        <span class="px-2 py-1 rounded-full text-xs {{ $opt->type === 'withdrawal' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                            {{ $opt->type === 'withdrawal' ? 'Retrait' : 'Envoi' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm font-bold text-gray-900">{{ number_format($opt->amount, 0, ',', ' ') }} FCFA</td>
                                    <td class="px-4 py-4 text-sm text-emerald-600 font-bold">+ {{ number_format($opt->savings, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 text-sm">
                                        Aucune optimisation récente. 
                                        <a href="{{ route('calculator') }}" class="text-indigo-600 underline">Commencer maintenant</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($totalOptimizations > 5)
                    <div class="mt-4 text-right">
                        <a href="{{ route('history') }}" class="text-sm text-indigo-600 hover:underline">Voir tout l'historique →</a>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-6">
            <x-card title="Actions rapides">
                <div class="grid grid-cols-1 gap-4">
                    <x-button :href="route('calculator')" tag="a" class="w-full">Nouvelle optimisation</x-button>
                    <x-button :href="route('settings')" tag="a" variant="secondary" class="w-full">Mes moyens préférés</x-button>
                </div>
            </x-card>

            @if($subscription_status !== 'active')
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg">
                    <h4 class="text-lg font-bold mb-2">Passez au Premium !</h4>
                    <p class="text-indigo-100 text-sm mb-6">Débloquez les options avancées tel que, le tableau analytique de bilan, les exports PDF et plus.</p>
                    <x-button :href="route('pricing')" tag="a" variant="secondary" class="w-full border-none text-indigo-700 hover:bg-white">
                        Voir les avantages
                    </x-button>
                </div>
            @endif

            @if($subscription_status === 'pay_as_you_go')
                <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-2xl p-6 text-white shadow-lg">
                    <h4 class="text-lg font-bold mb-2">💳 Pay As You Go</h4>
                    <p class="text-emerald-100 text-sm">
                        Vous avez <span class="font-bold text-white">{{ $remaining_credits }}</span> crédit(s) restant(s).
                    </p>
                    <a href="{{ route('pricing') }}" class="mt-4 inline-block text-sm text-emerald-200 hover:text-white underline">
                        Recharger →
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection