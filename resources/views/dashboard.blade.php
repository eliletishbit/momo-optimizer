{{-- <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout> --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- ✅ Affichage du statut de l'abonnement --}}
                    @php
                        $user = Auth::user();
                        $subscriptionStatus = $user ? $user->getSubscriptionStatus() : 'inactive';
                        $subscriptionMessage = $user ? $user->getSubscriptionMessage() : '';
                        $remainingCredits = $user ? $user->getPayAsYouGoRemainingUses() : 0;
                    @endphp

                    {{-- ✅ Message Pay As You Go --}}
                    @if($subscriptionStatus === 'pay_as_you_go')
                        <div class="mb-4 p-4 rounded-lg bg-blue-50 border border-blue-200">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">💳</span>
                                <div>
                                    <h3 class="font-semibold text-blue-800">Pay As You Go</h3>
                                    @if($remainingCredits > 0)
                                        <p class="text-blue-700">
                                            <span class="font-bold text-green-600">{{ $remainingCredits }}</span> utilisation(s) restante(s)
                                        </p>
                                    @else
                                        <p class="text-red-600 font-semibold">
                                            ⚠️ Plus de crédits disponibles.
                                            <a href="{{ route('pricing') }}" class="underline hover:text-red-800">Achetez-en ici.</a>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ✅ Message d'abonnement Premium/Pro --}}
                    @if($subscriptionStatus === 'active' && $subscriptionMessage)
                        <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">✅</span>
                                <div>
                                    <p class="text-green-800">{{ $subscriptionMessage }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ✅ Message essai gratuit --}}
                    @if($subscriptionStatus === 'trial_active')
                        <div class="mb-4 p-4 rounded-lg bg-yellow-50 border border-yellow-200">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">🔥</span>
                                <div>
                                    <p class="text-yellow-800">
                                        Essai gratuit - {{ $user ? $user->getFreeTrialDaysLeft() : 0 }} jour(s) restant(s)
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ✅ Message mode dégradé --}}
                    @if($subscriptionStatus === 'degraded')
                        <div class="mb-4 p-4 rounded-lg bg-orange-50 border border-orange-200">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">⚠️</span>
                                <div>
                                    <p class="text-orange-800">
                                        Mode dégradé - 1 calcul gratuit ce mois-ci.
                                        <a href="{{ route('pricing') }}" class="underline hover:text-orange-900">Souscrivez à un abonnement.</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ✅ Message par défaut (aucun abonnement) --}}
                    @if($subscriptionStatus === 'inactive' || $subscriptionStatus === 'trial_available')
                        <div class="mb-4 p-4 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">👋</span>
                                <div>
                                    <p class="text-gray-700">
                                        Bienvenue sur MomoOpti !
                                        <a href="{{ route('pricing') }}" class="underline hover:text-indigo-600">Découvrez nos offres.</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ✅ Message de bienvenue par défaut --}}
                    <div class="mt-6">
                        <p class="text-gray-600">{{ __("You're logged in!") }}</p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>