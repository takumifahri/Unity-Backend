<?php

namespace App\Repositories;

use App\Models\History;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class HistoryRepository
{
    /**
     * Mendapatkan history untuk admin
     */
    public function getAdminHistory(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = History::with('user')
                       ->orderBy('created_at', 'desc');
        
        // Filter berdasarkan model type
        if (isset($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }
        
        // Filter berdasarkan pengguna
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        // Filter berdasarkan rentang tanggal
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from']));
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
        
        return $query->paginate($perPage);
    }
    
    /**
     * Mendapatkan history untuk user biasa
     */
    public function getUserHistory(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return History::where(function($query) use ($userId) {
                // History transaksi user
                $query->where(function($q) use ($userId) {
                    $q->where('model_type', 'App\Models\Transaction')
                      ->whereIn('items_id', function($subquery) use ($userId) {
                            $subquery->select('id')
                                     ->from('transactions')
                                     ->where('user_id', $userId);
                      });
                })
                // Atau history profil user
                ->orWhere(function($q) use ($userId) {
                    $q->where('model_type', 'App\Models\User')
                      ->where('items_id', $userId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    public function getCachedAdminHistorySummary(): array
    {
        return cache()->remember('admin_history_summary', 60, function() {
            // Count histories by type for the last 30 days
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            
            return [
                'total' => History::where('created_at', '>=', $thirtyDaysAgo)->count(),
                'by_type' => History::where('created_at', '>=', $thirtyDaysAgo)
                                ->selectRaw('model_type, count(*) as count')
                                ->groupBy('model_type')
                                ->pluck('count', 'model_type')
                                ->toArray(),
                'by_day' => History::where('created_at', '>=', $thirtyDaysAgo)
                                ->selectRaw('DATE(created_at) as date, count(*) as count')
                                ->groupBy('date')
                                ->pluck('count', 'date')
                                ->toArray()
            ];
        });
    }
}