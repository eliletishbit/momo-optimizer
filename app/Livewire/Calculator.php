<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\User;
use App\Services\FeeOptimizerAdapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Calculator extends Component
{
    #[Validate('required|numeric|min:100')]
    public float $amount = 1000;

    public string $currency = 'XOF';

    public string $country = 'BJ';

    public string $type = 'withdrawal';

    public ?string $selectedNetwork = null;

    public ?array $results = null;

    public ?string $errorMessage = null;

    public bool $isLoading = false;

    public int $perPage = 5;

    public int $page = 1;

    private bool $initialized = false;

    public function mount()
    {
        $user = Auth::user();
        if ($user && $user->country_code) {
            $country = Country::where('code', $user->country_code)->first();
            if ($country) {
                $this->currency = $country->currency;
                $this->initialized = true;
            }
        }
    }

    public function updatedCountry($value)
    {
        $country = Country::where('code', $value)->first();
        if ($country) {
            $this->currency = $country->currency;
        }
    }

    public function setAmount($value): void
    {
        $this->amount = (float) $value;
    }

    public function getUserNetworks(): array
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return [];
        }
        return $user->userMethods()
            ->with('method')
            ->get()
            ->pluck('method.name', 'method.name')
            ->toArray();
    }

    public function calculate(FeeOptimizerAdapter $optimizer): void
    {
        $this->resetErrorBag();
        $this->errorMessage = null;
        $this->isLoading = true;
        $this->page = 1;
        $this->perPage = 5;

        $user = Auth::user();
        if (!$user instanceof User) {
            $this->errorMessage = 'Veuillez vous connecter pour utiliser le calculateur.';
            $this->isLoading = false;
            return;
        }

        Log::info('🔍 Livewire Calculator - pays utilisé pour le calcul', [
            'selected_country' => $this->country,
            'user_country' => $user->country_code,
        ]);

        // ✅ Vérifier les accès
        if (!$user->canAccessCalculator()) {
            if ($user->subscription === 'pay_as_you_go' && $user->payg_credits <= 0) {
                $this->errorMessage = 'Vous avez utilisé tous vos crédits Pay As You Go. Souscrivez à un forfait Premium, Pro ou achetez de nouveaux crédits pour continuer.';
                $this->isLoading = false;
                return;
            }

            if ($user->isDegraded()) {
                $remaining = $user->getRemainingCalculatorUsesThisMonth();
                $nextMonth = $user->getNextMonthlyRenewalDate();
                
                if ($remaining <= 0) {
                    $this->errorMessage = "Vous avez utilisé votre unique calcul gratuit du mois. Prochain calcul disponible le {$nextMonth}. Souscrivez à un abonnement pour un accès illimité.";
                    $this->isLoading = false;
                    return;
                }
                
                $this->errorMessage = "Mode dégradé - 1 calcul gratuit ce mois-ci. Souscrivez à un abonnement pour un accès illimité.";
                $this->isLoading = false;
                return;
            }

            if ($user->hasExpiredFreeTrial()) {
                $this->errorMessage = 'Votre essai gratuit a expiré. Souscrivez à un forfait premium, pro ou pay as you go pour continuer.';
                $this->isLoading = false;
                return;
            }

            if ($user->hasUsedFreeTrial() && $user->subscription === 'free') {
                $this->errorMessage = 'Votre essai gratuit est terminé. Souscrivez à un abonnement pour continuer à utiliser le calculateur.';
                $this->isLoading = false;
                return;
            }

            $this->errorMessage = 'Ajoutez au moins un moyen de paiement pour utiliser le calculateur.';
            $this->isLoading = false;
            return;
        }

        if ($user->userMethods()->count() < 1) {
            $this->errorMessage = 'Ajoutez au moins un moyen de paiement pour utiliser le calculateur.';
            $this->isLoading = false;
            return;
        }

        $validated = $this->validate();
        
        $methodIds = $user->userMethods()
            ->whereHas('method', function ($query) {
                $query->where(function ($q) {
                    $q->where('country_code', $this->country)
                      ->orWhereNull('country_code');
                });
            })
            ->pluck('method_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (count($methodIds) < 1) {
            $this->errorMessage = 'Aucun moyen de paiement trouvé pour le pays sélectionné.';
            $this->isLoading = false;
            return;
        }

        $result = $optimizer->optimizeWithdrawal(
            (float) $validated['amount'],
            $methodIds,
            $this->country,
            $this->type,
        );

        if (isset($result['error'])) {
            $this->errorMessage = $result['error'];
            $this->results = null;
            $this->isLoading = false;
            return;
        }

        $allOptions = [];
        if (isset($result['best'])) {
            $allOptions[] = $result['best'];
        }
        if (isset($result['alternatives'])) {
            $allOptions = array_merge($allOptions, $result['alternatives']);
        }

        if ($this->selectedNetwork) {
            $filteredOptions = array_filter($allOptions, function ($option) {
                foreach ($option['details'] as $detail) {
                    if ($detail['network'] !== $this->selectedNetwork) {
                        return false;
                    }
                }
                return true;
            });
            $allOptions = array_values($filteredOptions);
        }

        usort($allOptions, fn($a, $b) => $a['fee'] <=> $b['fee']);

        if (empty($allOptions)) {
            $this->errorMessage = $this->selectedNetwork
                ? 'Aucune option trouvée pour le réseau sélectionné.'
                : 'Aucune option d\'optimisation trouvée.';
            $this->results = null;
            $this->isLoading = false;
            return;
        }

        $best = $allOptions[0];
        $worst = $allOptions[count($allOptions) - 1];
        $savings = max(0, $worst['fee'] - $best['fee']);

        $this->results = [
            'options' => $allOptions,
            'best' => $best['label'] ?? 'N/A',
            'savings' => (int) round($savings),
            'savings_percent' => $this->amount > 0 ? round(($savings / $this->amount) * 100, 2) : 0,
            'fun_content' => $result['fun_content'] ?? null,
        ];

        $this->errorMessage = null;
        $this->isLoading = false;
    }

    public function loadMore(): void
    {
        $this->perPage += 5;
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage(): void
    {
        if ($this->page * $this->perPage < count($this->results['options'] ?? [])) {
            $this->page++;
        }
    }

    public function render()
    {
        $options = $this->results['options'] ?? [];
        $totalOptions = count($options);
        $displayedOptions = array_slice($options, 0, $this->page * $this->perPage);
        $hasMore = $this->page * $this->perPage < $totalOptions;

        $bestOption = $options[0] ?? null;
        $shareMessage = $bestOption
            ? 'Bonjour, je dois ' . ($this->type === 'sending' ? 'envoyer' : 'recevoir') . ' ' . number_format((float) $this->amount, 0, ',', ' ') . ' ' . $this->currency . '. Le meilleur choix est ' . ($bestOption['label'] ?? 'N/A') . ' avec des frais estimés à ' . number_format((float) ($bestOption['fee'] ?? 0), 0, ',', ' ') . ' ' . $this->currency . '.'
            : null;

        // ✅ CORRECTION POSTGRESQL : whereRaw pour les booléens
        $countries = Cache::remember('active_countries', 3600, function () {
            return Country::whereRaw('is_active = true')->orderBy('name')->get();
        });

        return view('livewire.calculator', [
            'options' => $displayedOptions,
            'bestOption' => $bestOption,
            'shareMessage' => $shareMessage,
            'hasMore' => $hasMore,
            'totalOptions' => $totalOptions,
            'currentPage' => $this->page,
            'perPage' => $this->perPage,
            'userNetworks' => $this->getUserNetworks(),
            'funContent' => $this->results['fun_content'] ?? null,
            'countries' => $countries,
            'currency' => $this->currency,
        ]);
    }
}