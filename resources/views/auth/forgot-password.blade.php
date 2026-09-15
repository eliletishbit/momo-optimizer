@extends('layouts.guest')

@section('title', 'Mot de passe oublié')

@section('content')
    <div class="mb-10 text-center lg:text-left">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-50 text-amber-500 rounded-3xl mb-6 lg:mb-8 transform rotate-3">
            <i class="fas fa-key-skeleton fa-2xl"></i>
        </div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Oubli de passe ?</h2>
        <p class="text-slate-500 mt-3 text-base leading-relaxed">Pas de panique. Entrez votre email et nous vous envoyons un lien sécurisé de réinitialisation.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="block text-sm font-bold text-slate-700 ml-1">
                Email de récupération
            </label>
            <x-text-input id="email" class="block w-full px-5 py-4 bg-slate-50 border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300" 
                          type="email" name="email" :value="old('email')" placeholder="nom@exemple.com" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-extrabold py-4 px-6 rounded-2xl shadow-xl transition-all active:scale-[0.98]">
                Envoyer le lien <i class="fas fa-paper-plane ml-2 text-slate-400"></i>
            </button>
        </div>
    </form>

    <div class="mt-12 pt-8 border-t border-slate-100 text-center">
        <a href="{{ route('login') }}" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors flex items-center justify-center">
            <i class="fas fa-chevron-left mr-2 text-xs"></i> Retour à la connexion
        </a>
    </div>
@endsection
