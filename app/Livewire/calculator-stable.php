<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\FeeOptimizer;
use App\Services\SendOptimizer; // à créer plus tard
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Calculator extends Component
{
    #[Validate('required|numeric|min:100')]
    public float $amount = 175000;

    public string $country = 'BJ';

    public string $type = 'withdrawal'; // 'withdrawal' ou 'sending'

    public ?array $results = null;

    public ?string $errorMessage = null;

    public bool $isLoading = false;

    public int $perPage = 5;

    public int $page = 1;

    public function setAmount($value): void
    {
        $this->amount = (float) $value;
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

    public function loadMore(): void
    {
        $this->perPage += 5;
    }

    public function calculate(FeeOptimizer $optimizer): void
    {
        $this->resetErrorBag();
        $this->errorMessage = null;
        $this->isLoading = true;
        $this->page = 1;
        $this->perPage = 5;

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            $this->errorMessage = 'Veuillez vous connecter pour utiliser le calculateur.';
            $this->isLoading = false;
            return;
        }

        if ($user->hasExpiredFreeTrial()) {
            $this->errorMessage = 'Votre essai gratuit a expiré. Souscrivez à un forfait premium, pro ou pay as you go pour continuer.';
            $this->isLoading = false;
            return;
        }

        if ($user->userMethods()->count() < 2) {
            $this->errorMessage = 'Ajoutez au moins 2 moyens de paiement pour utiliser le calculateur d’optimisation.';
            $this->isLoading = false;
            return;
        }

        $validated = $this->validate();

        $methodIds = $user->userMethods()->pluck('method_id')->filter()->unique()->values()->toArray();

        if (count($methodIds) < 2) {
            $this->errorMessage = 'Ajoutez au moins 2 moyens de paiement valides pour utiliser le calculateur d’optimisation.';
            $this->isLoading = false;
            return;
        }

        // Appel du service selon le type
        if ($this->type === 'sending') {
            // À implémenter : SendOptimizer
            $result = $this->optimizeSending((float) $validated['amount'], $methodIds);
        } else {
            $result = $optimizer->optimizeWithdrawal((float) $validated['amount'], $methodIds);
        }

        if (isset($result['error'])) {
            $this->errorMessage = $result['error'];
            $this->results = null;
            $this->isLoading = false;
            return;
        }

        // Construire la liste complète des options (meilleure + alternatives)
        $allOptions = [];
        if (isset($result['best'])) {
            $allOptions[] = $this->formatOption($result['best']);
        }
        if (isset($result['alternatives']) && is_array($result['alternatives'])) {
            foreach ($result['alternatives'] as $alt) {
                $allOptions[] = $this->formatOption($alt);
            }
        }

        // Si pas d'options, on affiche un message
        if (empty($allOptions)) {
            $this->results = null;
            $this->errorMessage = 'Aucune option d\'optimisation trouvée pour ce montant.';
            $this->isLoading = false;
            return;
        }

        // Déduplication par label (pour éviter les doublons)
        $uniqueOptions = [];
        $seenLabels = [];
        foreach ($allOptions as $option) {
            if (! in_array($option['label'], $seenLabels)) {
                $seenLabels[] = $option['label'];
                $uniqueOptions[] = $option;
            }
        }

        $this->results = [
            'options' => $uniqueOptions,
            'best' => $uniqueOptions[0]['label'] ?? 'N/A',
            'savings' => (float) ($result['savings'] ?? 0),
            'savings_percent' => $this->amount > 0 ? round(((float) ($result['savings'] ?? 0) / $this->amount) * 100, 2) : 0,
        ];

        $this->errorMessage = null;
        $this->isLoading = false;
    }

    private function formatOption(array $option): array
    {
        return [
            'label' => $option['label'] ?? 'Option',
            'fee' => (float) ($option['fee'] ?? 0),
            'net' => (float) ($option['net'] ?? $this->amount),
            'total_fee' => (float) ($option['fee'] ?? 0),
            'net_amount' => (float) ($option['net'] ?? $this->amount),
            'details' => $option['details'] ?? [],
        ];
    }

    private function optimizeSending(float $amount, array $methodIds): array
    {
        // À implémenter : appeler un service SendOptimizer similaire à FeeOptimizer
        // Pour l'instant, on renvoie un message d'erreur
        return [
            'error' => 'L\'optimisation des envois n\'est pas encore disponible.',
        ];
    }

    public function render()
    {
        $options = $this->results['options'] ?? [];
        $totalOptions = count($options);
        $displayedOptions = array_slice($options, 0, $this->page * $this->perPage);
        $hasMore = $this->page * $this->perPage < $totalOptions;

        $bestOption = $this->results['options'][0] ?? null;
        $shareMessage = $bestOption
            ? 'Bonjour, je dois ' . ($this->type === 'sending' ? 'envoyer' : 'recevoir') . ' ' . number_format((float) $this->amount, 0, ',', ' ') . ' FCFA. Le meilleur choix est ' . ($bestOption['label'] ?? 'N/A') . ' avec des frais estimés à ' . number_format((float) ($bestOption['total_fee'] ?? 0), 0, ',', ' ') . ' FCFA.'
            : null;

        return view('livewire.calculator', [
            'options' => $displayedOptions,
            'bestOption' => $bestOption,
            'shareMessage' => $shareMessage,
            'hasMore' => $hasMore,
            'totalOptions' => $totalOptions,
            'currentPage' => $this->page,
            'perPage' => $this->perPage,
        ]);
    }
}