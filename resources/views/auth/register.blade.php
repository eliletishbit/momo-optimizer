@extends('layouts.guest')

@section('title', 'Créer votre compte')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="text-center lg:text-left">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Rejoignez la révolution
            </h1>
            <p class="mt-2 text-slate-500 text-base leading-relaxed">
                Optimisez vos frais de transfert dès aujourd'hui.
            </p>
        </div>

        <!-- Formulaire -->
        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <!-- Champ : Nom -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    <i class="fas fa-user mr-2 text-indigo-500"></i>Nom complet
                </label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200/50 transition-all duration-200 placeholder:text-slate-400 text-slate-800 shadow-sm hover:shadow-md @error('name') border-red-500 ring-2 ring-red-200/50 @enderror"
                       placeholder="Votre nom complet">
                @error('name')
                    <p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Champ : Email -->
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    <i class="fas fa-envelope mr-2 text-indigo-500"></i>Adresse email
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                       class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200/50 transition-all duration-200 placeholder:text-slate-400 text-slate-800 shadow-sm hover:shadow-md @error('email') border-red-500 ring-2 ring-red-200/50 @enderror"
                       placeholder="exemple@email.com">
                @error('email')
                    <p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- champ code pays -->
            <div>
                <label for="country" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    <i class="fas fa-globe mr-2 text-indigo-500"></i>Pays
                </label>
                <select id="country" name="country_code" required
                    class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200/50 transition-all duration-200 text-slate-800 shadow-sm hover:shadow-md">
                    <option value="BJ">Bénin</option>
                    @foreach($countries ?? [] as $country)
                        <option value="{{ $country->code }}">{{ $country->name }}</option>
                    @endforeach
                </select>
                @error('country_code')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Champ : Mot de passe -->
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    <i class="fas fa-lock mr-2 text-indigo-500"></i>Mot de passe
                </label>
                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200/50 transition-all duration-200 placeholder:text-slate-400 text-slate-800 shadow-sm hover:shadow-md pr-12 @error('password') border-red-500 ring-2 ring-red-200/50 @enderror"
                           placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('password', 'password-icon')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition-colors">
                        <i id="password-icon" class="fas fa-eye"></i>
                    </button>
                </div>
                <p class="mt-2 text-xs text-slate-400 flex items-center gap-1">
                    <i class="fas fa-info-circle"></i> Minimum 8 caractères, une majuscule, un chiffre et un caractère spécial.
                </p>
                @error('password')
                    <p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Champ : Confirmation du mot de passe -->
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    <i class="fas fa-lock mr-2 text-indigo-500"></i>Confirmer le mot de passe
                </label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200/50 transition-all duration-200 placeholder:text-slate-400 text-slate-800 shadow-sm hover:shadow-md"
                       placeholder="••••••••">
            </div>

            <!-- Action : Bouton d'inscription (CORRIGÉ) -->
            <div class="pt-4">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-2xl shadow-xl shadow-indigo-200/50 hover:shadow-2xl hover:shadow-indigo-200/70 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] text-base flex items-center justify-center gap-3">
                    <i class="fas fa-user-plus"></i>
                    Créer mon compte
                    <i class="fas fa-arrow-right text-sm opacity-70 transition-transform group-hover:translate-x-1"></i>
                </button>
            </div>

            <!-- Lien vers connexion -->
            <div class="text-center mt-6">
                <p class="text-sm text-slate-500">
                    Vous avez déjà un compte ?
                    <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline underline-offset-4 transition-colors">
                        Connectez-vous
                    </a>
                </p>
            </div>

            <!-- Séparation -->
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white/70 backdrop-blur-sm text-slate-400 font-medium">ou</span>
                </div>
            </div>

            <!-- Boutons sociaux (optionnels) -->
            <div class="grid grid-cols-2 gap-4">
                <button type="button" class="flex items-center justify-center gap-3 w-full py-3 rounded-2xl border border-slate-200 bg-white/50 backdrop-blur-sm hover:bg-white transition-all duration-200 group shadow-sm hover:shadow-md">
                    <i class="fab fa-google text-[#ea4335] text-lg"></i>
                    <span class="text-sm font-medium text-slate-600 group-hover:text-slate-800">Google</span>
                </button>
                <button type="button" class="flex items-center justify-center gap-3 w-full py-3 rounded-2xl border border-slate-200 bg-white/50 backdrop-blur-sm hover:bg-white transition-all duration-200 group shadow-sm hover:shadow-md">
                    <i class="fab fa-apple text-slate-800 text-lg"></i>
                    <span class="text-sm font-medium text-slate-600 group-hover:text-slate-800">Apple</span>
                </button>
            </div>
        </form>
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