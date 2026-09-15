<?php

namespace App\Services;

use App\Models\Method;
use App\Models\ReceiptFee;
use App\Models\SendingFee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class VeilleTarifaireIA
{
    protected array $providers;
    protected array $activeProviders = [];

    public function __construct()
    {
        $this->providers = [
            // 🔥 GROQ - Priorité 1 (MODÈLES MIS À JOUR)
            'groq' => [
                'type' => 'groq',
                'models' => [
                    'llama-3.3-70b-versatile',    // ✅ Nouveau modèle
                    'llama-3.1-8b-instant',        // ✅ Modèle rapide
                    'mixtral-8x7b-32768',          // ✅ Encore supporté
                    'gemma2-9b-it',                 // ✅ Encore supporté
                ],
                'url' => 'https://api.groq.com/openai/v1/chat/completions',
                'priority' => 1,
                'api_key' => config('services.groq.api_key'),
            ],
            // 🥈 GEMINI - Priorité 2 (MODÈLES MIS À JOUR)
            'gemini' => [
                'type' => 'gemini',
                'models' => [
                    'gemini-3.6-flash',            // ✅ Nouveau modèle
                    'gemini-3.5-flash-lite',       // ✅ Nouveau modèle
                    'gemini-2.5-flash',            // ✅ Version plus stable
                ],
                'url' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
                'priority' => 2,
                'api_key' => config('services.gemini.api_key'),
            ],
            // 🥉 OPENROUTER - Priorité 3 (MODÈLES MIS À JOUR)
            'openrouter' => [
                'type' => 'openrouter',
                'models' => [
                    'google/gemini-3.6-flash:free',  // ✅ Nouveau modèle gratuit
                    'meta-llama/llama-3.3-70b-instruct:free', // ✅ Nouveau
                    'deepseek/deepseek-r1:free',      // ✅ Nouveau modèle gratuit
                    'qwen/qwen-2.5-72b-instruct:free', // ✅ Nouveau
                ],
                'url' => 'https://openrouter.ai/api/v1/chat/completions',
                'priority' => 3,
                'api_key' => config('services.openrouter.api_key'),
            ],
        ];

        // Filtrer les providers avec une clé API valide
        $this->activeProviders = array_filter($this->providers, function ($provider) {
            return !empty($provider['api_key']);
        });

        Log::info('VeilleTarifaireIA initialisée avec ' . count($this->activeProviders) . ' provider(s) actif(s)');
    }

    /**
     * Exécute la veille tarifaire pour toutes les méthodes actives
     */
    public function run(): void
    {
        // ✅ CORRECTION POUR POSTGRESQL
        $methods = Method::whereRaw('is_active = true')->get();
        
        if ($methods->isEmpty()) {
            Log::warning('VeilleTarifaireIA: Aucune méthode active trouvée.');
            return;
        }

        Log::info('🔄 VeilleTarifaireIA: Début de la mise à jour pour ' . $methods->count() . ' méthodes.');

        foreach ($methods as $method) {
            $this->updateFeesForMethod($method);
        }

        Log::info('✅ VeilleTarifaireIA: Mise à jour terminée.');
    }

    /**
     * Met à jour les frais pour une méthode spécifique
     */
    public function updateFeesForMethod(Method $method): void
    {
        try {
            Log::info("VeilleTarifaireIA: Traitement de {$method->name}...");

            $feesData = $this->fetchFeesFromIA($method);

            if (!$feesData) {
                Log::warning("VeilleTarifaireIA: Aucune donnée IA pour {$method->name}, utilisation du fallback");
                $feesData = $this->getFallbackFees($method);
            }

            if (isset($feesData['receipt']) && !empty($feesData['receipt'])) {
                $this->updateFees($method, $feesData['receipt'], 'receipt');
            }

            if (isset($feesData['sending']) && !empty($feesData['sending'])) {
                $this->updateFees($method, $feesData['sending'], 'sending');
            }

            Log::info("✅ VeilleTarifaireIA: Mise à jour réussie pour {$method->name}");
        } catch (\Exception $e) {
            Log::error("❌ VeilleTarifaireIA: Erreur critique pour {$method->name}: " . $e->getMessage());
        }
    }

    /**
     * Récupère les frais depuis l'IA avec fallback intelligent
     */
    protected function fetchFeesFromIA(Method $method): ?array
    {
        $cacheKey = 'veille_tarifaire_' . $method->id . '_' . date('Y-m-d');
        
        if (Cache::has($cacheKey)) {
            Log::info("VeilleTarifaireIA: Données en cache pour {$method->name}");
            return Cache::get($cacheKey);
        }

        $prompt = $this->buildPrompt($method);

        foreach ($this->activeProviders as $providerName => $providerConfig) {
            Log::info("VeilleTarifaireIA: Tentative avec {$providerName}...");

            foreach ($providerConfig['models'] as $model) {
                try {
                    Log::info("VeilleTarifaireIA: Essai du modèle {$model}");

                    $response = $this->callProvider($prompt, $providerConfig, $model);

                    if ($response && isset($response['receipt']) && isset($response['sending'])) {
                        $feesData = [
                            'receipt' => $this->normalizeTiers($response['receipt']),
                            'sending' => $this->normalizeTiers($response['sending']),
                        ];

                        Cache::put($cacheKey, $feesData, now()->addHours(24));
                        
                        Log::info("✅ VeilleTarifaireIA: Succès avec {$providerName}/{$model} pour {$method->name}");
                        return $feesData;
                    }

                    Log::warning("VeilleTarifaireIA: Réponse invalide de {$providerName}/{$model}");

                } catch (\Exception $e) {
                    Log::warning("VeilleTarifaireIA: Erreur avec {$providerName}/{$model}: " . $e->getMessage());
                    continue;
                }
            }

            Log::warning("VeilleTarifaireIA: Échec de tous les modèles pour {$providerName}");
        }

        return null;
    }

    /**
     * Appelle un provider spécifique
     */
    private function callProvider(string $prompt, array $providerConfig, string $model): ?array
    {
        try {
            $headers = ['Content-Type' => 'application/json'];
            
            if ($providerConfig['type'] === 'groq') {
                $headers['Authorization'] = 'Bearer ' . $providerConfig['api_key'];
                $payload = [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.1,
                    'max_tokens' => 300,
                ];
                $url = $providerConfig['url'];

            } elseif ($providerConfig['type'] === 'gemini') {
                $url = str_replace('{model}', $model, $providerConfig['url']) . '?key=' . $providerConfig['api_key'];
                $payload = [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 300,
                        'responseMimeType' => 'application/json',
                    ],
                ];

            } elseif ($providerConfig['type'] === 'openrouter') {
                $headers['Authorization'] = 'Bearer ' . $providerConfig['api_key'];
                $headers['HTTP-Referer'] = config('app.url');
                $headers['X-Title'] = 'MomoOpti';
                $payload = [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.1,
                    'max_tokens' => 300,
                ];
                $url = $providerConfig['url'];
            } else {
                Log::error("VeilleTarifaireIA: Type de provider inconnu: {$providerConfig['type']}");
                return null;
            }

            $response = Http::timeout(60)->withHeaders($headers)->post($url, $payload);

            if (!$response->successful()) {
                $status = $response->status();
                $body = $response->body();
                Log::error("VeilleTarifaireIA: HTTP {$status} - " . $body);
                
                if ($status === 429) {
                    sleep(5);
                }
                
                return null;
            }

            $data = $response->json();
            return $this->extractContent($data, $providerConfig['type']);

        } catch (\Exception $e) {
            Log::error("VeilleTarifaireIA: Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extrait le contenu JSON de la réponse du provider
     */
    private function extractContent(array $data, string $providerType): ?array
    {
        if ($providerType === 'gemini') {
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            return json_decode($text, true);
        }

        $content = $data['choices'][0]['message']['content'] ?? '';
        preg_match('/\{[\s\S]*\}/', $content, $matches);
        return !empty($matches) ? json_decode($matches[0], true) : null;
    }

    /**
     * Normalise les paliers de frais
     */
    private function normalizeTiers(array $tiers): array
    {
        $normalized = [];
        foreach ($tiers as $tier) {
            $normalized[] = [
                'min' => (float) ($tier['min'] ?? 0),
                'max' => (float) ($tier['max'] ?? 999999),
                'fee' => (float) ($tier['fee'] ?? 0),
                'type' => $tier['type'] ?? 'fixed',
            ];
        }
        return $normalized;
    }

    /**
     * Données de secours (fallback) en cas d'échec total
     */
    private function getFallbackFees(Method $method): array
    {
        if (in_array($method->category, ['mobile_money', 'transfer'])) {
            return [
                'receipt' => [
                    ['min' => 0, 'max' => 500, 'fee' => 50, 'type' => 'fixed'],
                    ['min' => 501, 'max' => 5000, 'fee' => 125, 'type' => 'fixed'],
                    ['min' => 5001, 'max' => 10000, 'fee' => 225, 'type' => 'fixed'],
                    ['min' => 10001, 'max' => 20000, 'fee' => 375, 'type' => 'fixed'],
                    ['min' => 20001, 'max' => 50000, 'fee' => 700, 'type' => 'fixed'],
                    ['min' => 50001, 'max' => 100000, 'fee' => 1000, 'type' => 'fixed'],
                    ['min' => 100001, 'max' => 200000, 'fee' => 2000, 'type' => 'fixed'],
                    ['min' => 200001, 'max' => 500000, 'fee' => 4000, 'type' => 'fixed'],
                    ['min' => 500001, 'max' => 999999, 'fee' => 5000, 'type' => 'fixed'],
                ],
                'sending' => [
                    ['min' => 0, 'max' => 500, 'fee' => 25, 'type' => 'fixed'],
                    ['min' => 501, 'max' => 5000, 'fee' => 65, 'type' => 'fixed'],
                    ['min' => 5001, 'max' => 10000, 'fee' => 115, 'type' => 'fixed'],
                    ['min' => 10001, 'max' => 20000, 'fee' => 190, 'type' => 'fixed'],
                    ['min' => 20001, 'max' => 50000, 'fee' => 350, 'type' => 'fixed'],
                    ['min' => 50001, 'max' => 100000, 'fee' => 500, 'type' => 'fixed'],
                    ['min' => 100001, 'max' => 200000, 'fee' => 1000, 'type' => 'fixed'],
                    ['min' => 200001, 'max' => 999999, 'fee' => 2000, 'type' => 'fixed'],
                ],
            ];
        }

        return [
            'receipt' => [
                ['min' => 0, 'max' => 999999, 'fee' => 100, 'type' => 'fixed'],
            ],
            'sending' => [
                ['min' => 0, 'max' => 999999, 'fee' => 50, 'type' => 'fixed'],
            ],
        ];
    }

    /**
     * Met à jour les frais en base de données
     */
    private function updateFees(Method $method, array $tiers, string $type): void
    {
        $model = $type === 'receipt' ? ReceiptFee::class : SendingFee::class;
        $countryCode = $method->country_code ?? 'INTERNATIONAL';

        // ✅ CORRECTION : Supprimer d'abord les anciens paliers
        $model::where('method_id', $method->id)
            ->where('country_code', $countryCode)
            ->delete();

        // Insérer les nouveaux paliers
        foreach ($tiers as $tier) {
            // ✅ CORRECTION : Tronquer le type à 10 caractères max
            $feeType = substr($tier['type'] ?? 'fixed', 0, 10);
            
            $model::create([
                'method_id' => $method->id,
                'country_code' => $countryCode,
                'min_amount' => $tier['min'],
                'max_amount' => $tier['max'],
                'fee_amount' => $tier['fee'],
                'fee_type' => $feeType, // ✅ Tronqué à 10 caractères
                'method_name' => $method->name,
            ]);
        }

        Log::info("VeilleTarifaireIA: Frais de {$type} mis à jour pour {$method->name} (" . count($tiers) . ' paliers)');
    }

    /**
     * Construit le prompt pour l'IA
     */
    private function buildPrompt(Method $method): string
    {
        $country = $method->country_code ?? 'International';
        $name = $method->name;
        $code = $method->code;

        return <<<PROMPT
Tu es un expert en collecte de données tarifaires pour les services de transfert d'argent.

Je veux les frais d'envoi et de retrait pour le service suivant :
- Nom : {$name}
- Code : {$code}
- Pays : {$country}

Retourne UNIQUEMENT un objet JSON valide avec cette structure EXACTE :
{
  "receipt": [
    {"min": 0, "max": 500, "fee": 50, "type": "fixed"}
  ],
  "sending": [
    {"min": 0, "max": 500, "fee": 25, "type": "fixed"}
  ]
}

Règles :
- Les montants sont en devise locale (FCFA, NGN, etc.)
- Le type de frais doit être "fixed" ou "percentage"
- Les paliers doivent couvrir de 0 à 999999
- Ne retourne que le JSON, sans commentaire
- Si tu ne connais pas les frais exacts, donne une estimation réaliste basée sur les frais standard pour ce type de service

PROMPT;
    }

    /**
     * Teste un provider spécifique (pour debug)
     */
    public function testProvider(string $providerName): array
    {
        if (!isset($this->activeProviders[$providerName])) {
            return ['success' => false, 'message' => "Provider {$providerName} non actif"];
        }

        $provider = $this->activeProviders[$providerName];
        $testPrompt = 'Retourne un JSON avec {"test": "ok", "timestamp": "' . date('Y-m-d H:i:s') . '"}';

        foreach ($provider['models'] as $model) {
            try {
                $response = $this->callProvider($testPrompt, $provider, $model);
                if ($response) {
                    return [
                        'success' => true,
                        'provider' => $providerName,
                        'model' => $model,
                        'response' => $response,
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return ['success' => false, 'message' => "Aucun modèle fonctionnel pour {$providerName}"];
    }
}