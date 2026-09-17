<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- En-tête -->
    <div class="mb-8 text-center">
        <div class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">
            MomoOpti
        </div>
        <h1 class="mt-4 text-3xl font-black text-gray-900 sm:text-4xl">Calculateur d'optimisation</h1>
        <p class="mt-3 text-sm text-gray-600">Comparez les frais et trouvez la meilleure combinaison pour votre transaction.</p>
    </div>

    <!-- Formulaire -->
    <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xl shadow-indigo-100/50 sm:p-8">
        <form action="{{ route('calculator.calculate') }}" method="POST" wire:submit="calculate" class="space-y-6">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="type" class="mb-2 block text-sm font-semibold text-gray-700">Type d'opération</label>
                    <select wire:model="type" name="type" id="type" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="withdrawal">Retrait / réception</option>
                        <option value="sending">Envoi</option>
                    </select>
                </div>

                <div>
                    <label for="country" class="mb-2 block text-sm font-semibold text-gray-700">Pays</label>
                    <select wire:model.live="country" name="country" id="country" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        @foreach($countries as $countryOption)
                            <option value="{{ $countryOption->code }}" @if($countryOption->code === ($country ?? 'BJ')) selected @endif>{{ $countryOption->name }} ({{ $countryOption->currency }})</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Le pays est automatiquement sélectionné selon votre profil. Vous pouvez le modifier si nécessaire.</p>
                </div>
            </div>

            <div>
                <label for="amount" class="mb-2 block text-sm font-semibold text-gray-700">Montant</label>
                <div class="relative">
                    <input wire:model="amount" name="amount" id="amount" type="number" min="1" step="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100 pr-20" placeholder="Ex: 175000" />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-500">{{ $currency }}</span>
                </div>
                @error('amount')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Réseau d'origine -->
            <div>
                <label for="sourceNetwork" class="mb-2 block text-sm font-semibold text-gray-700">
                    Réseau d'origine (si l'argent est déjà sur un réseau)
                </label>
                <select wire:model="selectedNetwork" name="selectedNetwork" id="sourceNetwork" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    <option value="">Source inconnue (optimisation générale)</option>
                    @foreach ($userNetworks as $name)
                        <option value="{{ $name }}">{{ $name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Si vous connaissez le réseau sur lequel l'argent est déjà, sélectionnez-le pour voir uniquement les options de retrait sur ce réseau.</p>
            </div>

            <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3.5 text-base font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer" 
                    wire:loading.attr="disabled" 
                    wire:target="calculate">
                <span wire:loading.remove wire:target="calculate">Calculer les frais</span>
                <span wire:loading wire:target="calculate">Calcul en cours...</span>
            </button>
        </form>

        @if ($errorMessage)
            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                {{ $errorMessage }}
            </div>
        @endif
    </div>

    <!-- ==================== RÉSULTATS ==================== -->
    @if ($options && count($options) > 0)
        <div class="mt-8 space-y-6">
            <!-- Résumé -->
            <div class="grid gap-5 md:grid-cols-3">
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Meilleur choix</p>
                    <p class="mt-2 text-xl font-black text-emerald-900">{{ $bestOption['label'] ?? 'N/A' }}</p>
                    <p class="mt-1 text-sm text-emerald-700">Frais : {{ number_format((float) ($bestOption['fee'] ?? 0), 0, ',', ' ') }} {{ $currency }}</p>
                </div>
                <div class="rounded-2xl border border-sky-100 bg-sky-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Montant à recevoir</p>
                    <p class="mt-2 text-xl font-black text-sky-900">{{ number_format((float) ($bestOption['net'] + $bestOption['fee'] ?? $amount), 0, ',', ' ') }} {{ $currency }}</p>
                </div>
                <div class="rounded-2xl border border-violet-100 bg-violet-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-violet-700">Économie</p>
                    <p class="mt-2 text-xl font-black text-violet-900">{{ number_format((float) ($results['savings'] ?? 0), 0, ',', ' ') }} {{ $currency }}</p>
                    <p class="mt-1 text-sm text-violet-700">{{ $results['savings_percent'] ?? 0 }}% de réduction</p>
                </div>
            </div>

            <!-- Fun Content -->
            @if($funContent)
                <div class="rounded-2xl border border-indigo-300/50 bg-indigo-50/80 p-4 shadow-sm backdrop-blur-sm transition-all duration-300 hover:shadow-md">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-1">
                            <div class="h-8 w-8 rounded-full bg-indigo-200/60 flex items-center justify-center shadow-inner">
                                <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-indigo-500">✨ Le saviez-vous ?</p>
                            <p class="mt-1 text-sm text-gray-700 leading-relaxed">{{ $funContent }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Boutons de partage -->
            @if ($shareMessage)
                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <p class="text-sm text-indigo-900">{{ $shareMessage }}</p>
                        <div class="flex gap-3">
                            <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-white px-3 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50" onclick="copyToClipboard('{{ addslashes($shareMessage) }}', this)">
                                <span>Copier</span>
                            </button>
                            <a href="https://wa.me/?text={{ urlencode($shareMessage) }}" target="_blank" rel="noopener" class="rounded-xl bg-emerald-500 px-3 py-2 text-sm font-semibold text-white">
                                WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tableau des options -->
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xl shadow-slate-100/50">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Classement des options</h2>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Trié par coûts</span>
                </div>

                <div class="space-y-4">
                    @foreach ($options as $index => $option)
                        <div wire:key="option-{{ $index }}" class="rounded-2xl border border-gray-200 bg-slate-50 p-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-lg font-black text-indigo-600 shadow-sm">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <p class="text-lg font-bold text-gray-900">{{ $option['label'] }}</p>
                                        <p class="text-sm text-gray-500">Frais total : {{ number_format((float) ($option['fee'] ?? 0), 0, ',', ' ') }} {{ $currency }}</p>
                                    </div>
                                </div>
                                <div class="rounded-full bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700 shadow-sm">
                                    Net : {{ number_format((float) ($option['net'] ?? 0), 0, ',', ' ') }} {{ $currency }}
                                </div>
                            </div>

                            <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white">
                                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Réseau</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Montant</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Frais</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach ($option['details'] as $detail)
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-900">{{ $detail['network'] }}</td>
                                                <td class="px-4 py-3">{{ number_format((float) ($detail['amount'] ?? 0), 0, ',', ' ') }} {{ $currency }}</td>
                                                <td class="px-4 py-3 font-semibold text-emerald-700">{{ number_format((float) ($detail['fee'] ?? 0), 0, ',', ' ') }} {{ $currency }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (count($options) > 5)
                    <div class="mt-6 flex justify-center">
                        <button wire:click="loadMore" class="inline-flex items-center rounded-full bg-indigo-50 px-6 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                            Charger plus
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>