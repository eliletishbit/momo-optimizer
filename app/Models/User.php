<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    // =============================================
    // ✅ CONSTANTES
    // =============================================

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const FREE_TRIAL_DAYS = 21;
    public const DEGRADED_USES_PER_MONTH = 1;
    public const PAY_AS_YOU_GO_USES = 5;
    public const PAY_AS_YOU_GO_PRICE = 100;
    public const PAY_AS_YOU_GO_DURATION_DAYS = 30;

    // =============================================
    // ✅ PROPRIÉTÉS DU MODÈLE
    // =============================================

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_verified_at',
        'whatsapp_enabled',
        'password',
        'country_code',
        'preferred_receipt_methods',
        'preferred_sending_methods',
        'subscription',
        'is_admin',
        'subscription_expires_at',
        'preferences',
        'trial_used',
        'last_calculator_use_at',
        'calculator_use_count_month',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'subscription_expires_at' => 'datetime',
            'preferred_receipt_methods' => 'array',
            'preferred_sending_methods' => 'array',
            'preferences' => 'array',
            'is_admin' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'trial_used' => 'integer',
            'last_calculator_use_at' => 'datetime',
            'calculator_use_count_month' => 'integer',
            'password' => 'hashed',
        ];
    }

    // =============================================
    // ✅ BOOT & INITIALISATION
    // =============================================

    public function initializeFreeTrial(): void
    {
        if (!$this->trial_used && ($this->subscription === null || $this->subscription === 'free')) {
            $this->subscription = 'free';
            $this->subscription_expires_at = now()->addDays(self::FREE_TRIAL_DAYS);
            $this->trial_used = true;
        }
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $user) {
            $user->id ??= (string) Str::uuid();
            $user->calculator_use_count_month = 0;
            
            if ($user->trial_used === null || $user->trial_used === false) {
                $user->trial_used = true;
                $user->subscription = 'free';
                $user->subscription_expires_at = now()->addDays(self::FREE_TRIAL_DAYS);
            }
        });

        static::retrieved(function (self $user) {
            if ($user->subscription === 'free' && $user->subscription_expires_at === null && !$user->trial_used) {
                $user->trial_used = true;
                $user->subscription = 'free';
                $user->subscription_expires_at = now()->addDays(self::FREE_TRIAL_DAYS);
                $user->save();
            }
        });
    }

    // =============================================
    // ✅ RELATIONS
    // =============================================

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    public function userMethods(): HasMany
    {
        return $this->hasMany(UserMethod::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function optimizationHistory(): HasMany
    {
        return $this->hasMany(OptimizationHistory::class);
    }

    // =============================================
    // ✅ MÉTHODES ADMIN
    // =============================================

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    // =============================================
    // ✅ GESTION DES ABONNEMENTS (NOUVELLES MÉTHODES)
    // =============================================

    /**
     * ✅ Active un abonnement pour l'utilisateur (méthode principale)
     */
    public function activateSubscription(string $plan, int $days = 30, float $amount = 0, ?string $transactionId = null, ?string $paymentMethod = null): Subscription
    {
        // ✅ Si l'utilisateur était en essai gratuit, le marquer comme utilisé
        if ($this->subscription === 'free') {
            $this->trial_used = true;
        }

        // ✅ Réinitialiser le compteur mensuel
        $this->calculator_use_count_month = 0;
        $this->last_calculator_use_at = null;

        // ✅ Mettre à jour l'utilisateur
        $this->subscription = $plan;
        $this->subscription_expires_at = now()->addDays($days);
        $this->save();

        // ✅ Créer l'abonnement dans subscriptions
        return $this->subscriptions()->create([
            'plan' => $plan,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays($days),
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'amount_paid' => $amount,
            'currency' => 'XOF',
        ]);
    }

    /**
     * ✅ Active l'abonnement Pay As You Go
     */
    public function activatePayAsYouGo(int $credits = 5, int $days = 30, float $amount = 100, ?string $transactionId = null, ?string $paymentMethod = null): Subscription
    {
        $subscription = $this->activateSubscription('pay_as_you_go', $days, $amount, $transactionId, $paymentMethod);

        // ✅ Ajouter les crédits
        $this->payg_credits = $credits;
        $this->save();

        $subscription->uses_remaining = $credits;
        $subscription->save();

        return $subscription;
    }

    /**
     * ✅ Récupère le prix d'un plan
     */
    private function getPlanPrice(string $plan): float
    {
        return match ($plan) {
            'premium' => 1000,
            'pro' => 5000,
            'business' => 15000,
            'pay_as_you_go' => 100,
            default => 0,
        };
    }

    // =============================================
    // ✅ MÉTHODES POUR LES ABONNEMENTS PAYANTS
    // =============================================

    public function getActiveSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function hasActivePremium(): bool
    {
        return $this->hasActivePaidPlan('premium');
    }

    public function hasActivePro(): bool
    {
        return $this->hasActivePaidPlan('pro');
    }

    public function hasActiveBusiness(): bool
    {
        return $this->hasActivePaidPlan('business');
    }

    public function hasActivePayAsYouGo(): bool
    {
        return $this->hasActivePaidPlan('pay_as_you_go');
    }

    public function hasActivePaidPlan(string $plan): bool
    {
        // 1. Vérification sur le champ direct du modèle User
        if ($this->subscription === $plan) {
            return $this->subscription_expires_at === null || $this->subscription_expires_at->isFuture();
        }

        // 2. Vérification sur la souscription active la plus récente
        $latestActive = $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestActive && $latestActive->plan === $plan) {
            return true;
        }

        return false;
    }

    public function hasActivePaidSubscription(): bool
    {
        $paidPlans = ['premium', 'pro', 'business', 'pay_as_you_go'];
        
        $activeSubscription = $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->whereIn('plan', $paidPlans)
            ->first();

        if ($activeSubscription) {
            return true;
        }

        if (in_array($this->subscription, $paidPlans, true)) {
            if ($this->subscription_expires_at === null) {
                return false;
            }
            return $this->subscription_expires_at->isFuture();
        }

        return false;
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->hasActivePaidSubscription()) {
            return true;
        }

        if ($this->isOnFreeTrial() && $this->subscription_expires_at !== null) {
            return $this->subscription_expires_at->isFuture();
        }

        return false;
    }

    // =============================================
    // ✅ MÉTHODES POUR L'ESSAI GRATUIT
    // =============================================

    public function hasUsedFreeTrial(): bool
    {
        return (bool) $this->trial_used;
    }

    public function canUseFreeTrial(): bool
    {
        return !$this->hasUsedFreeTrial() && 
               !$this->hasActivePaidSubscription() && 
               $this->subscription === 'free';
    }

    public function hasActiveFreeTrial(): bool
    {
        if ($this->subscription !== 'free') {
            return false;
        }

        if ($this->subscription_expires_at === null) {
            return false;
        }

        return $this->subscription_expires_at->isFuture();
    }

    public function hasExpiredFreeTrial(): bool
    {
        if ($this->hasActivePaidSubscription()) {
            return false;
        }

        if ($this->subscription !== 'free') {
            return false;
        }

        if (!$this->hasUsedFreeTrial()) {
            return false;
        }

        if ($this->subscription_expires_at === null) {
            return false;
        }

        return $this->subscription_expires_at->isPast();
    }

    public function isOnFreeTrial(): bool
    {
        return $this->subscription === 'free';
    }

    public function getFreeTrialDaysLeft(): ?int
    {
        if ($this->subscription !== 'free' || $this->subscription_expires_at === null) {
            return null;
        }

        if ($this->subscription_expires_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($this->subscription_expires_at);
    }

    // =============================================
    // ✅ MÉTHODES POUR LE MODE DÉGRADÉ
    // =============================================

    public function isDegraded(): bool
    {
        return $this->hasExpiredFreeTrial() || 
               ($this->subscription === 'free' && $this->hasUsedFreeTrial() && 
                $this->subscription_expires_at !== null && $this->subscription_expires_at->isPast());
    }

    public function hasUsedMonthlyCalculator(): bool
    {
        if (!$this->isDegraded()) {
            return false;
        }

        $thisMonth = now()->format('Y-m');
        $lastUse = $this->last_calculator_use_at?->format('Y-m');

        if ($lastUse !== $thisMonth) {
            $this->calculator_use_count_month = 0;
            $this->last_calculator_use_at = now();
            $this->save();
            return false;
        }

        return $this->calculator_use_count_month >= self::DEGRADED_USES_PER_MONTH;
    }

    public function canUseCalculatorDegraded(): bool
    {
        if (!$this->isDegraded()) {
            return false;
        }

        $thisMonth = now()->format('Y-m');
        $lastUse = $this->last_calculator_use_at?->format('Y-m');

        if ($lastUse !== $thisMonth) {
            $this->calculator_use_count_month = 0;
            $this->last_calculator_use_at = now();
            $this->save();
            return true;
        }

        return $this->calculator_use_count_month < self::DEGRADED_USES_PER_MONTH;
    }

    public function incrementCalculatorUse(): void
    {
        $thisMonth = now()->format('Y-m');
        $lastUse = $this->last_calculator_use_at?->format('Y-m');

        if ($lastUse !== $thisMonth) {
            $this->calculator_use_count_month = 0;
        }

        $this->calculator_use_count_month++;
        $this->last_calculator_use_at = now();
        $this->save();
    }

    public function getRemainingCalculatorUsesThisMonth(): int
    {
        if (!$this->isDegraded()) {
            return PHP_INT_MAX;
        }

        $thisMonth = now()->format('Y-m');
        $lastUse = $this->last_calculator_use_at?->format('Y-m');

        if ($lastUse !== $thisMonth) {
            return self::DEGRADED_USES_PER_MONTH;
        }

        return max(0, self::DEGRADED_USES_PER_MONTH - $this->calculator_use_count_month);
    }

    public function getNextMonthlyRenewalDate(): ?string
    {
        if (!$this->isDegraded()) {
            return null;
        }

        $nextMonth = now()->addMonth()->startOfMonth();
        return $nextMonth->format('d/m/Y');
    }

    // =============================================
    // ✅ MÉTHODES POUR PAY AS YOU GO
    // =============================================

    public function getPayAsYouGoRemainingUses(): int
    {
        if ($this->payg_credits > 0) {
            return $this->payg_credits;
        }

        $payAsYouGo = $this->subscriptions()
            ->where('status', 'active')
            ->where('plan', 'pay_as_you_go')
            ->where('end_date', '>', now())
            ->first();

        if (!$payAsYouGo) {
            return 0;
        }

        return $payAsYouGo->uses_remaining ?? 0;
    }

    public function canUsePayAsYouGo(): bool
    {
        return $this->hasActivePayAsYouGo() && $this->getPayAsYouGoRemainingUses() > 0;
    }

    public function usePayAsYouGoCredit(): bool
    {
        $payAsYouGo = $this->subscriptions()
            ->where('status', 'active')
            ->where('plan', 'pay_as_you_go')
            ->where('end_date', '>', now())
            ->first();

        if (!$payAsYouGo) {
            return false;
        }

        $payAsYouGo->refresh();

        if ($payAsYouGo->uses_remaining !== null && $payAsYouGo->uses_remaining > 0) {
            $payAsYouGo->decrement('uses_remaining');
            $payAsYouGo->refresh();
            
            $this->payg_credits = $payAsYouGo->uses_remaining;
            $this->save();
            
            if ($payAsYouGo->uses_remaining <= 0) {
                $payAsYouGo->update(['status' => 'expired']);
                
                $this->subscription = 'pay_as_you_go';
                $this->subscription_expires_at = $payAsYouGo->end_date;
                $this->payg_credits = 0;
                $this->save();
            }
            
            return true;
        }

        return false;
    }

    // =============================================
    // ✅ VÉRIFICATIONS D'ACCÈS
    // =============================================

    public function canAccessCalculator(): bool
    {
        if ($this->is_admin) {
            return true;
        }

        if ($this->canUsePayAsYouGo()) {
            return true;
        }

        if ($this->subscription === 'pay_as_you_go' && $this->payg_credits <= 0) {
            return false;
        }

        if ($this->hasActivePaidSubscription()) {
            return true;
        }

        if ($this->hasActiveFreeTrial()) {
            return true;
        }

        if ($this->subscription === 'free' || $this->subscription === null) {
            return !$this->hasExpiredFreeTrial();
        }

        if ($this->isDegraded() && $this->subscription === 'free') {
            return true;
        }

        return false;
    }

    public function canAccessPremium(): bool
    {
        return $this->isAdmin() || 
               $this->hasActivePremium() || 
               $this->hasActivePro() || 
               $this->hasActiveBusiness();
    }

    public function getSubscriptionStatus(): string
    {
        if ($this->hasActivePaidSubscription()) {
            return 'active';
        }

        if ($this->hasActivePayAsYouGo()) {
            $remaining = $this->getPayAsYouGoRemainingUses();
            if ($remaining > 0) {
                return 'pay_as_you_go';
            }
        }

        if ($this->hasActiveFreeTrial()) {
            $daysLeft = $this->getFreeTrialDaysLeft();
            if ($daysLeft !== null && $daysLeft <= 3) {
                return 'trial_expiring_soon';
            }
            return 'trial_active';
        }

        if ($this->isDegraded()) {
            return 'degraded';
        }

        if ($this->canUseFreeTrial()) {
            return 'trial_available';
        }

        return 'inactive';
    }

    public function getSubscriptionMessage(): string
    {
        $activeSubscription = $this->getActiveSubscription();
        if ($activeSubscription) {
            $plan = ucfirst($activeSubscription->plan);
            $endDate = $activeSubscription->end_date->format('d/m/Y');
            return "Abonnement {$plan} actif jusqu'au {$endDate}";
        }

        if ($this->hasActivePayAsYouGo()) {
            $remaining = $this->getPayAsYouGoRemainingUses();
            return "Pay As You Go - {$remaining} utilisation(s) restante(s)";
        }

        if ($this->hasActiveFreeTrial()) {
            $daysLeft = $this->getFreeTrialDaysLeft();
            return "Essai gratuit - {$daysLeft} jour(s) restant(s)";
        }

        if ($this->isDegraded()) {
            $remaining = $this->getRemainingCalculatorUsesThisMonth();
            $nextMonth = $this->getNextMonthlyRenewalDate();
            if ($remaining > 0) {
                return "Mode dégradé - 1 calcul gratuit ce mois-ci (encore disponible). Abonnez-vous pour un accès illimité !";
            } else {
                return "Mode dégradé - Vous avez utilisé votre calcul gratuit du mois. Prochain calcul disponible le {$nextMonth}. Abonnez-vous pour un accès illimité !";
            }
        }

        if ($this->canUseFreeTrial()) {
            return "Découvrez MomoOpti - Profitez de votre essai gratuit de " . self::FREE_TRIAL_DAYS . " jours !";
        }

        return "Aucun abonnement actif";
    }

    // =============================================
    // ✅ GESTION DES ABONNEMENTS (EXISTANTES)
    // =============================================

    public function subscribe(string $plan, int $days, float $amount = null, string $paymentMethod = null, string $transactionId = null): Subscription
    {
        if ($this->subscription === 'free') {
            $this->trial_used = true;
        }

        $this->calculator_use_count_month = 0;
        $this->last_calculator_use_at = null;

        $this->subscription = $plan;
        $this->subscription_expires_at = now()->addDays($days);
        $this->save();

        return $this->subscriptions()->create([
            'plan' => $plan,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays($days),
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'amount_paid' => $amount,
            'currency' => 'XOF',
        ]);
    }

    public function buyPayAsYouGo(string $paymentMethod = null, string $transactionId = null): Subscription
    {
        $subscription = $this->subscriptions()->create([
            'plan' => 'pay_as_you_go',
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays(self::PAY_AS_YOU_GO_DURATION_DAYS),
            'uses_remaining' => self::PAY_AS_YOU_GO_USES,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'amount_paid' => self::PAY_AS_YOU_GO_PRICE,
            'currency' => 'XOF',
        ]);

        $this->calculator_use_count_month = 0;
        $this->last_calculator_use_at = null;
        $this->subscription = 'pay_as_you_go';
        $this->subscription_expires_at = now()->addDays(self::PAY_AS_YOU_GO_DURATION_DAYS);
        $this->payg_credits = self::PAY_AS_YOU_GO_USES;
        $this->save();

        return $subscription;
    }

    public function renewSubscription(int $days, float $amount = null, string $paymentMethod = null, string $transactionId = null): ?Subscription
    {
        $activeSubscription = $this->getActiveSubscription();
        
        if ($activeSubscription) {
            $newEndDate = $activeSubscription->end_date->addDays($days);
            $activeSubscription->update([
                'end_date' => $newEndDate,
                'amount_paid' => $amount,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
            ]);

            $this->calculator_use_count_month = 0;
            $this->last_calculator_use_at = null;

            $this->subscription_expires_at = $newEndDate;
            $this->save();

            return $activeSubscription;
        }

        return $this->subscribe($this->subscription ?? 'premium', $days, $amount, $paymentMethod, $transactionId);
    }

    public function cancelSubscription(): void
    {
        $activeSubscription = $this->getActiveSubscription();
        
        if ($activeSubscription) {
            $activeSubscription->update(['status' => 'cancelled']);
        }

        $this->subscription = 'free';
        $this->subscription_expires_at = null;
        $this->trial_used = true;
        $this->calculator_use_count_month = 0;
        $this->last_calculator_use_at = null;
        $this->save();
    }

    // =============================================
    // ✅ MÉTHODES UTILITAIRES
    // =============================================

    public function getMaxMethodsAllowed(): int
    {
        return PHP_INT_MAX;
    }

    /////////////////////////////////////////////////////////////// pour les operateurs
    
 
    // =============================================
    // ✅ RELATION AVEC LE PROFIL OPÉRATEUR
    // =============================================

    /**
     * ✅ Relation avec le profil opérateur
     */
    public function operatorProfile(): HasOne
    {
        return $this->hasOne(OperatorProfile::class);
    }

    // =============================================
    // ✅ MÉTHODES POUR LES OPÉRATEURS
    // =============================================

    /**
     * ✅ Vérifier si l'utilisateur a un profil opérateur (même non actif)
     */
    public function hasOperatorProfile(): bool
    {
        return $this->operatorProfile()->exists();
    }

    /**
     * ✅ Vérifier si l'utilisateur est un opérateur actif
     */
    public function isOperator(): bool
    {
        // Admin est considéré comme opérateur
        if ($this->is_admin) {
            return true;
        }

        $profile = $this->operatorProfile;
        
        if (!$profile) {
            return false;
        }

        return $profile->is_active && $profile->approved_at !== null;
    }

    /**
     * ✅ Vérifier si l'utilisateur peut accéder aux fonctionnalités opérateur
     */
    public function canAccessOperatorFeatures(): bool
    {
        return $this->is_admin || $this->isOperator();
    }

    /**
     * ✅ Récupérer le nom de l'entreprise
     */
    public function getBusinessName(): ?string
    {
        $profile = $this->operatorProfile;
        return $profile ? $profile->business_name : null;
    }

    /**
     * ✅ Récupérer le profil opérateur
     */
    public function getOperatorProfile(): ?OperatorProfile
    {
        return $this->operatorProfile;
    }
}