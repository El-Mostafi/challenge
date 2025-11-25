<?php

namespace App\Http\Controllers\Api;
use App\Models\Expense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Invalider le cache des statistiques pour une période donnée
     */
    public static function clearCache($period = null)
    {
        if ($period === null) {
            $period = now()->format('Y-m');
        }
        $cacheKey = "stats_summary_{$period}";
        Cache::forget($cacheKey);
    }

    public function summary(Request $request)
    {
        try{
            // Récupération de la période 
            $period = $request->input('period', now()->format('Y-m'));
            
            // Clé de cache unique par période
            $cacheKey = "stats_summary_{$period}";
            
            // Cache pendant 60 secondes
            $stats = Cache::remember($cacheKey, 60, function () use ($period) {
                [$year, $month] = explode('-', $period);
                
                // Total par catégorie pour la période
                $byCategory = Expense::where('status', 'APPROVED')
                    ->whereYear('spent_at', $year)
                    ->whereMonth('spent_at', $month)
                    ->select('category', DB::raw('SUM(amount) as total'))
                    ->groupBy('category')
                    ->get();
                
                // Total général
                $totalAmount = $byCategory->sum('total');
                
                // Nombre de dépenses par statut
                $byStatus = Expense::whereYear('spent_at', $year)
                    ->whereMonth('spent_at', $month)
                    ->select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get();
                
                return [
                    'period' => $period,
                    'total_amount' => $totalAmount,
                    'by_category' => $byCategory,
                    'by_status' => $byStatus,
                ];
            });
            
            return response()->json($stats);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des statistiques.'], 500);
        }
    }
}
