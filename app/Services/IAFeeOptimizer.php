<?php

namespace App\Services;

use App\Models\Method;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IAFeeOptimizer
{
    protected $geminiApiKey;
    protected $openRouterApiKey;
    protected $currentProvider;
    protected $currentModel;
    protected $groqApiKey ;

    // Configuration des fournisseurs (UNIQUEMENT GRATUITS)
protected $providers = [
    'groq' => [
            'type' => 'groq',
            'model' => 'llama3-70b-8192', // ou 'llama3-70b-8192'
            'url' => 'https://api.groq.com/openai/v1/chat/completions',
            'priority' => 1,
        ],
    'openrouter_free_1' => [
        'type' => 'openrouter',
        'model' => 'google/gemini-2.0-flash:free',
        'url' => 'https://openrouter.ai/api/v1/chat/completions',
        'priority' => 2,
    ],
    'openrouter_free_2' => [
        'type' => 'openrouter',
        'model' => 'qwen/qwen-coder:free',
        'url' => 'https://openrouter.ai/api/v1/chat/completions',
        'priority' => 3,
    ],
    'openrouter_free_3' => [
        'type' => 'openrouter',
        'model' => 'deepseek/deepseek-chat:free',
        'url' => 'https://openrouter.ai/api/v1/chat/completions',
        'priority' => 4,
    ],
    // Supprime TOTALEMENT le modèle payant
        'openrouter_paid' => [
            'type' => 'openrouter',
            'model' => 'google/gemini-2.5-flash',
            'url' => 'https://openrouter.ai/api/v1/chat/completions',
            'priority' => 5,
        ],
];

    public function __construct()
    {
        $this->groqApiKey = config('services.groq.api_key');
        $this->geminiApiKey = config('services.gemini.api_key');
        $this->openRouterApiKey = config('services.openrouter.api_key');

        $this->currentProvider = $this->getFirstAvailableProvider();
        Log::info('IAFeeOptimizer initialisé avec: ' . $this->currentProvider['type'] . ' (' . $this->currentProvider['model'] . ')');
    }

    /**
     * Trouve le premier fournisseur disponible
     */
    private function getFirstAvailableProvider(): array
    {
        // Trier les fournisseurs par priorité
        $sortedProviders = $this->providers;
        uasort($sortedProviders, fn($a, $b) => $a['priority'] <=> $b['priority']);

        foreach ($sortedProviders as $key => $provider) {
            if ($this->testProviderAvailability($provider)) {
                Log::info('✅ Fournisseur disponible: ' . $provider['type'] . ' (' . $provider['model'] . ')');
                return $provider;
            }
        }

        // Fallback : dernier recours
        Log::warning('⚠️ Aucun fournisseur IA disponible, utilisation du fallback OpenRouter payant');
        return $this->providers['openrouter_paid'];
    }

    /**
     * Teste la disponibilité d'un fournisseur
     */
    private function testProviderAvailability(array $provider): bool
    {
        try {
            $apiKey = match ($provider['type']) {
                'groq' => $this->groqApiKey,
                'gemini' => $this->geminiApiKey,
                'openrouter' => $this->openRouterApiKey,
                default => null,
            };

            if (empty($apiKey)) {
                return false;
            }

            $headers = ['Content-Type' => 'application/json'];
            if (in_array($provider['type'], ['groq', 'openrouter'])) {
                $headers['Authorization'] = 'Bearer ' . $apiKey;
            }

            $payload = $this->buildPingPayload($provider);
            $response = Http::timeout(5)->withHeaders($headers)->post($provider['url'], $payload);
            $status = $response->status();

            Log::info('Test fournisseur ' . $provider['type'] . ' - statut: ' . $status);
            return in_array($status, [200, 429, 503]);
        } catch (\Exception $e) {
            Log::warning('Test fournisseur échoué: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Construit le payload de test (ping)
     */
    private function buildPingPayload(array $provider): array
    {
        if ($provider['type'] === 'gemini') {
            return [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'ping']
                        ]
                    ]
                ],
                'generationConfig' => ['maxOutputTokens' => 5]
            ];
        }

        // Pour Groq et OpenRouter
        $payload = [
            'model' => $provider['model'],
            'messages' => [
                ['role' => 'user', 'content' => 'ping']
            ],
            'max_tokens' => 5,
        ];

        // Groq ne supporte pas response_format
        if ($provider['type'] !== 'groq') {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        return $payload;
    }

    public function optimize(float $amount, array $userMethodIds, string $countryCode = 'BJ', string $type = 'withdrawal'): array
    {
        Log::info('🤖 IAFeeOptimizer appelé', [
            'amount' => $amount,
            'country' => $countryCode,
            'type' => $type,
            'provider' => $this->currentProvider['type'],
            'model' => $this->currentProvider['model'],
        ]);

        try {
            $methods = Method::whereIn('id', $userMethodIds)
                ->where(function ($query) use ($countryCode) {
                    $query->where('country_code', $countryCode)
                          ->orWhereNull('country_code');
                })
                ->whereRaw('is_active = true')
                ->with(['receiptFees', 'sendingFees'])
                ->get();

            if ($methods->isEmpty()) {
                return ['error' => 'Aucune méthode disponible'];
            }

            $feesData = $this->getCurrentFees($methods, $amount, $type);
            $paliersInfo = $this->getPaliersInfo($methods, $type);
            $prompt = $this->buildOptimizationPrompt($amount, $feesData, $paliersInfo, $type);

            // Essayer avec le fournisseur courant
            $response = $this->callProvider($prompt, $this->currentProvider);

            // Si le fournisseur échoue, essayer les suivants
            if (!$response) {
                Log::warning('⚠️ Fournisseur ' . $this->currentProvider['type'] . ' a échoué, tentative avec le suivant...');
                $fallbackProvider = $this->getFallbackProvider($this->currentProvider);
                if ($fallbackProvider) {
                    $this->currentProvider = $fallbackProvider;
                    Log::info('🔄 Fallback vers: ' . $this->currentProvider['type'] . ' (' . $this->currentProvider['model'] . ')');
                    $response = $this->callProvider($prompt, $this->currentProvider);
                }
            }

            if (!$response) {
                Log::error('❌ Tous les fournisseurs ont échoué');
                return ['error' => 'L\'IA n\'a pas pu traiter la demande (tous les fournisseurs indisponibles)'];
            }

            Log::info('✅ IAFeeOptimizer terminé avec succès (provider: ' . $this->currentProvider['type'] . ')');
            return $this->parseAIResponse($response, $amount);

        } catch (\Exception $e) {
            Log::error('IAFeeOptimizer error: ' . $e->getMessage());
            return ['error' => 'Erreur lors de l\'optimisation IA: ' . $e->getMessage()];
        }
    }

    /**
     * Appelle un fournisseur spécifique
     */
    private function callProvider(string $prompt, array $provider): ?array
    {
        try {
            $apiKey = match ($provider['type']) {
                'groq' => $this->groqApiKey,
                'gemini' => $this->geminiApiKey,
                'openrouter' => $this->openRouterApiKey,
                default => null,
            };

            if (empty($apiKey)) {
                Log::error('Clé API manquante pour ' . $provider['type']);
                return null;
            }

            $headers = ['Content-Type' => 'application/json'];
            if (in_array($provider['type'], ['groq', 'openrouter'])) {
                $headers['Authorization'] = 'Bearer ' . $apiKey;
            }
            if ($provider['type'] === 'openrouter') {
                $headers['HTTP-Referer'] = config('app.url');
                $headers['X-Title'] = 'MomoOpti';
            }

            $payload = $this->buildProviderPayload($prompt, $provider);
            $response = Http::timeout(20)->withHeaders($headers)->post($provider['url'], $payload);

            if (!$response->successful()) {
                Log::error('API error (' . $provider['type'] . '): ' . $response->body());
                return null;
            }

            $data = $response->json();
            return $this->extractContent($data, $provider);

        } catch (\Exception $e) {
            Log::error('Provider call exception (' . $provider['type'] . '): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Construit le payload pour un fournisseur
     */
    private function buildProviderPayload(string $prompt, array $provider): array
    {
        if ($provider['type'] === 'gemini') {
            return [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                    'maxOutputTokens' => 1000,
                ]
            ];
        }

        // Pour Groq (et OpenRouter), on utilise le format OpenAI
        $payload = [
            'model' => $provider['model'],
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.1,
            'max_tokens' => 500,
        ];

        // Groq ne supporte pas response_format comme OpenAI
        if ($provider['type'] !== 'groq') {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        return $payload;
    }

    /**
     * Extrait le contenu de la réponse
     */
    private function extractContent(array $data, array $provider): ?array
    {
        if ($provider['type'] === 'gemini') {
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            preg_match('/\{[\s\S]*\}/', $text, $matches);
            return !empty($matches) ? json_decode($matches[0], true) : null;
        }

        $content = $data['choices'][0]['message']['content'] ?? '';
        preg_match('/\{[\s\S]*\}/', $content, $matches);
        return !empty($matches) ? json_decode($matches[0], true) : null;
    }

    /**
     * Retourne le prochain fournisseur disponible
     */
    private function getFallbackProvider(array $currentProvider): ?array
    {
        $sortedProviders = $this->providers;
        uasort($sortedProviders, fn($a, $b) => $a['priority'] <=> $b['priority']);

        $found = false;
        foreach ($sortedProviders as $provider) {
            if ($found && $this->testProviderAvailability($provider)) {
                return $provider;
            }
            if ($provider['type'] === $currentProvider['type'] && $provider['model'] === $currentProvider['model']) {
                $found = true;
            }
        }

        return null;
    }

    protected function getCurrentFees($methods, float $amount, string $type): array
    {
        $result = [];
        foreach ($methods as $method) {
            $fee = $this->getFeeForAmount(
                $type === 'withdrawal' ? $method->receiptFees : $method->sendingFees,
                $amount
            );
            $result[] = [
                'name' => $method->name,
                'code' => $method->code,
                'fee' => $fee,
                'color' => $method->color_primary ?? '#888888',
                'category' => $method->category,
            ];
        }
        return $result;
    }

    protected function getFeeForAmount($fees, float $amount): float
    {
        foreach ($fees as $fee) {
            if ($amount >= $fee->min_amount && $amount <= $fee->max_amount) {
                return (float) $fee->fee_amount;
            }
        }
        return 0.0;
    }

    protected function getPaliersInfo($methods, string $type): string
    {
        $feeRelation = $type === 'withdrawal' ? 'receiptFees' : 'sendingFees';
        $info = "Voici les paliers de frais pour chaque méthode :\n";
        foreach ($methods as $method) {
            $info .= "\n**{$method->name}** (catégorie: {$method->category}) :\n";
            $fees = $method->$feeRelation->sortBy('min_amount');
            if ($fees->isEmpty()) {
                $info .= "  - Aucun palier disponible\n";
                continue;
            }
            foreach ($fees as $fee) {
                $info .= "  - {$fee->min_amount} à {$fee->max_amount} FCFA : frais de {$fee->fee_amount} FCFA\n";
            }
        }
        return $info;
    }

    protected function buildOptimizationPrompt(float $amount, array $feesData, string $paliersInfo, string $type): string
    {
        $typeLabel = $type === 'withdrawal' ? 'retrait' : 'envoi';
        $action = $type === 'withdrawal' ? 'retirer' : 'envoyer';

        $feesTable = '';
        foreach ($feesData as $data) {
            $feesTable .= "- **{$data['name']}** (catégorie: {$data['category']}) : frais = {$data['fee']} FCFA\n";
        }

        return <<<PROMPT
Tu es un expert en optimisation des frais de {$typeLabel}. L'utilisateur souhaite {$action} **{$amount} FCFA**.

Voici les méthodes disponibles avec leurs frais pour ce montant (calculés à partir des paliers fournis) :

{$feesTable}

{$paliersInfo}

**RÈGLES IMPORTANTES** :
1. Tu **ne dois PAS** proposer de combinaison entre des méthodes de catégories différentes. Exemple:
   - ❌ Mobile Money (MTN) + International (Wise) → INTERDIT
   - ❌ Mobile Money (Moov) + Banque (Société Générale) → INTERDIT
   - ✅ Mobile Money + Mobile Money (MTN + Moov) → AUTORISÉ
   - ✅ International + International (Wise + PayPal) → AUTORISÉ

2. Si aucune combinaison valide n'est possible, propose uniquement des options "seul" ou "fractionné".

**Objectif** : Proposer **5 options** classées de la meilleure (moins de frais) à la moins bonne.

**Format de réponse** (JSON) :
{
  "best_option": {
    "label": "Nom de l'option",
    "details": [
      {"network": "Celtiis", "category": "mobile_money", "amount": 100000, "fee": 1000}
    ],
    "total_fee": 1000,
    "net_amount": 154000
  },
  "alternatives": [
    {
      "label": "Alternative 1",
      "details": [...],
      "total_fee": ...,
      "net_amount": ...
    }
  ],
  "savings": 280,
  "explanation": "Explication concise en français.",
  "tips": ["Conseil 1", "Conseil 2"]
}

**IMPORTANT** :
- Ne fournis que le JSON.
- Les montants dans les détails doivent additionner exactement {$amount} FCFA.
- Propose **5 options au total** (1 meilleure + 4 alternatives).

PROMPT;
    }

    protected function parseAIResponse(?array $aiResponse, float $amount): array
    {
        if (!$aiResponse || empty($aiResponse['best_option'])) {
            return ['error' => 'L\'IA n\'a pas pu fournir une optimisation valide'];
        }

        $aiResponse = $this->filterInvalidCombinations($aiResponse, $amount);

        // Générer le contenu "Blague / Actu / Anecdote"
        $funContent = $this->generateFunContent();

        $best = [
            'label' => $aiResponse['best_option']['label'] ?? 'Option',
            'details' => $aiResponse['best_option']['details'] ?? [],
            'fee' => $aiResponse['best_option']['total_fee'] ?? 0,
            'net' => $aiResponse['best_option']['net_amount'] ?? $amount,
        ];

        $alternatives = [];
        foreach ($aiResponse['alternatives'] ?? [] as $alt) {
            $alternatives[] = [
                'label' => $alt['label'] ?? 'Alternative',
                'details' => $alt['details'] ?? [],
                'fee' => $alt['total_fee'] ?? 0,
                'net' => $alt['net_amount'] ?? $amount,
            ];
        }

        return [
            'amount' => (int) round($amount),
            'best' => $best,
            'alternatives' => $alternatives,
            'savings' => $aiResponse['savings'] ?? 0,
            'fun_content' => $funContent,
            'is_ai' => false, // On masque le badge IA
        ];
    }

    private function filterInvalidCombinations(array $response, float $amount): array
    {
        if (isset($response['best_option']['details']) && count($response['best_option']['details']) > 1) {
            if (!$this->isCombinationValid($response['best_option']['details'])) {
                $firstDetail = $response['best_option']['details'][0];
                $response['best_option']['details'] = [$firstDetail];
                $response['best_option']['total_fee'] = $firstDetail['fee'] ?? 0;
                $response['best_option']['net_amount'] = $amount - ($firstDetail['fee'] ?? 0);
                $response['best_option']['label'] = ($firstDetail['network'] ?? 'Option') . ' seul';
            }
        }

        if (isset($response['alternatives'])) {
            $response['alternatives'] = array_filter($response['alternatives'], function ($alt) {
                if (isset($alt['details']) && count($alt['details']) > 1) {
                    return $this->isCombinationValid($alt['details']);
                }
                return true;
            });
            $response['alternatives'] = array_values($response['alternatives']);
        }

        return $response;
    }

    private function isCombinationValid(array $details): bool
    {
        if (count($details) < 2) {
            return true;
        }

        $categories = [];
        foreach ($details as $detail) {
            if (isset($detail['category'])) {
                $categories[] = $detail['category'];
            } else {
                $networkName = $detail['network'] ?? '';
                $method = Method::where('name', $networkName)->first();
                if ($method) {
                    $categories[] = $method->category;
                } else {
                    return false;
                }
            }
        }

        $firstCategory = $categories[0];
        foreach ($categories as $cat) {
            if ($cat !== $firstCategory) {
                return false;
            }
        }

        return true;
    }

    public function isAvailable(): bool
    {
        foreach ($this->providers as $provider) {
            if ($this->testProviderAvailability($provider)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Génère une blague, une actualité ou une anecdote du jour
     */
    private function generateFunContent(): string
    {
        // Phrases de secours
        $fallbackPhrases = [
            "Saviez-vous que le premier transfert d'argent mobile a été effectué au Kenya en 2007 ?",
            "Le Bénin compte plus de 5 millions d'utilisateurs de Mobile Money.",
            "En Afrique, le Mobile Money représente plus de 60% des transactions financières.",
            "La fintech africaine attire des investissements record chaque année.",
            "Le Nigeria est le plus grand marché de Mobile Money en Afrique.",
        ];
        
        try {
            $prompt = "Donne-moi UNE seule chose parmi les suivantes (au hasard) :
            - Une blague courte et drôle (en français)
            - Une actualité récente dans le monde de la finance, de la tech ou des startups
            - Une anecdote intéressante sur l'entrepreneuriat, la fintech ou l'Afrique
            - Une information sur une startup africaine qui a levé des fonds
            - Une découverte ou innovation technologique récente
            
            Réponds en UNE seule phrase courte et impactante, sans mentionner que c'est une blague ou une actualité. Sois naturel et engageant.";

            $response = $this->callProvider($prompt, $this->currentProvider);
            
            if ($response && isset($response['content']) && !empty($response['content'])) {
                return $response['content'];
            }
            
            return $fallbackPhrases[array_rand($fallbackPhrases)];
        } catch (\Exception $e) {
            Log::warning('Impossible de générer le contenu fun: ' . $e->getMessage());
            return $fallbackPhrases[array_rand($fallbackPhrases)];
        }
    }
}