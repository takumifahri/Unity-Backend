<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\keuangan;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Laravel\Prompts\error;

class KeuanganControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::findOrFail(Auth::id());
        if ($user->isOwner()) {
            try {
                $keuangan = keuangan::with(['user', 'order.customOrder', 'order', 'order.catalog']);
                $customOrder = Order::with(['customOrder'])->get();
                if (request()->has('search') && request('search')) {
                    $keuangan->where('keterangan', 'like', '%' . request('search') . '%');
                }
                if (request()->has('sort_by') && request()->has('sort_type')) {
                    $keuangan->orderBy(request('sort_by'), request('sort_type'));
                } else {
                    $keuangan->orderBy('created_at', 'asc');
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil mendapatkan data',
                    'data' => $keuangan->get()
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendapatkan data',
                    'data' => null
                ], 500);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthorized',
            'data' => null
        ], 403);
    }


    /**
     * Track monthly income, profit, and loss.
     */
    public function trackMonthlyIncomeProfitLoss()
    {
        $user = User::findOrFail(Auth::id());
        if ($user->isOwner()) {
            try {
                $monthlyData = keuangan::selectRaw(
                    'YEAR(created_at) as year, 
                     MONTH(created_at) as month, 
                     SUM(CASE WHEN nominal > 0 THEN nominal ELSE 0 END) as total_income'
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $datasets = [
                    'label' => 'Pendapatan',
                    'data' => array_fill(0, 12, 0), // Initialize with 0 for all months
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1
                ];

                foreach ($monthlyData as $data) {
                    $datasets['data'][$data->month - 1] = $data->total_income; // Map income to the correct month
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil mendapatkan data pendapatan bulanan',
                    'data' => [
                        'labels' => $labels,
                        'datasets' => [$datasets]
                    ]
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendapatkan data pendapatan bulanan: ' . $e->getMessage(),
                    'data' => null
                ], 500);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthorized',
            'data' => null
        ], 403);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
