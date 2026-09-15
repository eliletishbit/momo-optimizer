<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\OptimizationHistory;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Affiche les statistiques globales.
     */
    public function index(): View
    {
        $stats = [
            'users_by_country' => User::select('country_code', DB::raw('count(*) as total'))
                                    ->groupBy('country_code')
                                    ->get(),
            'subscriptions_by_type' => User::select('subscription', DB::raw('count(*) as total'))
                                    ->groupBy('subscription')
                                    ->get(),
            'optimizations_over_time' => OptimizationHistory::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
                                    ->groupBy('date')
                                    ->orderBy('date', 'desc')
                                    ->take(30)
                                    ->get(),
        ];

        return view('pages.admin.statistics', compact('stats'));
    }
}
