@extends('layouts.guest')

@section('title', 'Créer votre compte')

@section('content')
    <div class="space-y-6" x-data="registrationHandler()">
        <!-- Header -->
        <div class="text-center lg:text-left">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Rejoignez la révolution
            </h1>
            <p class="mt-2 text-slate-500 text-base leading-relaxed">
                Optimisez vos frais de transfert dès aujourd'hui.
            </p>
        </div>

        <!-- Sélecteur de méthode d'inscription (Onglets) -->
        <div class="flex p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200 shadow-inner">
            <button type="button"
                    @click="setRegType('phone')"
                    :class="regType === 'phone' ? 'bg-white text-emerald-700 shadow-md font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="flex-1 py-3 px-4 rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2">
                <i class="fab fa-whatsapp text-emerald-500 text-lg"></i>
                <span>WhatsApp / SMS</span>
                <span class="ml-1 text-[10px] bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Rapide</span>
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
        <form method="POST" action="{{ route('register') }}" class="space-y-5" @submit="handleSubmit($event)">
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
            <!-- 🟢 MODE 1 : INSCRIPTION WHATSAPP / SMS (OTP) -->
            <!-- ============================================== -->
            <div x-show="regType === 'phone'" class="space-y-4 pt-1">
                <!-- Choix du canal (WhatsApp vs SMS) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                        Canal de réception du code
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button"
                                @click="channel = 'whatsapp'"
                                :class="channel === 'whatsapp' ? 'border-emerald-500 bg-emerald-50/70 text-emerald-800 ring-2 ring-emerald-300/60 font-bold' : 'border-slate-200 bg-white/70 text-slate-600 hover:bg-white'"
                                class="p-3 rounded-2xl border transition-all duration-200 flex items-center justify-center gap-2 text-sm shadow-sm">
                            <i class="fab fa-whatsapp text-emerald-600 text-lg"></i>
                            <span>WhatsApp</span>
                        </button>
                        <button type="button"
                                @click="channel = 'sms'"
                                :class="channel === 'sms' ? 'border-indigo-500 bg-indigo-50/70 text-indigo-800 ring-2 ring-indigo-300/60 font-bold' : 'border-slate-200 bg-white/70 text-slate-600 hover:bg-white'"
                                class="p-3 rounded-2xl border transition-all duration-200 flex items-center justify-center gap-2 text-sm shadow-sm">
                            <i class="fas fa-comment-sms text-indigo-600 text-base"></i>
                            <span>SMS direct</span>
                        </button>
                    </div>
                </div>

                <!-- Saisie du numéro de téléphone avec indicatif -->
                <div>
                    <label for="phone_input" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        <i class="fas fa-mobile-screen mr-2 text-emerald-500"></i>Numéro de téléphone
                    </label>
                    <div class="flex rounded-2xl border border-slate-200 bg-white/70 backdrop-blur-sm overflow-hidden focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200/50 transition-all shadow-sm">
                        <span class="inline-flex items-center px-4 bg-slate-100/80 text-slate-600 font-bold text-sm border-r border-slate-200" x-text="currentPrefix">
                            +229
                        </span>
                        <input id="phone_input" type="tel" x-model="phoneRaw" @input="clearStatusMessages()"
                               class="flex-1 px-4 py-3.5 border-0 focus:ring-0 text-slate-800 placeholder:text-slate-400 bg-transparent text-base"
                               placeholder="Ex : 97 00 00 00">
                    </div>
                    <input type="hidden" name="phone" :value="fullPhone">
                    @error('phone')
                        <p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                            <i class="fas fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Bouton d'envoi du code OTP -->
                <div>
                    <button type="button"
                            @click="sendOtp()"
                            :disabled="isSendingOtp || countdown > 0"
                            class="w-full py-3.5 px-4 rounded-2xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2 shadow-md"
                            :class="countdown > 0 ? 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed' : (channel === 'whatsapp' ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-emerald-200/60' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-200/60')">
                        <template x-if="isSendingOtp">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i> Envoi du code...
                            </span>
                        </template>
                        <template x-if="!isSendingOtp && countdown === 0">
                            <span class="flex items-center gap-2">
                                <i :class="channel === 'whatsapp' ? 'fab fa-whatsapp text-lg' : 'fas fa-paper-plane'"></i>
                                <span x-text="otpSent ? 'Renvoyer un nouveau code' : (channel === 'whatsapp' ? 'Envoyer le code par WhatsApp' : 'Envoyer le code par SMS')"></span>
                            </span>
                        </template>
                        <template x-if="!isSendingOtp && countdown > 0">
                            <span class="flex items-center gap-1">
                                <i class="far fa-clock"></i> Renvoyer dans <span x-text="countdown" class="font-mono font-bold"></span>s
                            </span>
                        </template>
                    </button>
                </div>

                <!-- Messages de retour (Succès / Erreur) -->
                <div x-show="errorMessage" x-cloak class="p-3.5 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-2.5">
                    <i class="fas fa-circle-exclamation mt-0.5 text-red-500"></i>
                    <span x-text="errorMessage"></span>
                </div>

                <div x-show="successMessage" x-cloak class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm space-y-2">
                    <div class="flex items-center gap-2 font-bold text-emerald-900">
                        <i class="fas fa-circle-check text-emerald-600"></i>
                        <span x-text="successMessage"></span>
                    </div>
                    <!-- Bandeau Démo (Permet de tester instantanément sans frais) -->
                    <div x-show="demoCode" class="p-3 bg-white/90 rounded-xl border border-emerald-200 shadow-sm flex items-center justify-between">
                        <span class="text-xs text-slate-600 font-medium">Code de test (mode démo) :</span>
                        <span class="font-mono text-base font-extrabold text-emerald-700 tracking-widest bg-emerald-100/70 px-3 py-1 rounded-lg" x-text="demoCode"></span>
                    </div>
                </div>

                <!-- Champ OTP (Code de validation à 6 chiffres) -->
                <div x-show="otpSent" x-cloak class="pt-2">
                    <label for="otp_code" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        <i class="fas fa-key mr-2 text-indigo-500"></i>Code de confirmation à 6 chiffres
                    </label>
                    <input id="otp_code" type="text" name="otp_code" x-model="otpCode" maxlength="6"
                           class="w-full text-center tracking-[0.4em] font-mono text-2xl font-bold py-3.5 px-4 rounded-2xl border-2 border-emerald-400 bg-emerald-50/20 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-200/50 transition-all text-slate-900 shadow-inner"
                           placeholder="••••••">
                    <p class="mt-1.5 text-xs text-slate-500 flex items-center gap-1">
                        <i class="fas fa-shield-halved text-emerald-500"></i> Code valable pendant 10 minutes.
                    </p>
                    @error('otp_code')
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
            <!-- 🔒 CHAMPS COMMUNS : MOT DE PASSE -->
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
                        :disabled="regType === 'phone' && (!otpSent || otpCode.trim().length !== 6)"
                        class="w-full font-bold py-4 px-6 rounded-2xl shadow-xl transition-all duration-300 transform text-base flex items-center justify-center gap-3"
                        :class="regType === 'phone' && (!otpSent || otpCode.trim().length !== 6) ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-200/50 hover:shadow-2xl hover:scale-[1.02] active:scale-[0.98]'">
                    <i class="fas fa-user-plus"></i>
                    <span>Créer mon compte</span>
                    <i class="fas fa-arrow-right text-sm opacity-70"></i>
                </button>
                <p x-show="regType === 'phone' && !otpSent" class="mt-2 text-center text-xs text-slate-500">
                    Cliquez d'abord sur "Envoyer le code" ci-dessus pour activer la création de votre compte.
                </p>
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
                channel: 'whatsapp', // 'whatsapp' ou 'sms'
                selectedCountry: 'BJ',
                phoneRaw: '',
                currentPrefix: '+229',
                otpSent: false,
                isSendingOtp: false,
                otpCode: '',
                countdown: 0,
                timerId: null,
                errorMessage: '',
                successMessage: '',
                demoCode: '',

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
                    // Si l'utilisateur a déjà tapé l'indicatif sans +, on le gère
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
                    this.clearStatusMessages();
                },

                updateCountryPrefix() {
                    this.currentPrefix = this.prefixes[this.selectedCountry] || '+229';
                },

                clearStatusMessages() {
                    this.errorMessage = '';
                },

                async sendOtp() {
                    const phone = this.fullPhone;
                    if (!phone || phone.length < 8) {
                        this.errorMessage = 'Veuillez saisir un numéro de téléphone valide.';
                        return;
                    }

                    this.isSendingOtp = true;
                    this.errorMessage = '';
                    this.successMessage = '';
                    this.demoCode = '';

                    try {
                        const res = await fetch('{{ route('otp.send') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                phone: phone,
                                channel: this.channel
                            })
                        });

                        const data = await res.json();

                        if (!res.ok || !data.success) {
                            this.errorMessage = data.message || 'Impossible d\'envoyer le code. Vérifiez votre numéro.';
                        } else {
                            this.otpSent = true;
                            this.successMessage = data.message || 'Code envoyé avec succès !';
                            if (data.demo_code) {
                                this.demoCode = data.demo_code;
                                this.otpCode = data.demo_code; // Remplissage d'aide au test
                            }
                            this.startCountdown(60);
                        }
                    } catch (e) {
                        this.errorMessage = 'Erreur réseau. Veuillez réessayer.';
                    } finally {
                        this.isSendingOtp = false;
                    }
                },

                startCountdown(seconds) {
                    this.countdown = seconds;
                    if (this.timerId) clearInterval(this.timerId);
                    this.timerId = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            clearInterval(this.timerId);
                            this.timerId = null;
                        }
                    }, 1000);
                },

                handleSubmit(e) {
                    if (this.regType === 'phone') {
                        if (!this.otpSent) {
                            e.preventDefault();
                            this.errorMessage = 'Veuillez d\'abord demander et valider votre code de vérification.';
                            return;
                        }
                        if (this.otpCode.trim().length !== 6) {
                            e.preventDefault();
                            this.errorMessage = 'Veuillez entrer le code de confirmation à 6 chiffres.';
                            return;
                        }
                    }
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