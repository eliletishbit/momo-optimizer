@extends('layouts.app')

@section('title', 'Calculateur gratuit')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- En-tête -->
    <div class="mb-8 text-center">
        <div class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">
            MomoOpti
        </div>
        <h1 class="mt-4 text-3xl font-black text-gray-900 sm:text-4xl">Calculateur gratuit</h1>
        <p class="mt-3 text-sm text-gray-600">
            Comparez les frais de retrait et d'envoi en 30 secondes
            @if($canCalculate)
                <span class="inline-block ml-2 bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold">
                    🔥 {{ $remaining }} essai(s) restant(s)
                </span>
            @endif
        </p>
    </div>

    {{-- Message d'essai gratuit --}}
    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🎯</span>
            <div>
                @if($canCalculate)
                    <p class="text-sm text-blue-800">
                        <strong>Essai gratuit :</strong> Il vous reste <strong>{{ $remaining }}</strong> calcul(s) gratuit(s) sur {{ $maxTrials }}.
                    </p>
                @else
                    <p class="text-sm text-blue-800">
                        <strong>Essai gratuit :</strong> Vous avez utilisé tous vos <strong>{{ $maxTrials }}</strong> essais gratuits.
                    </p>
                @endif
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($canCalculate)
        {{-- Formulaire --}}
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xl shadow-indigo-100/50 sm:p-8">
            <form action="{{ route('public.calculator.calculate') }}" method="POST" novalidate>
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="type" class="mb-2 block text-sm font-semibold text-gray-700">Type d'opération</label>
                        <select name="type" id="type" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <option value="withdrawal">Retrait / réception</option>
                            <option value="sending">Envoi</option>
                        </select>
                    </div>

                    <div>
                        <label for="country" class="mb-2 block text-sm font-semibold text-gray-700">Pays</label>
                        <select name="country" id="country" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <option value="BJ">Bénin (XOF)</option>
                            <option value="TG">Togo (XOF)</option>
                            <option value="CI">Côte d'Ivoire (XOF)</option>
                            <option value="SN">Sénégal (XOF)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="amount" class="mb-2 block text-sm font-semibold text-gray-700">Montant</label>
                    <div class="relative">
                        <input type="text" name="amount" id="amount" value="{{ old('amount', 1000) }}"
                               inputmode="numeric" pattern="[0-9]*"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100 pr-20"
                               placeholder="Ex: 175000" required>
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-500">XOF</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Minimum : 100 FCFA</p>
                    @error('amount')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3.5 text-base font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">
                    Calculer les frais
                </button>
            </form>

            <div class="mt-8 border-t border-gray-200 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center text-sm text-gray-600">
                    <div>
                        <span class="text-2xl block">📊</span>
                        Historique des calculs
                    </div>
                    <div>
                        <span class="text-2xl block">🔔</span>
                        Alertes de baisse de frais
                    </div>
                    <div>
                        <span class="text-2xl block">📁</span>
                        Export de résultats
                    </div>
                </div>
                <p class="mt-4 text-center text-xs text-gray-400">
                    🔐 <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Connectez-vous</a> ou <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">créez un compte</a> pour débloquer toutes ces fonctionnalités
                </p>
            </div>
        </div>
    @else
        {{-- Plus d'essais disponibles --}}
        <div class="rounded-3xl border border-gray-200 bg-white p-8 text-center shadow-xl shadow-slate-100/50">
            <div class="text-6xl mb-4">🚀</div>
            <h3 class="text-xl font-bold text-gray-800">Plus d'essais gratuits ?</h3>
            <p class="text-gray-600 mt-2">
                Vous avez utilisé vos <strong>{{ $maxTrials }}</strong> calculs gratuits.
            </p>
            <div class="mt-6 space-y-3">
                <a href="{{ route('register') }}"
                   class="inline-block w-full bg-indigo-600 text-white font-semibold py-3 rounded-xl hover:bg-indigo-700 transition">
                    Créer un compte gratuit
                </a>
                <p class="text-sm text-gray-500">
                    ✅ Sauvegardez vos calculs<br>
                    ✅ 21 jours d'essai gratuit<br>
                    ✅ Accès illimité
                </p>
            </div>
        </div>
    @endif
</div>
@endsection