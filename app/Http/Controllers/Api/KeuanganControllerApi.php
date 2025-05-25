<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\keuangan;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
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
    public function trackMonthlyIncomeProfitLoss(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        if ($user->isOwner()) {
            try {
                // Get year from request or use current year
                $year = $request->year ?? Carbon::now()->year;
                
                // Query data for selected year grouped by month
                $monthlyData = Keuangan::selectRaw(
                    'MONTH(tanggal) as month, 
                    SUM(CASE WHEN nominal > 0 THEN nominal ELSE 0 END) as total_income,
                    SUM(CASE WHEN nominal < 0 THEN ABS(nominal) ELSE 0 END) as total_expense,
                    SUM(nominal) as profit_loss'
                )
                ->whereYear('tanggal', $year)
                ->groupBy('month')
                ->orderBy('month')
                ->get();
                
                // Initialize arrays for all 12 months with zeros
                $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
                $incomeData = array_fill(0, 12, 0);
                $expenseData = array_fill(0, 12, 0);
                $profitLossData = array_fill(0, 12, 0);
                
                // Fill in the actual data from database
                foreach ($monthlyData as $data) {
                    $index = $data->month - 1; // Convert 1-based month to 0-based array index
                    $incomeData[$index] = (float) $data->total_income;
                    $expenseData[$index] = (float) $data->total_expense;
                    $profitLossData[$index] = (float) $data->profit_loss;
                }
                
                // Calculate yearly totals
                $yearlyIncome = array_sum($incomeData);
                $yearlyExpense = array_sum($expenseData);
                $yearlyProfitLoss = array_sum($profitLossData);
                
                // Format as chart-ready datasets
                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                
                $datasets = [
                    [
                        'label' => 'Pemasukan',
                        'data' => $incomeData,
                        'fill' => false,
                        'borderColor' => 'rgb(75, 192, 192)',
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Pengeluaran',
                        'data' => $expenseData,
                        'fill' => false,
                        'borderColor' => 'rgb(255, 99, 132)',
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Laba/Rugi',
                        'data' => $profitLossData,
                        'fill' => false,
                        'borderColor' => 'rgb(54, 162, 235)',
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'tension' => 0.1
                    ]
                ];
                
                // Return complete response with monthly and yearly data
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil mendapatkan data keuangan bulanan',
                    'data' => [
                        'year' => $year,
                        'labels' => $labels,
                        'datasets' => $datasets,
                        'summary' => [
                            'total_income' => $yearlyIncome,
                            'total_expense' => $yearlyExpense,
                            'total_profit_loss' => $yearlyProfitLoss,
                            'is_profitable' => $yearlyProfitLoss > 0
                        ],
                        'monthly_details' => array_map(function($month, $income, $expense, $profitLoss) use ($labels) {
                            return [
                                'month' => $labels[$month - 1],
                                'month_number' => $month,
                                'income' => $income,
                                'expense' => $expense,
                                'profit_loss' => $profitLoss,
                                'is_profitable' => $profitLoss > 0
                            ];
                        }, $months, $incomeData, $expenseData, $profitLossData)
                    ]
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendapatkan data keuangan bulanan: ' . $e->getMessage(),
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
    public function storePengeluaran(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        if ($user->isOwner()) {
            try {
                $validate = $request->validate([
                    'keterangan' => 'required|string',
                    'nominal' => 'required|integer',
                    'jenis_pembayaran' => 'required|in:Cash,Transfer,E-Wallet',
                    'tanggal' => 'required|date_format:Y-m-d',  // Validasi format YYYY-MM-DD
                    'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);
    
                // Handle upload bukti pembayaran jika ada
                if ($request->hasFile('bukti_pembayaran')) {
                    $fileName = time() . '.' . $request->bukti_pembayaran->extension();
                    $request->bukti_pembayaran->move(public_path('uploads/keuangan/pengeluaran/'), $fileName);
                    $buktiPath = 'uploads/keuangan/pengeluaran/'.$fileName;
                } else {
                    $buktiPath = null;
                }
    
                // Format tanggal sesuai kebutuhan database
                $tanggal = \Carbon\Carbon::parse($validate['tanggal'])->format('Y-m-d');
    
                $keuangan = keuangan::create([
                    'keterangan' => $validate['keterangan'],
                    'nominal' => -$validate['nominal'], // negative because it's an expense
                    'jenis_pembayaran' => $validate['jenis_pembayaran'],
                    'tanggal' => $tanggal,
                    'jenis_keuangan' => 'pengeluaran', // tambahkan jenis keuangan
                    'user_id' => $user->id,
                    'bukti_pembayaran' => $buktiPath
                ]);
    
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil menambahkan pengeluaran',
                    'data' => $keuangan
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal menambahkan pengeluaran: ' . $e->getMessage(),
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

    public function pengeluaran(Request $request){
        $user = User::findOrFail(Auth::id());
        if ($user->isOwner()) {
            try {
                // Filtering options
                $query = Keuangan::where('jenis_keuangan', 'pengeluaran');
                
                // Optional date range filters
                if ($request->has('start_date') && $request->has('end_date')) {
                    $startDate = Carbon::parse($request->start_date)->startOfDay();
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                }
                
                // Optional payment method filter
                if ($request->has('jenis_pembayaran')) {
                    $query->where('jenis_pembayaran', $request->jenis_pembayaran);
                }
                
                // Get results with pagination
                $pengeluaran = $query->with('user')
                    ->orderBy('tanggal', 'desc')
                    ->paginate($request->per_page ?? 10);
                    
                // Calculate total amount for filtered records
                $totalPengeluaran = $query->sum('nominal');
                
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil mendapatkan data pengeluaran',
                    'data' => [
                        'pengeluaran' => $pengeluaran,
                        'total_pengeluaran' => abs($totalPengeluaran), // Convert to positive for display
                    ]
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendapatkan data pengeluaran: ' . $e->getMessage(),
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

    public function getPemasukan(Request $request){
        $user = User::findOrFail(Auth::id());
        if ($user->isOwner()) {
            try {
                // Filtering options
                $query = keuangan::where('jenis_keuangan', 'pemasukan');
                
                // Optional date range filters
                if ($request->has('start_date') && $request->has('end_date')) {
                    $startDate = Carbon::parse($request->start_date)->startOfDay();
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                }
                
                // Optional payment method filter
                if ($request->has('jenis_pembayaran')) {
                    $query->where('jenis_pembayaran', $request->jenis_pembayaran);
                }
                
                // Get results with pagination
                $pemasukan = $query->with('user')
                    ->orderBy('tanggal', 'desc')
                    ->paginate($request->per_page ?? 10);
                    
                // Calculate total amount for filtered records
                $totalPemasukan = $query->sum('nominal');
                
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil mendapatkan data pemasukan',
                    'data' => [
                        'pemasukan' => $pemasukan,
                        'total_pemasukan' => abs($totalPemasukan), // Convert to positive for display
                    ]
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendapatkan data pemasukan: ' . $e->getMessage(),
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
