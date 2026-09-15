<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- En-tête -->
    <div class="mb-8 text-center">
        <div class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">
            MomoOpti
        </div>
        <h1 class="mt-4 text-3xl font-black text-gray-900 sm:text-4xl">Calculateur d’optimisation</h1>
        <p class="mt-3 text-sm text-gray-600">Comparez les frais et trouvez la meilleure combinaison pour votre transaction.</p>
    </div>

    <!-- Formulaire -->
    <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xl shadow-indigo-100/50 sm:p-8">
        <form wire:submit="calculate" class="space-y-6">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="type" class="mb-2 block text-sm font-semibold text-gray-700">Type d’opération</label>
                    <select wire:model="type" id="type" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="withdrawal">Retrait / réception</option>
                        <option value="sending">Envoi</option>
                    </select>
                </div>

                <div>
                    <label for="country" class="mb-2 block text-sm font-semibold text-gray-700">Pays</label>
                    <select wire:model="country" id="country" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="BJ">Bénin</option>
                        <!-- Ajouter d'autres pays ici -->
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Le pays est automatiquement sélectionné selon votre profil.</p>
                </div>
            </div>

            <div>
                <label for="amount" class="mb-2 block text-sm font-semibold text-gray-700">Montant (FCFA)</label>
                <input wire:model="amount" id="amount" type="number" min="100" step="100" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100" placeholder="Ex: 175000" />
                @error('amount')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nouveau dropdown pour la source réseau -->
            <div>
                <label for="sourceNetwork" class="mb-2 block text-sm font-semibold text-gray-700">
                    Réseau d’origine (si l’argent est déjà sur un réseau)
                </label>
                <select wire:model="selectedNetwork" id="sourceNetwork" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-gray-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    <option value="">Source inconnue (optimisation générale)</option>
                    @foreach ($userNetworks as $name)
                        <option value="{{ $name }}">{{ $name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Si vous connaissez le réseau sur lequel l’argent est déjà, sélectionnez‑le pour voir uniquement les options de retrait sur ce réseau.</p>
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3.5 text-base font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60" wire:loading.attr="disabled">
                <span wire:loading.remove>Calculer les frais</span>
                <span wire:loading>Calcul en cours...</span>
            </button>
        </form>

        @if ($errorMessage)
            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                {{ $errorMessage }}
            </div>
        @endif
    </div>

    <!-- Résultats -->
    @if ($options && count($options) > 0)
        <div class="mt-8 space-y-6">
            <!-- Résumé (meilleur choix, net, économies) -->
            <div class="grid gap-5 md:grid-cols-3">
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Meilleur choix</p>
                    <p class="mt-2 text-xl font-black text-emerald-900">{{ $bestOption['label'] ?? 'N/A' }}</p>
                    <p class="mt-1 text-sm text-emerald-700">{{ number_format((float) $bestOption['fee'] ?? 0, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="rounded-2xl border border-sky-100 bg-sky-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Montant net</p>
                    <p class="mt-2 text-xl font-black text-sky-900">{{ number_format((float) ($bestOption['net'] ?? $amount), 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="rounded-2xl border border-violet-100 bg-violet-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-violet-700">Économie</p>
                    <p class="mt-2 text-xl font-black text-violet-900">{{ number_format((float) $results['savings'] ?? 0, 0, ',', ' ') }} FCFA</p>
                    <p class="mt-1 text-sm text-violet-700">{{ $results['savings_percent'] ?? 0 }}% de réduction</p>
                </div>
            </div>

            <!-- Partage -->
            @if ($shareMessage)
                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <p class="text-sm text-indigo-900">{{ $shareMessage }}</p>
                        <div class="flex gap-3">
                            <button type="button" class="rounded-xl border border-indigo-200 bg-white px-3 py-2 text-sm font-semibold text-indigo-700" onclick="navigator.clipboard.writeText('{{ addslashes($shareMessage) }}')">
                                Copier
                            </button>
                            <a href="https://wa.me/?text={{ urlencode($shareMessage) }}" target="_blank" rel="noopener" class="rounded-xl bg-emerald-500 px-3 py-2 text-sm font-semibold text-white">
                                WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Classement des options -->
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xl shadow-slate-100/50">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Classement des options</h2>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Trié par coûts</span>
                </div>

                <div class="space-y-4">
                    @foreach ($options as $index => $option)
                        <div wire:key="option-{{ $index }}" class="rounded-2xl border border-gray-200 bg-slate-50 p-4">
                            <!-- En-tête de l'option -->
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-lg font-black text-indigo-600 shadow-sm">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <p class="text-lg font-bold text-gray-900">{{ $option['label'] }}</p>
                                        <p class="text-sm text-gray-500">Frais total : {{ number_format((float) $option['fee'], 0, ',', ' ') }} FCFA</p>
                                    </div>
                                </div>
                                <div class="rounded-full bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700 shadow-sm">
                                    Net : {{ number_format((float) $option['net'], 0, ',', ' ') }} FCFA
                                </div>
                            </div>

                            <!-- Détails des frais par réseau -->
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
                                                <td class="px-4 py-3">{{ number_format((float) $detail['amount'], 0, ',', ' ') }} FCFA</td>
                                                <td class="px-4 py-3 font-semibold text-emerald-700">{{ number_format((float) $detail['fee'], 0, ',', ' ') }} FCFA</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination / Charger plus -->
                @if ($hasMore)
                    <div class="mt-6 flex justify-center">
                        <button wire:click="loadMore" class="inline-flex items-center rounded-full bg-indigo-50 px-6 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                            Charger plus ({{ $totalOptions - $perPage * $currentPage }} restants)
                            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>
                @endif

                @if ($totalOptions > 5)
                    <div class="mt-4 flex justify-center gap-2">
                        <button wire:click="previousPage" wire:disabled="{{ $currentPage <= 1 }}" class="rounded-full bg-white px-4 py-1 text-sm font-semibold text-gray-600 shadow-sm hover:bg-gray-50 disabled:opacity-40">Précédent</button>
                        <span class="text-sm text-gray-600">Page {{ $currentPage }} / {{ ceil($totalOptions / $perPage) }}</span>
                        <button wire:click="nextPage" wire:disabled="{{ ! $hasMore }}" class="rounded-full bg-white px-4 py-1 text-sm font-semibold text-gray-600 shadow-sm hover:bg-gray-50 disabled:opacity-40">Suivant</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@if($isAI ?? false)
    <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-purple-500 to-indigo-500 px-2 py-0.5 text-[10px] font-bold text-white">
        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 14.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm.5-7.5a1.5 1.5 0 0 1-3 0V9a1.5 1.5 0 0 1 3 0z"/></svg>
        IA
    </span>
@endif 

