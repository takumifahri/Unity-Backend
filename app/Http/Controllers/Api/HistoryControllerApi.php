<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;
use App\Repositories\HistoryRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistoryControllerApi extends Controller
{
    private function generateVerbalDescription($history)
    {
        $description = "Pada tanggal " . $history->created_at->format('d-m-Y') . ", user " . $history->user->name . " melakukan aksi " . $history->action . " pada item dengan tipe " . $history->item_type . " dan ID " . $history->items_id . ".";
        
        if ($history->action === 'create' && $history->item_type === 'user') {
            $description .= $history->user->role === 'admin' ? " Admin tersebut membuat user baru." : " User tersebut melakukan registrasi.";
        }
        
        return $description;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = History::with('user')->latest();
        
        if ($user->role !== 'admin' || $user->role !== 'owner') {
            // Jika bukan admin, hanya bisa melihat history miliknya sendiri
            $query->where('user_id', $user->id);
        }

        // Filter berdasarkan tipe item
        if ($request->has('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        // Filter berdasarkan ID item
        if ($request->has('items_id')) {
            $query->where('items_id', $request->items_id);
        }

        // Filter berdasarkan aksi
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter berdasarkan tanggal
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter berdasarkan admin/user
        if ($request->has('user_id') && $user->role === 'admin') {
            $query->where('user_id', $request->user_id);
        }

        $histories = $query->paginate($request->per_page ?? 15);

        if ($histories->isEmpty()) {
            return response()->json([
                'message' => 'Belum ada history sama sekali'
            ], 200);
        }

        // Menambahkan deskripsi verbal untuk setiap history
        $histories->getCollection()->transform(function ($history) {
            $history->verbal_description = $this->generateVerbalDescription($history);
            return $history;
        });

        return response()->json([
            'message' => 'Data History berhasil diambil',
            'data' => $histories
        ]);
    }


    
    public function dailyRevenue()
    {
        $user = Auth::user();
        $permission = $user->role ==='owner';

        if($permission) {
            // Mengambil data pemasukan harian dari transaksi
                $dailyRevenue = DB::table('transactions')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total_revenue'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'desc')
                ->paginate(30); // 30 hari terakhir
            
            if($dailyRevenue->isEmpty()) {
                return response()->json([
                    'message' => 'Belum ada pemasukan'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Data pemasukan harian berhasil diambil',
                    'data' => $dailyRevenue
                ]);
            }
 
        }

       
    }
    
    public function activitySummary()
    {
        $user = Auth::user();
        $permission = $user->role ==='owner';

        if($permission) {
            // Ringkasan aktivitas: berapa banyak create, update, delete per hari
            $summary = DB::table('histories')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('action'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('date', 'action')
                ->orderBy('date', 'desc')
                ->get();
                
            return response()->json([
                'data' => $summary
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
    }
    
    // Laporan aktivitas admin tertentu
    public function adminActivity($userId)
    {
        $activities = History::with('user')
            ->where('user_id', $userId)
            ->latest()
            ->paginate(15);
            
        return response()->json([
            'data' => $activities
        ]);
    }
}
