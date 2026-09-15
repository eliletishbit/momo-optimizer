<?php

namespace App\Http\Controllers;

use FedaPay\FedaPay;
use FedaPay\Transaction;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PremiumController extends Controller
{
    public function __construct()
    {
        // Configuration FedaPay
        $apiKey = config('services.fedapay.api_key');
        FedaPay::setApiKey($apiKey);

        $mode = config('services.fedapay.mode', 'live');
        FedaPay::setEnvironment($mode);

        $accountId = config('services.fedapay.account_id');
        if ($accountId) {
            FedaPay::setAccountId($accountId);
        }
    }

    /**
     * Affiche le calculateur avancé (multi-réseaux).
     */
    public function advancedCalculator(): View
    {
        return view('pages.premium.advanced-calculator');
    }

    /**
     * Gère l'export des résultats en PDF/CSV.
     */
    public function export(): View
    {
        return view('pages.premium.export');
    }

    /**
     * Affiche la gestion des alertes.
     */
    public function alerts(): View
    {
        return view('pages.premium.alerts');
    }

    /**
     * Affiche les statistiques avancées d'économies.
     */
    public function analytics(): View
    {
        return view('pages.premium.analytics');
    }

    /**
     * Affiche le bilan mensuel.
     */
    public function bilan(): View
    {
        return view('pages.premium.bilan');
    }

    /**
     * CHECKOUT : Crée une transaction FedaPay et redirige vers le paiement.
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $plan = $request->query('plan');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour souscrire.');
        }

        $validPlans = ['premium', 'pro', 'pay_as_you_go'];
        if (!in_array($plan, $validPlans)) {
            abort(404, 'Plan non valide.');
        }

        $planData = [
            'premium' => [
                'amount' => 1000,
                'description' => 'Abonnement Premium MomoOpti - 1 mois',
                'days' => 30,
            ],
            'pro' => [
                'amount' => 5000,
                'description' => 'Abonnement Pro MomoOpti - 1 mois',
                'days' => 30,
            ],
            'pay_as_you_go' => [
                'amount' => 100,
                'description' => 'Pack Pay As You Go - 5 utilisations',
                'days' => 30,
                'credits' => 5,
            ],
        ];

        try {
            $transaction = Transaction::create([
                'description' => $planData[$plan]['description'],
                'amount' => $planData[$plan]['amount'],
                'currency' => ['iso' => 'XOF'],
                'callback_url' => route('subscription.callback'),
                'mode' => 'mtn',
                'customer' => [
                    'firstname' => $user->name ?? 'Client',
                    'lastname' => '',
                    'email' => $user->email,
                    'phone_number' => [
                        'number' => $user->phone ?? '+22966000000',
                        'country' => 'bj',
                    ],
                ],
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => $plan,
                ],
            ]);

            $paymentUrl = $transaction->payment_url;
            if (empty($paymentUrl)) {
                Log::error('FedaPay : URL de paiement vide pour la transaction ' . $transaction->id);
                return back()->with('error', '❌ URL de paiement non disponible. Veuillez réessayer.');
            }

            session(['fedapay_transaction_id' => $transaction->id]);

            return redirect()->to($paymentUrl);

        } catch (\Exception $e) {
            Log::error('FedaPay checkout error: ' . $e->getMessage());
            return back()->with('error', '❌ Erreur lors de la création du paiement : ' . $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        Log::info('Callback reçu avec : ' . json_encode($request->all()));

        $transactionId = $request->input('transaction_id')
            ?? $request->query('transaction_id')
            ?? session('fedapay_transaction_id');

        if (!$transactionId) {
            return redirect()->route('pricing')->with('error', '❌ Aucune transaction trouvée.');
        }

        try {
            $transaction = Transaction::retrieve($transactionId);

            if (in_array($transaction->status, ['completed', 'approved', 'authorized'])) {
                $this->activateSubscription($transaction);
                session()->forget('fedapay_transaction_id');
                return redirect()->route('dashboard')->with('success', '✅ Abonnement activé avec succès !');
            }

            if (in_array($transaction->status, ['pending', 'processing', 'transferred'])) {
                session(['fedapay_transaction_pending' => $transactionId]);
                return redirect()->route('pricing')->with('info', '⏳ Votre paiement est en cours de validation.');
            }

            return redirect()->route('pricing')->with('error', 'Le paiement n\'a pas abouti. Statut : ' . $transaction->status);

        } catch (\Exception $e) {
            Log::error('Callback error : ' . $e->getMessage());
            return redirect()->route('pricing')->with('error', '❌ Erreur lors de la validation.');
        }
    }

    /**
     * ✅ Active l'abonnement après paiement réussi
     */
    private function activateSubscription($transaction)
    {
        $metadata = $transaction->metadata;
        $userId = $metadata['user_id'] ?? null;
        $plan = $metadata['plan'] ?? 'premium';

        if (!$userId) {
            Log::error('activateSubscription: user_id manquant');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            Log::error('activateSubscription: Utilisateur non trouvé : ' . $userId);
            return;
        }

        // ✅ Vérifier si déjà activé
        if (Subscription::where('transaction_id', $transaction->id)->exists()) {
            Log::info('activateSubscription: Abonnement déjà activé pour la transaction ' . $transaction->id);
            return;
        }

        // ✅ Durées et crédits par plan
        $planConfig = [
            'premium' => ['days' => 30, 'credits' => 0],
            'pro' => ['days' => 30, 'credits' => 0],
            'pay_as_you_go' => ['days' => 30, 'credits' => 5],
        ];

        $days = $planConfig[$plan]['days'] ?? 30;
        $credits = $planConfig[$plan]['credits'] ?? 0;

        // ✅ ACTIVER L'ABONNEMENT (utilise la méthode du modèle User)
        $subscription = $user->activateSubscription($plan, $days, (float) $transaction->amount, $transaction->id, 'fedapay');

        // ✅ Si Pay As You Go, ajouter les crédits
        if ($plan === 'pay_as_you_go' && $credits > 0) {
            $user->payg_credits = $credits;
            $user->save();
            
            // Mettre à jour l'abonnement avec les crédits
            $subscription->uses_remaining = $credits;
            $subscription->save();
        }

        Log::info('✅ Abonnement activé pour l\'utilisateur ' . $user->id . ' (plan ' . $plan . ')');
    }

    /**
     * WEBHOOK : Notification automatique de FedaPay
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();

        Log::info('Webhook FedaPay reçu', $payload);

        if (($payload['event'] ?? '') === 'transaction.succeeded') {
            $transactionData = $payload['data'];
            $fedapayTransactionId = $transactionData['id'];

            $metadata = $transactionData['metadata'] ?? [];
            $userId = $metadata['user_id'] ?? null;
            $plan = $metadata['plan'] ?? 'premium';

            if (!$userId) {
                Log::error('Webhook : user_id manquant');
                return response()->json(['status' => 'error', 'message' => 'User ID missing'], 400);
            }

            $user = User::find($userId);
            if (!$user) {
                Log::error('Webhook : Utilisateur non trouvé : ' . $userId);
                return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            }

            if (Subscription::where('transaction_id', $fedapayTransactionId)->exists()) {
                return response()->json(['status' => 'already_processed']);
            }

            // ✅ Utiliser la méthode du modèle User
            $planConfig = [
                'premium' => ['days' => 30, 'credits' => 0],
                'pro' => ['days' => 30, 'credits' => 0],
                'pay_as_you_go' => ['days' => 30, 'credits' => 5],
            ];

            $days = $planConfig[$plan]['days'] ?? 30;
            $credits = $planConfig[$plan]['credits'] ?? 0;
            $amount = $transactionData['amount'] ?? 0;

            $subscription = $user->activateSubscription($plan, $days, (float) $amount, $fedapayTransactionId, 'fedapay');

            if ($plan === 'pay_as_you_go' && $credits > 0) {
                $user->payg_credits = $credits;
                $user->save();
                $subscription->uses_remaining = $credits;
                $subscription->save();
            }

            Log::info('Webhook : Abonnement activé pour l\'utilisateur ' . $user->id . ' (plan ' . $plan . ')');
        }

        return response()->json(['status' => 'ok']);
    }
}