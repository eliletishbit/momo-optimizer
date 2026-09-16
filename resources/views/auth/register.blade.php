@extends('layouts.guest')

@section('title', 'Créer votre compte')

@section('content')
    <div class="space-y-6" x-data="registrationHandler()">
        <!-- Header -->
        <div class="text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-3">
                <i class="fas fa-gift text-emerald-600"></i> 14 jours d'essai gratuit inclus
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Créer votre compte
            </h1>
            <p class="mt-2 text-slate-500 text-base leading-relaxed">
                Rejoignez MomoOpti et optimisez vos transactions dès aujourd'hui.
            </p>
        </div>

        <!-- Sélecteur de méthode d'inscription (Onglets) -->
        <div class="flex p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200 shadow-inner">
            <button type="button"
                    @click="setRegType('phone')"
                    :class="regType === 'phone' ? 'bg-white text-emerald-700 shadow-md font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="flex-1 py-3 px-4 rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2">
                <i class="fab fa-whatsapp text-emerald-500 text-lg"></i>
                <span>Numéro WhatsApp / Téléphone</span>
                <span class="ml-1 text-[10px] bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Direct</span>
            </button>
            <button type="button"
                    @click="setRegType('email')"
                    :class="regType === 'email' ? 'bg-white text-indigo-700 shadow-md font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="flex-1 py-3 px-4 rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2">
                <i class="far fa-envelope text-indigo-500 text-base"></i>
                <span>Email classique</span>
            </button>
        </div>

        <!-- Formulaire d'inscription -->
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="registration_type" :value="regType">

            <!-- Champ : Nom -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    <i class="fas fa-user mr-2 text-indigo-500"></i>Nom complet
                </label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200/50 transition-all duration-200 placeholder:text-slate-400 text-slate-800 shadow-sm hover:shadow-md @error('name') border-red-500 ring-2 ring-red-200/50 @enderror"
                       placeholder="Ex : Rodrigue Koffi">
                @error('name')
                    <p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Champ : Pays -->
            <div>
                <label for="country" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    <i class="fas fa-globe mr-2 text-indigo-500"></i>Pays de résidence
                </label>
                <select id="country" name="country_code" x-model="selectedCountry" @change="updateCountryPrefix()" required
                    class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200/50 transition-all duration-200 text-slate-800 shadow-sm hover:shadow-md font-medium">
                    <option value="BJ">Bénin (+229)</option>
                    @foreach($countries ?? [] as $country)
                        @if($country->code !== 'BJ')
                            <option value="{{ $country->code }}">{{ $country->name }}</option>
                        @endif
                    @endforeach
                </select>
                @error('country_code')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- ============================================== -->
            <!-- 🟢 MODE 1 : INSCRIPTION WHATSAPP / TELEPHONE   -->
            <!-- ============================================== -->
            <div x-show="regType === 'phone'" class="space-y-4 pt-1">
                <div>
                    <label for="phone_input" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        <i class="fab fa-whatsapp mr-2 text-emerald-500"></i>Numéro WhatsApp / Téléphone
                    </label>
                    <div class="flex rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm overflow-hidden focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200/50 transition-all shadow-sm">
                        <span class="inline-flex items-center px-4 bg-slate-100/80 text-slate-600 font-bold text-sm border-r border-slate-200" x-text="currentPrefix">
                            +229
                        </span>
                        <input id="phone_input" type="tel" x-model="phoneRaw"
                               class="flex-1 px-4 py-3.5 border-0 focus:ring-0 text-slate-800 placeholder:text-slate-400 bg-transparent text-base"
                               placeholder="Ex : 97 00 00 00">
                    </div>
                    <input type="hidden" name="phone" :value="fullPhone">
                    <p class="mt-1.5 text-xs text-slate-400">
                        Votre numéro servira pour vous connecter facilement à votre compte.
                    </p>
                    @error('phone')
                        <p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                            <i class="fas fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- ============================================== -->
            <!-- ✉️ MODE 2 : INSCRIPTION PAR EMAIL CLASSIQUE -->
            <!-- ============================================== -->
            <div x-show="regType === 'email'" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        <i class="fas fa-envelope mr-2 text-indigo-500"></i>Adresse email
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username"
                           class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200/50 transition-all duration-200 placeholder:text-slate-400 text-slate-800 shadow-sm hover:shadow-md @error('email') border-red-500 ring-2 ring-red-200/50 @enderror"
                           placeholder="exemple@email.com">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                            <i class="fas fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- ============================================== -->
            <!-- 🔒 MOT DE PASSE -->
            <!-- ============================================== -->
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
                <p class="mt-1.5 text-xs text-slate-400 flex items-center gap-1">
                    <i class="fas fa-info-circle"></i> Minimum 8 caractères.
                </p>
                @error('password')
                    <p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Confirmation du mot de passe -->
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    <i class="fas fa-lock mr-2 text-indigo-500"></i>Confirmer le mot de passe
                </label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200/50 transition-all duration-200 placeholder:text-slate-400 text-slate-800 shadow-sm hover:shadow-md"
                       placeholder="••••••••">
            </div>

            <!-- Bouton d'inscription final -->
            <div class="pt-3">
                <button type="submit"
                        class="w-full font-bold py-4 px-6 rounded-2xl shadow-xl transition-all duration-300 transform text-base flex items-center justify-center gap-3 bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-200/50 hover:shadow-2xl hover:scale-[1.02] active:scale-[0.98] cursor-pointer">
                    <i class="fas fa-user-plus"></i>
                    <span>Créer mon compte gratuitement</span>
                    <i class="fas fa-arrow-right text-sm opacity-70"></i>
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
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function registrationHandler() {
            return {
                regType: 'phone', // 'phone' ou 'email'
                selectedCountry: 'BJ',
                phoneRaw: '',
                currentPrefix: '+229',

                prefixes: {
                    'BJ': '+229',
                    'CI': '+225',
                    'TG': '+228',
                    'SN': '+221',
                    'BF': '+226',
                    'ML': '+223',
                    'NE': '+227',
                    'CM': '+237',
                    'GA': '+241',
                    'CD': '+243',
                    'CG': '+242',
                    'GN': '+224',
                    'FR': '+33'
                },

                get fullPhone() {
                    const cleaned = this.phoneRaw.replace(/\D/g, '');
                    if (!cleaned) return '';
                    if (this.phoneRaw.startsWith('+')) {
                        return this.phoneRaw.replace(/\s+/g, '');
                    }
                    return this.currentPrefix + cleaned;
                },

                init() {
                    this.updateCountryPrefix();
                },

                setRegType(type) {
                    this.regType = type;
                },

                updateCountryPrefix() {
                    this.currentPrefix = this.prefixes[this.selectedCountry] || '+229';
                }
            };
        }

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