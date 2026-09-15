<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\OptimizationHistory;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord administrateur.
     */
    public function index(): View
    {
        return view('pages.admin.dashboard', [
            'totalUsers' => User::count(),
            'activeSubscriptions' => Subscription::where('status', 'active')->count(),
            'totalOptimizations' => OptimizationHistory::count(),
            'recentUsers' => User::latest()->take(5)->get(),
        ]);
    }
}
