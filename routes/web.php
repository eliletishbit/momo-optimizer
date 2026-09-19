<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\FeeController as AdminFeeController;
use App\Http\Controllers\Admin\MethodController as AdminMethodController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\StatisticsController as AdminStatisticsController;
use App\Http\Controllers\PublicCalculatorController;



use App\Http\Controllers\OperationOperateurController;
use App\Http\Controllers\OperatorApplyController;


use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/**
 * ==========================================
 * 1. ROUTES PUBLIQUES (🔓)
 * ==========================================
 */
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');

Route::get('/calculator', [CalculatorController::class, 'index'])->name('calculator');
Route::post('/calculator', [CalculatorController::class, 'calculate'])->name('calculator.calculate');
Route::match(['get', 'post'], '/calculator/result', [CalculatorController::class, 'result'])->name('calculator.result');

// Webhook (sans auth, appelé par FedaPay)
Route::post('/payment/webhook', [PremiumController::class, 'webhook'])->name('payment.webhook');

// ✅ Routes publiques du calculateur
Route::get('/calculateur-gratuit', [PublicCalculatorController::class, 'index'])
    ->name('public.calculator');

Route::post('/calculateur-gratuit/calculer', [PublicCalculatorController::class, 'calculate'])
    ->name('public.calculator.calculate');

// ✅ Route pour réinitialiser les essais (tests)
Route::get('/calculateur-gratuit/reset', [PublicCalculatorController::class, 'resetTrials'])
    ->name('public.calculator.reset');



/**
 * ==========================================
 * 2. ROUTES PRIVÉES (🔐) - Authentification requise
 * ==========================================
 */
Route::middleware(['auth'])->group(function () {

    // Routes de gestion de souscription
    Route::get('/subscription/checkout', [PremiumController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/payment/callback', [PremiumController::class, 'callback'])->name('subscription.callback');
    
    // Tableau de bord utilisateur
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestion du profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Paramètres (Moyens de paiement)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
    Route::patch('/settings/{userMethod}', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/{userMethod}', [SettingsController::class, 'destroy'])->name('settings.destroy');

    // Résultat du calculateur
    Route::get('/calculator/result', [CalculatorController::class, 'result'])->name('calculator.result.view');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // ✅ History : accessible à tous les abonnés (Premium, Pro, Pay As You Go)
    Route::middleware(['subscription'])->group(function () {
        Route::get('/history', [HistoryController::class, 'index'])->name('history');
        Route::delete('/history/{id}', [HistoryController::class, 'destroy'])->name('history.destroy');
        Route::post('/history/clear', [HistoryController::class, 'clear'])->name('history.clear');
    });

    // ✅ ROUTES PREMIUM : UNIQUEMENT les abonnés Premium
    Route::middleware(['subscription:premium'])->prefix('premium')->name('premium.')->group(function () {
        Route::get('/advanced-calculator', function () {
            return "calculateur avancé"; 
        });
    });

    // ✅ ROUTES PRO : UNIQUEMENT les abonnés Pro (et Administrateurs)
    Route::middleware(['subscription:pro'])->prefix('pro')->name('pro.')->group(function () {
        Route::get('/decisional-dashboard', [PremiumController::class, 'decisionalDashboard'])->name('analytics');
        Route::get('/decisionaldashbaorad', [PremiumController::class, 'decisionalDashboard']); // Alias rétrocompatible
        Route::get('/bilan', [PremiumController::class, 'bilan'])->name('bilan');
    });

    // ✅ ROUTES PAY AS YOU GO : UNIQUEMENT les abonnés Pay As You Go
    Route::middleware(['subscription:pay_as_you_go'])->prefix('payg')->name('payg.')->group(function () {
        // Routes Pay As You Go ici
    });

});

/**
 * ==========================================
 * 3. ROUTES ADMINISTRATION (👑) - Admin requis
 * ==========================================
 */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/fees/receipt', [AdminFeeController::class, 'receipt'])->name('fees.receipt');
    Route::get('/fees/sending', [AdminFeeController::class, 'sending'])->name('fees.sending');
    Route::post('/fees', [AdminFeeController::class, 'store'])->name('fees.store');
    Route::patch('/fees/{id}', [AdminFeeController::class, 'update'])->name('fees.update');
    Route::delete('/fees/{id}', [AdminFeeController::class, 'destroy'])->name('fees.destroy');
    Route::get('/methods', [AdminMethodController::class, 'index'])->name('methods.index');
    Route::get('/methods/create', [AdminMethodController::class, 'create'])->name('methods.create');
    Route::post('/methods', [AdminMethodController::class, 'store'])->name('methods.store');
    Route::get('/methods/{method}/edit', [AdminMethodController::class, 'edit'])->name('methods.edit');
    Route::patch('/methods/{method}', [AdminMethodController::class, 'update'])->name('methods.update');
    Route::delete('/methods/{method}', [AdminMethodController::class, 'destroy'])->name('methods.destroy');
    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::patch('/subscriptions/{subscription}', [AdminSubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [AdminSubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    Route::get('/statistics', [AdminStatisticsController::class, 'index'])->name('statistics');
});

//operateurs


// ✅ ROUTE POUR DEVENIR OPÉRATEUR
// =============================================
Route::middleware(['auth'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/apply', [OperatorApplyController::class, 'index'])->name('apply');
    Route::post('/apply', [OperatorApplyController::class, 'store'])->name('apply.store');
});

// =============================================
// ✅ ROUTES PROTÉGÉES - OPÉRATIONS OPÉRATEUR
// =============================================
Route::middleware(['auth', 'is.operator'])
    ->prefix('operations')
    ->name('operations.')
    ->group(function () {
        // Liste des opérations
        Route::get('/', [OperationOperateurController::class, 'index'])->name('index');
        
        // Création d'une opération
        Route::get('/create', [OperationOperateurController::class, 'create'])->name('create');
        Route::post('/', [OperationOperateurController::class, 'store'])->name('store');
        
        // Suppression
        Route::delete('/{id}', [OperationOperateurController::class, 'destroy'])->name('destroy');
        
        // Bilan journalier
        Route::get('/bilan', [OperationOperateurController::class, 'bilan'])->name('bilan');
        
        // ✅ Mise à jour manuelle des caisses
        Route::get('/caisses', [OperationOperateurController::class, 'editCaisses'])->name('caisses.edit');
        Route::put('/caisses', [OperationOperateurController::class, 'updateCaissesManually'])->name('caisses.update');
    });


/**
 * ==========================================
 * 4. AUTHENTIFICATION (Breeze)
 * ==========================================
 */
require __DIR__.'/auth.php';