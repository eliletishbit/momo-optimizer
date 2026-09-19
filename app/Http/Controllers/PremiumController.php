<?php

namespace App\Http\Controllers;

use FedaPay\FedaPay;
use FedaPay\Transaction;
use App\Models\Subscription;
use App\Models\User;
use App\Models\OptimizationHistory;
use Carbon\Carbon;
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
     * Affiche le tableau de bord décisionnel (Analytics Pro).
     */
    public function decisionalDashboard(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // 🔒 Contrôle d'accès strict : réservé aux abonnés Pro, Business et Administrateurs
        if (!$user->is_admin && !$user->hasActivePro() && !$user->hasActiveBusiness()) {
            return redirect()->route('pricing')->with('error', 'Veuillez passer à l\'abonnement Pro pour utiliser cette fonctionnalité.');
        }

        // 1. Statistiques globales d'optimisation
        $totalVolume = (float) OptimizationHistory::where('user_id', $user->id)->sum('amount');
        $totalSavings = (float) OptimizationHistory::where('user_id', $user->id)->sum('savings');
        $totalOptimizations = (int) OptimizationHistory::where('user_id', $user->id)->count();
        $avgSavingsRate = $totalVolume > 0 ? round(($totalSavings / $totalVolume) * 100, 2) : 0;

        // 2. Dernières optimisations
        $recentOptimizations = OptimizationHistory::where('user_id', $user->id)
            ->with('selectedMethod')
            ->latest()
            ->take(6)
            ->get();

        // 3. Répartition par réseau (basée sur les méthodes choisies ou méthodes de l'utilisateur)
        $networkDistribution = [];
        $userMethods = $user->userMethods()->with('method')->get();

        $networkColors = [
            'MTN' => ['bg' => 'bg-yellow-400', 'color' => '#EAB308'],
            'Moov' => ['bg' => 'bg-blue-500', 'color' => '#3B82F6'],
            'Celtiis' => ['bg' => 'bg-emerald-500', 'color' => '#10B981'],
            'Orange' => ['bg' => 'bg-orange-500', 'color' => '#F97316'],
            'T-Money' => ['bg' => 'bg-red-500', 'color' => '#EF4444'],
            'Wave' => ['bg' => 'bg-sky-400', 'color' => '#38BDF8'],
        ];

        $totalMethods = $userMethods->count();
        if ($totalMethods > 0) {
            $grouped = $userMethods->groupBy(function ($um) {
                $name = $um->method ? $um->method->name : 'Autre';
                foreach (['MTN', 'Moov', 'Celtiis', 'Orange', 'T-Money', 'Wave'] as $net) {
                    if (stripos($name, $net) !== false) {
                        return $net;
                    }
                }
                return 'Autre';
            });

            foreach ($grouped as $net => $items) {
                $pct = round(($items->count() / $totalMethods) * 100);
                $networkDistribution[] = [
                    'name' => $net,
                    'percentage' => $pct,
                    'count' => $items->count(),
                    'class' => $networkColors[$net]['bg'] ?? 'bg-indigo-500',
                ];
            }
        } else {
            // Répartition par défaut pour la démo visuelle
            $networkDistribution = [
                ['name' => 'MTN MoMo', 'percentage' => 45, 'count' => 0, 'class' => 'bg-yellow-400'],
                ['name' => 'Moov Money', 'percentage' => 35, 'count' => 0, 'class' => 'bg-blue-500'],
                ['name' => 'Celtiis Cash', 'percentage' => 20, 'count' => 0, 'class' => 'bg-emerald-500'],
            ];
        }

        // 4. Données mensuelles (6 derniers mois)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthName = $monthDate->translatedFormat('M Y');

            $mVolume = (float) OptimizationHistory::where('user_id', $user->id)
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->sum('amount');

            $mSavings = (float) OptimizationHistory::where('user_id', $user->id)
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->sum('savings');

            $monthlyTrends[] = [
                'month' => $monthName,
                'volume' => $mVolume,
                'savings' => $mSavings,
            ];
        }

        // 5. Statistiques opérateur UNIQUEMENT si l'utilisateur est un opérateur actif
        $isOperator = $user->isOperator();
        $operatorProfile = $isOperator ? $user->operatorProfile : null;
        $operatorStats = null;
        if ($isOperator && $operatorProfile) {
            $todayOps = \App\Models\OperationOperateur::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->get();

            $operatorStats = [
                'caisse_physique' => $operatorProfile->caisse_physique,
                'caisse_virtuelle' => $operatorProfile->caisse_virtuelle,
                'total_caisses' => $operatorProfile->caisse_physique + $operatorProfile->caisse_virtuelle,
                'today_entrants' => $todayOps->where('direction', 'entrant')->sum('montant'),
                'today_sortants' => $todayOps->where('direction', 'sortant')->sum('montant'),
                'today_count' => $todayOps->count(),
            ];
        }

        return view('pages.premium.analytics', compact(
            'totalVolume',
            'totalSavings',
            'totalOptimizations',
            'avgSavingsRate',
            'recentOptimizations',
            'networkDistribution',
            'monthlyTrends',
            'operatorStats',
            'operatorProfile',
            'isOperator'
        ));
    }

    /**
     * Alias de rétrocompatibilité pour analytics.
     */
    public function analytics(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        return $this->decisionalDashboard($request);
    }

    /**
     * Affiche le bilan mensuel d'aide à la décision Pro et gère l'export CSV.
     * Dédié aux entreprises et particuliers qui optimisent leurs frais de transfert.
     */
    public function bilan(Request $request)
    {
        $user = Auth::user();

        // 🔒 Contrôle d'accès strict : réservé aux abonnés Pro, Business et Administrateurs
        if (!$user->is_admin && !$user->hasActivePro() && !$user->hasActiveBusiness()) {
            return redirect()->route('pricing')->with('error', 'Veuillez passer à l\'abonnement Pro pour utiliser cette fonctionnalité.');
        }

        // Mois sélectionné (ex: 2026-09)
        $selectedMonth = $request->get('month', now()->format('Y-m'));
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth);
        } catch (\Throwable $e) {
            $monthDate = now();
            $selectedMonth = $monthDate->format('Y-m');
        }

        $startDate = $monthDate->copy()->startOfMonth();
        $endDate = $monthDate->copy()->endOfMonth();

        // 1. Optimisations et arbitrages réalisés durant le mois
        $optimizations = OptimizationHistory::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('selectedMethod')
            ->orderBy('created_at', 'desc')
            ->get();

        $optVolume = (float) $optimizations->sum('amount');
        $optSavings = (float) $optimizations->sum('savings');
        $optFees = (float) $optimizations->sum('total_fee');
        $optCount = $optimizations->count();
        $avgSavingsRate = $optVolume > 0 ? round(($optSavings / $optVolume) * 100, 2) : 0;

        // 2. Analyse de performance et répartition par réseau
        $networkBreakdown = [];
        $groupedByNetwork = $optimizations->groupBy(function ($opt) {
            $methodName = $opt->selectedMethod ? $opt->selectedMethod->name : 'Autre';
            foreach (['MTN', 'Moov', 'Celtiis', 'Orange', 'T-Money', 'Wave'] as $net) {
                if (stripos($methodName, $net) !== false) {
                    return $net;
                }
            }
            return $methodName ?: 'Général';
        });

        foreach ($groupedByNetwork as $netName => $group) {
            $netVol = (float) $group->sum('amount');
            $netFees = (float) $group->sum('total_fee');
            $netSav = (float) $group->sum('savings');
            $networkBreakdown[] = [
                'name' => $netName,
                'count' => $group->count(),
                'volume' => $netVol,
                'fees' => $netFees,
                'savings' => $netSav,
                'percentage' => $optVolume > 0 ? round(($netVol / $optVolume) * 100, 1) : 0,
            ];
        }

        // 3. Export CSV d'aide à la décision financière (sans opérations de caisse opérateur)
        if ($request->get('export') === 'csv') {
            $fileName = "bilan-aide-decision-momoopti-{$selectedMonth}.csv";
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ];

            return response()->stream(function () use ($optimizations, $selectedMonth, $optVolume, $optFees, $optSavings, $optCount, $avgSavingsRate, $user) {
                $handle = fopen('php://output', 'w');
                // BOM UTF-8 pour ouverture correcte dans Excel
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($handle, ["MOMOOPTI - BILAN D'AIDE À LA DÉCISION & OPTIMISATION FINANCIÈRE"]);
                fputcsv($handle, ["Période : {$selectedMonth}", "Généré pour : {$user->name} ({$user->email})", "Date d'export : " . now()->format('d/m/Y H:i')]);
                fputcsv($handle, []);

                fputcsv($handle, ['--- RÉSUMÉ EXÉCUTIF DU MOIS ---']);
                fputcsv($handle, ['Indicateur', 'Valeur']);
                fputcsv($handle, ['Volume total optimisé (FCFA)', $optVolume]);
                fputcsv($handle, ['Frais optimisés payés (FCFA)', $optFees]);
                fputcsv($handle, ['Économies nettes générées (FCFA)', $optSavings]);
                fputcsv($handle, ['Taux d\'économie global (%)', $avgSavingsRate . '%']);
                fputcsv($handle, ['Nombre total de transactions analysées', $optCount]);
                fputcsv($handle, []);

                fputcsv($handle, ['--- JOURNAL DÉTAILLÉ DES TRANSACTIONS AUDITÉES ---']);
                fputcsv($handle, ['Date & Heure', 'Opération', 'Montant (FCFA)', 'Frais payés (FCFA)', 'Économie nette (FCFA)', 'Solution retenue', 'Statut']);
                foreach ($optimizations as $opt) {
                    fputcsv($handle, [
                        $opt->created_at->format('d/m/Y H:i'),
                        $opt->type === 'sending' ? 'Envoi d\'argent' : 'Retrait / Réception',
                        $opt->amount,
                        $opt->total_fee,
                        $opt->savings,
                        $opt->selectedMethod ? $opt->selectedMethod->name : 'Optimisé',
                        'Optimisé avec succès',
                    ]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        return view('pages.premium.bilan', compact(
            'selectedMonth',
            'monthDate',
            'optimizations',
            'optVolume',
            'optSavings',
            'optFees',
            'optCount',
            'avgSavingsRate',
            'networkBreakdown'
        ));
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