@extends('layouts.app')

@section('title', 'Tarifs - MomoOpti')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl">
            Choisissez l'offre qui vous correspond
        </h1>
        <p class="mt-4 text-xl text-gray-600 max-w-2xl mx-auto">
            Des solutions flexibles pour tous les profils, du particulier à l'entreprise.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        @php
            $plans = [
            
                [
                    'id' => 'free',
                    'name' => 'Free',
                    'price' => '0 FCFA',
                    'period' => '21 jours',
                    'features' => [
                        'Analyse standard',
                        'Accès limité (21j)',                        
                    ],
                    'cta' => 'Commencer',
                    'popular' => false,
                    'url' => route('register'),
                    'btn_class' => 'bg-gray-200 hover:bg-gray-300 text-gray-800'
                ],
                    [
                    'id' => 'pay_as_you_go',
                    'name' => 'Pay As You Go',
                    'price' => '100 FCFA',
                    'period' => '/ 5 utilisations',
                    'features' => [
                        '5 utilisations',
                        'Valable 1 mois',
                        'Flexible, sans engagement'
                    ],
                    'cta' => 'Acheter',
                    'popular' => false,
                    'url' => route('subscription.checkout', ['plan' => 'pay_as_you_go']),
                    'btn_class' => 'bg-indigo-600 hover:bg-indigo-700 text-white'
                ],
                [
                    'id' => 'premium',
                    'name' => 'Premium',
                    'price' => '1 000 FCFA',
                    'period' => '/ mois',
                    'features' => [
                        'Analyse illimitée',
                        'Historique complet',                        
                    ],
                    'cta' => 'S\'abonner',
                    'popular' => true,
                    'url' => route('subscription.checkout', ['plan' => 'premium']),
                    'btn_class' => 'bg-indigo-600 hover:bg-indigo-700 text-white'
                ],
                [
                    'id' => 'pro',
                    'name' => 'Pro',
                    'price' => '5 000 FCFA',
                    'period' => '/ mois',
                    'features' => [
                        'Tout Premium',
                        'Moyens illimités',
                        'Bilan mensuel avancé',
                        'Export PDF/CSV'
                    ],
                    'cta' => 'S\'abonner',
                    'popular' => false,
                    'url' => route('subscription.checkout', ['plan' => 'pro']),
                    'btn_class' => 'bg-indigo-600 hover:bg-indigo-700 text-white'
                ],
                
            ];
        @endphp

        @foreach($plans as $plan)
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 @if($plan['popular']) ring-2 ring-indigo-500 transform scale-105 @endif">
            @if($plan['popular'])
                <div class="bg-indigo-500 text-white text-center text-xs font-semibold py-1 uppercase tracking-wide">
                    Populaire
                </div>
            @else
                <div class="h-6"></div>
            @endif

            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900">{{ $plan['name'] }}</h3>
                <div class="mt-4">
                    <span class="text-4xl font-extrabold text-gray-900">{{ $plan['price'] }}</span>
                    <span class="text-sm text-gray-500">{{ $plan['period'] }}</span>
                </div>
                <ul class="mt-6 space-y-4">
                    @foreach($plan['features'] as $feature)
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-indigo-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="ml-3 text-gray-700">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                @auth
                    @if(auth()->user()->subscription === $plan['id'])
                        <div class="mt-8 bg-green-100 text-green-800 text-center py-2 rounded-lg font-semibold">
                            ✅ Actuel
                        </div>
                    @else
                        <a href="{{ $plan['url'] }}" class="mt-8 w-full inline-flex justify-center items-center py-3 px-4 rounded-lg font-semibold transition shadow-sm {{ $plan['btn_class'] }}">
                            {{ $plan['cta'] }}
                        </a>
                    @endif
                @else
                    <a href="{{ $plan['url'] }}" class="mt-8 w-full inline-flex justify-center items-center py-3 px-4 rounded-lg font-semibold transition shadow-sm {{ $plan['btn_class'] }}">
                        {{ $plan['cta'] }}
                    </a>
                @endauth
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-16 text-center text-gray-500 text-sm">
        <p>Tous les prix sont en FCFA (Franc CFA).</p>
        <p class="mt-1">Les abonnements sont mensuels et renouvelables automatiquement.</p>
    </div>
</div>
@endsection