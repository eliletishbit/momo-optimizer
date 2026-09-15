<!-- <?php

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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Ici se trouvent toutes les routes web de l'application MomoOpti.
| Elles sont organisées par niveaux d'accès : Public, Privé, Premium, Admin.
|
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
Route::post('/calculator/result', [CalculatorController::class, 'result'])->name('calculator.result');

// Webhook (sans auth, appelé par FedaPay)
Route::post('/payment/webhook', [PremiumController::class, 'webhook'])->name('payment.webhook');

/**
 * ==========================================
 * 2. ROUTES PRIVÉES (🔐) - Authentification requise
 * ==========================================
 */
Route::middleware(['auth'])->group(function () {

    //routes de gestion de souscription à un abonnement par lutilisateur

    Route::get('/subscription/checkout', [PremiumController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/payment/callback', [PremiumController::class, 'callback'])->name('subscription.callback');
    
    // Tableau de bord utilisateur
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestion du profil (Utilise le ProfileController de Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

   
    // Paramètres (Moyens de paiement préférés)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
    Route::patch('/settings/{userMethod}', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/{userMethod}', [SettingsController::class, 'destroy'])->name('settings.destroy');

    // Résultat du calculateur (si GET requis après calcul)
    Route::get('/calculator/result', [CalculatorController::class, 'result'])->name('calculator.result.view');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');


    Route::middleware(['subscription'])->group(function () {
        // Route unique accessible à tous les abonnés (Premium, Pro, Business, Pay_as_you_go)
        Route::get('/history', [HistoryController::class, 'index'])->name('history');
    });
    /**
     * ==========================================
     * 3. ROUTES PREMIUM (💎) - Abonnement requis
     * ==========================================
     */
    Route::middleware(['subscription:premium'])->prefix('premium')->name('premium.')->group(function () {
        
       
    });

    //souscription abonnement pro pmes
        Route::middleware(['subscription:pro'])->prefix('pro')->name('pro.')->group(function () {     
        
        Route::get('/decisionaldashbaorad', [PremiumController::class, 'analytics'])->name('analytics');
        Route::get('/bilan', [PremiumController::class, 'bilan'])->name('bilan');
    });

});

/**
 * ==========================================
 * 4. ROUTES ADMINISTRATION (👑) - Admin requis
 * ==========================================
 */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Tableau de bord Admin
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Gestion des utilisateurs
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Gestion des frais
    Route::get('/fees/receipt', [AdminFeeController::class, 'receipt'])->name('fees.receipt');
    Route::get('/fees/sending', [AdminFeeController::class, 'sending'])->name('fees.sending');
    Route::post('/fees', [AdminFeeController::class, 'store'])->name('fees.store');
    Route::patch('/fees/{id}', [AdminFeeController::class, 'update'])->name('fees.update');
    Route::delete('/fees/{id}', [AdminFeeController::class, 'destroy'])->name('fees.destroy');

    // Gestion des méthodes de paiement
    Route::get('/methods', [AdminMethodController::class, 'index'])->name('methods.index');
    Route::get('/methods/create', [AdminMethodController::class, 'create'])->name('methods.create');
    Route::post('/methods', [AdminMethodController::class, 'store'])->name('methods.store');
    Route::get('/methods/{method}/edit', [AdminMethodController::class, 'edit'])->name('methods.edit');
    Route::patch('/methods/{method}', [AdminMethodController::class, 'update'])->name('methods.update');
    Route::delete('/methods/{method}', [AdminMethodController::class, 'destroy'])->name('methods.destroy');

    // Abonnements
    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::patch('/subscriptions/{subscription}', [AdminSubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [AdminSubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    // Statistiques Globales
    Route::get('/statistics', [AdminStatisticsController::class, 'index'])->name('statistics');
});

/**
 * ==========================================
 * 5. AUTHENTIFICATION (Breeze)
 * ==========================================
 */
require __DIR__.'/auth.php'; 
