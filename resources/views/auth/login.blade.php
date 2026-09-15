@extends('layouts.guest')

@section('title', 'Connexion')

@section('content')
    <div class="mb-10 text-center lg:text-left">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Bon retour parmi nous !</h2>
        <p class="text-slate-500 mt-3 text-base">Ravis de vous revoir. Connectez-vous pour optimiser vos frais.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="block text-sm font-bold text-slate-700 ml-1">
                <i class="far fa-envelope mr-2 text-indigo-500"></i> Adresse Email
            </label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required autofocus autocomplete="username"
                   class="block w-full px-5 py-4 bg-slate-50 border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 placeholder:text-slate-400 text-slate-800 @error('email') border-red-500 ring-2 ring-red-200/50 @enderror"
                   placeholder="nom@exemple.com">
            @error('email')
                <p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <div class="flex justify-between items-center px-1">
                <label for="password" class="block text-sm font-bold text-slate-700">
                    <i class="fas fa-lock mr-2 text-indigo-500"></i> Mot de passe
                </label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-bold text-indigo-600 hover:text-indigo-700 transition-colors" href="{{ route('password.request') }}">
                        Mot de passe oublié ?
                    </a>
                @endif
            </div>

            <!-- Champ Mot de passe avec icône œil -->
            <div class="relative">
                <input id="password" 
                       type="password" 
                       name="password" 
                       required autocomplete="current-password"
                       class="block w-full px-5 py-4 pr-12 bg-slate-50 border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 placeholder:text-slate-400 text-slate-800 @error('password') border-red-500 ring-2 ring-red-200/50 @enderror"
                       placeholder="••••••••">
                <button type="button" 
                        onclick="togglePasswordVisibility('password', 'password-icon')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition-colors">
                    <i id="password-icon" class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between py-2">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <div class="relative">
                    <input id="remember_me" type="checkbox" class="sr-only peer" name="remember">
                    <div class="w-10 h-6 bg-slate-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-indigo-500/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </div>
                <span class="ms-3 text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors">Se souvenir de moi</span>
            </label>
        </div>

        <!-- Bouton de connexion -->
        <div class="pt-4">
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-indigo-200 transform transition hover:-translate-y-0.5 active:scale-[0.98] duration-200">
                Se connecter <i class="fas fa-arrow-right ml-2 text-indigo-200"></i>
            </button>
        </div>
    </form>

    <!-- Lien vers inscription -->
    <div class="mt-12 pt-8 border-t border-slate-100 text-center">
        <p class="text-sm font-bold text-slate-500">
            Nouveau sur MomoOpti ? 
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 transition-colors ml-1">
                Créer un compte gratuitement
            </a>
        </p>
    </div>
@endsection

@push('scripts')
    <script>
        /**
         * Toggle password visibility
         */
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    </script>
@endpush