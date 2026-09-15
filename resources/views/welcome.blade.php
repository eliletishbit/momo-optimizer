@extends('layouts.home')

@section('content')
    <div class="bg-slate-950 text-slate-100 antialiased selection:bg-indigo-500/30">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(99,102,241,0.18),_transparent_30%),radial-gradient(circle_at_right,_rgba(45,212,191,0.14),_transparent_25%),linear-gradient(180deg,#020617_0%,#0f172a_100%)]">
            
            {{-- HEADER AVEC BOUTON ESSAYER --}}
            <header class="sticky top-0 z-40 border-b border-white/10 bg-slate-950/70 backdrop-blur-xl">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <a href="#top" class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-emerald-400 shadow-lg shadow-indigo-500/30">
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5 text-white">
                                <path fill="currentColor" d="M7 18.5c-.8 0-1.5-.7-1.5-1.5V7c0-.8.7-1.5 1.5-1.5h10c.8 0 1.5.7 1.5 1.5v10c0 .8-.7 1.5-1.5 1.5H7Zm2.2-8.7c0-.4.3-.7.7-.7h2.9c.8 0 1.5.7 1.5 1.5v1.5c0 .9-.6 1.6-1.5 1.7l-2.5.3c-.5.1-.9.5-.9 1v1.2c0 .4-.3.7-.7.7h-.5c-.4 0-.7-.3-.7-.7v-2.4c0-.8.4-1.5 1-2l2-.9H9.9c-.4 0-.7-.3-.7-.7v-.5Z"/>
                            </svg>
                        </span>
                        <div>
                            <div class="text-lg font-bold tracking-tight text-white">MomoOpti</div>
                            <div class="text-[10px] uppercase tracking-[0.24em] text-slate-400">Mobile money optimizer</div>
                        </div>
                    </a>

                    <nav class="hidden items-center gap-7 text-sm text-slate-300 md:flex">
                        <a href="#avantages" class="transition hover:text-white">Avantages</a>
                        <a href="#fonctionnalites" class="transition hover:text-white">Fonctionnalités</a>
                        <a href="#usage" class="transition hover:text-white">Cas d’usage</a>
                        <a href="#tarifs" class="transition hover:text-white">Tarifs</a>
                    </nav>

                    {{-- ✅ HEADER AVEC BOUTON ESSAI GRATUIT --}}
                    <div class="flex items-center gap-3">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="hidden rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white transition hover:border-indigo-400/60 hover:bg-indigo-500/10 sm:inline-flex">
                                    Dashboard
                                </a>
                            @else
                                {{-- ✅ BOUTON ESSAYER GRATUITEMENT (HEADER) --}}
                                <a href="{{ route('public.calculator') }}" 
                                   class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition hover:bg-emerald-400 hover:scale-[1.02]">
                                    🎯 Essayer
                                </a>
                                <a href="{{ route('login') }}" class="hidden rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-white/30 hover:text-white sm:inline-flex">
                                    Connexion
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex rounded-full bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                                        Créer un compte
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </header>

            <main id="top" class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8 lg:pt-20">

                {{-- SECTION HERO --}}
                <section class="grid items-center gap-12 lg:grid-cols-[1.1fr_0.9fr]">
                    <div>
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-500/10 px-3 py-1.5 text-xs font-medium uppercase tracking-[0.18em] text-indigo-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            SaaS mobile • International
                        </div>

                        <h1 class="max-w-xl text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl">
                            Choisir le bon réseau pour payer moins.
                        </h1>

                        <p class="mt-6 max-w-xl text-lg leading-8 text-slate-300">
                            MomoOpti compare instantanément les frais d’envoi et de retrait de vos moyens préférés pour vous aider à économiser à chaque transaction.
                        </p>

                        {{-- ✅ BOUTONS HERO --}}
                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            {{-- ✅ BOUTON ESSAYER GRATUITEMENT (HERO) --}}
                            <a href="{{ route('public.calculator') }}" 
                               class="inline-flex items-center gap-3 rounded-full bg-emerald-500 px-8 py-4 text-lg font-bold text-white shadow-2xl shadow-emerald-500/30 transition hover:bg-emerald-400 hover:scale-[1.02]">
                                <span>🎯 Essayer gratuitement</span>
                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-5 w-5">
                                    <path d="M4.167 10h11.666M10 4.167 15.833 10 10 15.833" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <a href="#tarifs" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-6 py-3.5 text-base font-semibold text-slate-100 transition hover:border-white/30 hover:bg-white/10">
                                Voir les offres
                            </a>
                        </div>

                        <div class="mt-10 flex flex-wrap items-center gap-3 text-sm text-slate-300">
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5">MTN</span>
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5">Moov</span>
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5">Celtiis</span>
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5">Orange</span>
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5">Wise</span>
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5">etc</span>
                        </div>
                    </div>

                    {{-- Aperçu du calculateur --}}
                    <div class="relative">
                        <div class="absolute -inset-10 rounded-[2rem] bg-indigo-500/20 blur-3xl"></div>
                        <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/80 p-4 shadow-2xl shadow-slate-950/60 backdrop-blur-xl">
                            <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/90 p-5">
                                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Analyse de retrait</p>
                                        <h2 class="mt-2 text-2xl font-bold text-white">175 000 FCFA</h2>
                                    </div>
                                    <div class="rounded-2xl bg-emerald-500/15 px-3 py-2 text-right">
                                        <div class="text-[10px] uppercase tracking-[0.2em] text-emerald-300">Meilleure option</div>
                                        <div class="mt-1 text-sm font-semibold text-emerald-300">Moov</div>
                                    </div>
                                </div>

                                <div class="mt-5 space-y-3">
                                    <div class="flex items-center justify-between rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400/15 text-lg font-bold text-emerald-300">1</div>
                                            <div>
                                                <div class="font-semibold text-white">Moov</div>
                                                <div class="text-xs text-slate-400">Retrait le moins cher</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-base font-bold text-white">1 750 FCFA</div>
                                            <div class="text-xs text-emerald-300">Montant net: 173 250</div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800 text-lg font-bold text-slate-100">2</div>
                                            <div>
                                                <div class="font-semibold text-white">MTN</div>
                                                <div class="text-xs text-slate-400">+250 FCFA</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-base font-bold text-white">2 000 FCFA</div>
                                            <div class="text-xs text-slate-400">Montant net: 173 000</div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800 text-lg font-bold text-slate-100">3</div>
                                            <div>
                                                <div class="font-semibold text-white">Celtiis</div>
                                                <div class="text-xs text-slate-400">+250 FCFA</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-base font-bold text-white">2 000 FCFA</div>
                                            <div class="text-xs text-slate-400">Montant net: 173 000</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 rounded-2xl border border-indigo-400/20 bg-indigo-500/10 p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-indigo-100">Économie potentielle</p>
                                        <span class="rounded-full bg-indigo-500/20 px-2 py-1 text-xs font-semibold text-indigo-200">-250 FCFA</span>
                                    </div>
                                    <div class="mt-2 text-3xl font-black text-white">+ 14%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- SECTION AVANTAGES --}}
                <section id="avantages" class="mt-24">
                    <div class="mb-10 text-center">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-300">Pourquoi c’est utile</p>
                        <h2 class="mt-3 text-3xl font-bold text-white sm:text-4xl">Une décision plus intelligente à chaque transfert.</h2>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-300">
                                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 18V6m0 0 7 6 9-6v12l-9-6-7 6Z" /></svg>
                            </div>
                            <h3 class="text-xl font-semibold text-white">Comparaison instantanée</h3>
                            <p class="mt-3 text-slate-300">Visualisez les frais, le montant net et la meilleure option avant de payer.</p>
                        </div>

                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-300">
                                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M12 5v14" /></svg>
                            </div>
                            <h3 class="text-xl font-semibold text-white">Économie réelle</h3>
                            <p class="mt-3 text-slate-300">Évitez les pertes inutiles sur chaque retrait et envoi selon les montants et réseaux.</p>
                        </div>

                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-300">
                                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 12h10M12 7v10M5 5h14v14H5z" /></svg>
                            </div>
                            <h3 class="text-xl font-semibold text-white">Mobile-first</h3>
                            <p class="mt-3 text-slate-300">Conçu pour fonctionner sereinement sur mobile, sur le terrain et en déplacement.</p>
                        </div>
                    </div>
                </section>

                {{-- SECTION ESSAI GRATUIT (BLOQUÉE) --}}
                <section class="mt-24">
                    <div class="relative overflow-hidden rounded-[2rem] border border-emerald-500/20 bg-gradient-to-br from-emerald-500/10 via-slate-900 to-slate-900 p-8 md:p-12">
                        <div class="absolute -inset-4 rounded-[2rem] bg-emerald-500/10 blur-3xl"></div>
                        <div class="relative flex flex-col items-center text-center">
                            <div class="inline-flex rounded-full bg-emerald-500/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-200">
                                🎯 Sans inscription
                            </div>
                            <h2 class="mt-6 text-3xl font-bold text-white md:text-4xl">
                                Testez MomoOpti <span class="text-emerald-400">gratuitement</span>
                            </h2>
                            <p class="mt-4 max-w-2xl text-lg text-slate-300">
                                Comparez les frais de retrait et d'envoi en quelques secondes. 
                                <span class="text-emerald-300 font-semibold">3 essais gratuits</span> sans créer de compte.
                            </p>
                            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                                <a href="{{ route('public.calculator') }}" 
                                   class="inline-flex items-center gap-3 rounded-full bg-emerald-500 px-8 py-4 text-lg font-bold text-white shadow-2xl shadow-emerald-500/30 transition hover:bg-emerald-400 hover:scale-[1.02]">
                                    <span>🚀 Tester maintenant</span>
                                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-5 w-5">
                                        <path d="M4.167 10h11.666M10 4.167 15.833 10 10 15.833" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                                <a href="#fonctionnalites" class="text-sm text-slate-400 hover:text-white transition">
                                    En savoir plus →
                                </a>
                            </div>
                            <div class="mt-6 flex flex-wrap items-center justify-center gap-6 text-sm text-slate-400">
                                <span class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                    Sans carte bancaire
                                </span>
                                <span class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                    3 essais gratuits
                                </span>
                                <span class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                    Aucune inscription
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- SECTION FONCTIONNALITÉS --}}
                <section id="fonctionnalites" class="mt-24">
                    <div class="mb-10 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-300">Fonctionnalités</p>
                            <h2 class="mt-3 text-3xl font-bold text-white sm:text-4xl">Tout ce qu’il faut pour optimiser vos transactions.</h2>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6">
                            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/15 text-indigo-300">
                                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20M2 12h20" /></svg>
                            </div>
                            <h3 class="text-2xl font-semibold text-white">Mode “Je reçois”</h3>
                            <p class="mt-3 text-slate-300">Saisissez le montant à recevoir et obtenez le classement complet des options les moins chères.</p>
                            <ul class="mt-5 space-y-3 text-sm text-slate-200">
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Classement par frais croissants</li>
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Montant net estimé</li>
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Message prêt à partager</li>
                            </ul>
                        </div>

                        <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6">
                            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-300">
                                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 18h14M9 6l6 6-6 6" /></svg>
                            </div>
                            <h3 class="text-2xl font-semibold text-white">Mode “J’envoie”</h3>
                            <p class="mt-3 text-slate-300">Comparez vos moyens préférés pour choisir la solution la plus économique avant d’envoyer.</p>
                            <ul class="mt-5 space-y-3 text-sm text-slate-200">
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Frais détaillés</li>
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Montant total à débiter</li>
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Sélection simple et rapide</li>
                            </ul>
                        </div>

                        <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6">
                            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-300">
                                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 7h10v10H7zM7 12h10" /></svg>
                            </div>
                            <h3 class="text-2xl font-semibold text-white">Moyens préférés</h3>
                            <p class="mt-3 text-slate-300">Enregistrez les comptes et réseaux que vous utilisez pour personnaliser les recommandations.</p>
                            <ul class="mt-5 space-y-3 text-sm text-slate-200">
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Paramétrage rapide</li>
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Plusieurs réseaux selon les pays</li>
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Historique et gestion simplifiée</li>
                            </ul>
                        </div>

                        <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6">
                            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-500/15 text-rose-300">
                                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 20V9m7 11V4m7 16v-7" /></svg>
                            </div>
                            <h3 class="text-2xl font-semibold text-white">Historique et économies</h3>
                            <p class="mt-3 text-slate-300">Gardez un œil sur vos transactions et visualisez les économies réalisées au fil du temps.</p>
                            <ul class="mt-5 space-y-3 text-sm text-slate-200">
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-rose-400"></span> Suivi des transactions</li>
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-rose-400"></span> Économies cumulées</li>
                                <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-rose-400"></span> Statistiques claires</li>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- SECTION CAS D'USAGE --}}
                <section id="usage" class="mt-24">
                    <div class="mb-10 text-center">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-300">Cas d’usage</p>
                        <h2 class="mt-3 text-3xl font-bold text-white sm:text-4xl">Pensé pour la vie quotidienne et les professionnels.</h2>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="rounded-[2rem] border border-indigo-500/20 bg-gradient-to-br from-indigo-500/10 to-slate-900 p-7">
                            <div class="inline-flex rounded-full bg-indigo-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-indigo-200">Pour les particuliers</div>
                            <h3 class="mt-5 text-2xl font-bold text-white">Je reçois de l’argent</h3>
                            <p class="mt-3 text-slate-300">Fatima veut recevoir 175 000 FCFA. L’application lui indique immédiatement que Moov est le moins cher, évitant ainsi 250 FCFA de frais inutiles.</p>
                            <div class="mt-6 rounded-2xl border border-white/10 bg-slate-950/70 p-4">
                                <div class="flex items-center justify-between text-sm text-slate-300">
                                    <span>Frais estimés</span>
                                    <span class="font-semibold text-white">1 750 FCFA</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-sm text-slate-300">
                                    <span>Montant net</span>
                                    <span class="font-semibold text-white">173 250 FCFA</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[2rem] border border-emerald-500/20 bg-gradient-to-br from-emerald-500/10 to-slate-900 p-7">
                            <div class="inline-flex rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-200">Pour les entreprises</div>
                            <h3 class="mt-5 text-2xl font-bold text-white">J’envoie de l’argent</h3>
                            <p class="mt-3 text-slate-300">Jean compare ses méthodes d’envoi et identifie immédiatement le réseau le plus économique pour des montants de plusieurs dizaines de milliers.</p>
                            <div class="mt-6 rounded-2xl border border-white/10 bg-slate-950/70 p-4">
                                <div class="flex items-center justify-between text-sm text-slate-300">
                                    <span>MTN</span>
                                    <span class="font-semibold text-emerald-300">125 FCFA</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-sm text-slate-300">
                                    <span>Moov</span>
                                    <span class="font-semibold text-white">1 000 FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- SECTION TARIFS --}}
                <section id="tarifs" class="mt-24">
                    <div class="mb-10 text-center">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-300">Tarifs</p>
                        <h2 class="mt-3 text-3xl font-bold text-white sm:text-4xl">Un accès simple, ciblé selon votre usage.</h2>
                    </div>

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
                                'usage' => 'Pour découvrir l’application',
                                'cta' => 'Commencer',
                                'popular' => false,
                                'url' => route('register'),
                                'btn_class' => 'bg-white/10 hover:bg-white/20 text-white border border-white/10'
                            ],
                            [
                                'id' => 'pay_as_you_go',
                                'name' => 'Pay As You Go',
                                'price' => '100 FCFA',
                                'period' => '/ 5 utilisations',
                                'features' => [
                                    '5 utilisations pour 100 FCFA',
                                    'Valable 1 mois (non consommé = perdu)',
                                    'Flexible, sans engagement'
                                ],
                                'usage' => 'Idéal pour les usages occasionnels, sans abonnement.',
                                'cta' => 'Acheter',
                                'popular' => false,
                                'url' => route('subscription.checkout', ['plan' => 'pay_as_you_go']),
                                'btn_class' => 'bg-emerald-500 hover:bg-emerald-400 text-white shadow-lg shadow-emerald-500/30'
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
                                'usage' => 'Pour les particuliers actifs qui souhaitent optimiser chaque envoi et retrait.',
                                'cta' => "S'abonner",
                                'popular' => true,
                                'url' => route('subscription.checkout', ['plan' => 'premium']),
                                'btn_class' => 'bg-indigo-500 hover:bg-indigo-400 text-white shadow-lg shadow-indigo-500/30'
                            ],
                            [
                                'id' => 'pro',
                                'name' => 'Pro',
                                'price' => '5 000 FCFA',
                                'period' => '/ mois',
                                'features' => [
                                    'Tout Premium',                    
                                    'Bilan mensuel avancé (export PDF/CSV)',
                                    'Tableau de bord décisionnel'
                                ],
                                'usage' => 'Pour les petits commerces, PME ou grandes entreprises qui veulent un suivi régulier et fiable.',
                                'cta' => "S'abonner",
                                'popular' => false,
                                'url' => route('subscription.checkout', ['plan' => 'pro']),
                                'btn_class' => 'bg-indigo-500 hover:bg-indigo-400 text-white shadow-lg shadow-indigo-500/30'
                            ],
                        ];
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                        @foreach($plans as $plan)
                            <div class="rounded-[2rem] border {{ $plan['popular'] ? 'border-indigo-500/30 bg-indigo-500/10 shadow-2xl shadow-indigo-500/10' : 'border-white/10 bg-slate-900/70' }} p-6 transition hover:scale-[1.02] duration-300">
                                @if($plan['popular'])
                                    <div class="inline-flex rounded-full border border-indigo-400/40 bg-indigo-500/20 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-indigo-200">Populaire</div>
                                @endif

                                <p class="text-sm font-semibold uppercase tracking-[0.2em] {{ $plan['popular'] ? 'text-indigo-200' : 'text-slate-400' }}">
                                    {{ $plan['name'] }}
                                </p>
                                <div class="mt-4 text-4xl font-black text-white">{{ $plan['price'] }}</div>
                                <p class="{{ $plan['popular'] ? 'text-indigo-100' : 'text-slate-300' }}">{{ $plan['period'] }}</p>

                                <p class="mt-2 text-sm {{ $plan['popular'] ? 'text-indigo-200/80' : 'text-slate-300/80' }} italic">
                                    {{ $plan['usage'] }}
                                </p>

                                <ul class="mt-6 space-y-3 text-sm {{ $plan['popular'] ? 'text-slate-100' : 'text-slate-200' }}">
                                    @foreach($plan['features'] as $feature)
                                        <li class="flex items-start">
                                            <svg class="h-5 w-5 text-indigo-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="ml-3">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                @auth
                                    @if(auth()->user()->subscription === $plan['id'])
                                        <div class="mt-8 bg-emerald-500/10 text-emerald-300 text-center py-2 rounded-lg font-semibold border border-emerald-400/20">
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
                        @endforeach
                    </div>

                    <div class="mt-16 text-center text-slate-400 text-sm">
                        <p>Tous les prix sont en FCFA (Franc CFA).</p>
                        <p class="mt-1">Les abonnements sont mensuels et renouvelables automatiquement.</p>
                        <p class="mt-1 text-xs text-slate-500">Paiement sécurisé via FedaPay (Mobile Money, carte bancaire).</p>
                    </div>
                </section>

                {{-- SECTION TÉMOIGNAGES --}}
                <section id="temoignages" class="mt-24">
                    <div class="mb-10 text-center">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-300">Ils nous font confiance</p>
                        <h2 class="mt-3 text-3xl font-bold text-white sm:text-4xl">Ce que disent nos utilisateurs</h2>
                        <p class="mt-4 text-slate-300 max-w-2xl mx-auto">
                            Des milliers de personnes économisent chaque jour grâce à MomoOpti.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 backdrop-blur-sm hover:border-indigo-400/30 transition duration-300">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-emerald-400 text-lg font-bold text-white">A</div>
                                <div>
                                    <p class="font-semibold text-white">Amina Diallo</p>
                                    <p class="text-xs text-slate-400">Commerçante • Cotonou</p>
                                </div>
                            </div>
                            <div class="mt-4 flex text-emerald-400">
                                @for($i=0; $i<5; $i++)
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                @endfor
                            </div>
                            <p class="mt-4 text-sm leading-relaxed text-slate-200">
                                "Grâce à MomoOpti, j’économise beaucoup d'argent par mois sur mes retraits. L’application est simple et rapide, je la recommande à tous mes collègues commerçants."
                            </p>
                        </div>

                        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 backdrop-blur-sm hover:border-indigo-400/30 transition duration-300">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-400 text-lg font-bold text-white">K</div>
                                <div>
                                    <p class="font-semibold text-white">Koffi Mensah</p>
                                    <p class="text-xs text-slate-400">Freelance • Lomé</p>
                                </div>
                            </div>
                            <div class="mt-4 flex text-emerald-400">
                                @for($i=0; $i<5; $i++)
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                @endfor
                            </div>
                            <p class="mt-4 text-sm leading-relaxed text-slate-200">
                                "Je reçois des paiements de l’étranger. MomoOpti m’a fait économiser plus de 10 000 FCFA sur mes derniers retraits. Un outil indispensable pour les freelances."
                            </p>
                        </div>

                        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 backdrop-blur-sm hover:border-indigo-400/30 transition duration-300">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-lg font-bold text-white">M</div>
                                <div>
                                    <p class="font-semibold text-white">Mamadou Sow</p>
                                    <p class="text-xs text-slate-400">PME • Dakar</p>
                                </div>
                            </div>
                            <div class="mt-4 flex text-emerald-400">
                                @for($i=0; $i<5; $i++)
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                @endfor
                            </div>
                            <p class="mt-4 text-sm leading-relaxed text-slate-200">
                                "Avec MomoOpti, j’optimise les paiements fournisseurs de mon entreprise. J’ai réduit de 15% mes frais de transfert en seulement 2 mois. Un vrai gain de compétitivité."
                            </p>
                        </div>
                    </div>
                </section>

            </main>

            {{-- FOOTER --}}
            <footer class="border-t border-white/10 bg-slate-950/80">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm text-slate-400 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-emerald-400 text-white">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M7 18.5c-.8 0-1.5-.7-1.5-1.5V7c0-.8.7-1.5 1.5-1.5h10c.8 0 1.5.7 1.5 1.5v10c0 .8-.7 1.5-1.5 1.5H7Zm2.2-8.7c0-.4.3-.7.7-.7h2.9c.8 0 1.5.7 1.5 1.5v1.5c0 .9-.6 1.6-1.5 1.7l-2.5.3c-.5.1-.9.5-.9 1v1.2c0 .4-.3.7-.7.7h-.5c-.4 0-.7-.3-.7-.7v-2.4c0-.8.4-1.5 1-2l2-.9H9.9c-.4 0-.7-.3-.7-.7v-.5Z" /></svg>
                        </span>
                        <span>MomoOpti © 2026</span>
                    </div>
                    <div class="flex items-center gap-6">
                        <a href="#avantages" class="transition hover:text-white">Avantages</a>
                        <a href="#fonctionnalites" class="transition hover:text-white">Fonctionnalités</a>
                        <a href="#tarifs" class="transition hover:text-white">Tarifs</a>
                    </div>
                </div>
            </footer>
        </div>

        {{-- PWA Install Button --}}
        <button id="pwa-install-btn" type="button" class="fixed bottom-5 right-5 hidden items-center gap-2 rounded-full border border-indigo-400/40 bg-slate-900/90 px-3.5 py-2.5 text-sm font-semibold text-indigo-100 shadow-2xl shadow-indigo-500/20 backdrop-blur-lg transition hover:border-indigo-300 hover:text-white" aria-label="Installer l'application">
            <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4">
                <path fill="currentColor" d="M12 3a1 1 0 0 1 1 1v8.59l2.3-2.3a1 1 0 1 1 1.4 1.42l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.42L11 12.59V4a1 1 0 0 1 1-1Zm-7 13a1 1 0 0 1 1 1v1h12v-1a1 1 0 1 1 2 0v1a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1a1 1 0 0 1 1-1Z"/>
            </svg>
            Installer
        </button>

        <script>
            let deferredPrompt = null;
            const installButton = document.getElementById('pwa-install-btn');

            window.addEventListener('beforeinstallprompt', (event) => {
                event.preventDefault();
                deferredPrompt = event;
                installButton.classList.remove('hidden');
                installButton.classList.add('inline-flex');
            });

            installButton.addEventListener('click', async () => {
                if (!deferredPrompt) {
                    return;
                }
                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;
                installButton.classList.add('hidden');
                installButton.classList.remove('inline-flex');
            });

            window.addEventListener('appinstalled', () => {
                installButton.classList.add('hidden');
                installButton.classList.remove('inline-flex');
            });
        </script>
    </div>
@endsection