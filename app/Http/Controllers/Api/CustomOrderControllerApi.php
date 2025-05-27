<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AdminOrderEmail;
use App\Mail\CustomerorderMail;
use App\Models\CustomOrder;
use App\Models\Order;
use App\Models\transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomOrderControllerApi extends Controller
{
    //
    public function show($custom_order_unique_id)
    {
        $user = User::findOrFail(Auth::id());
        try {
            if ($user->isUser()) {
                $customOrder = CustomOrder::where('user_id', $user->id)
                    ->where('custom_order_unique_id', $custom_order_unique_id)
                    ->with('masterBahan', 'approvedByUser', 'colors.sizes', 'user', 'orders', 'transactions', 'deliveryProof')
                    ->first();
                
                if ($customOrder) {
                    return response()->json([
                        'message' => 'Data retrieved successfully',
                        'data' => $customOrder
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Custom order not found',
                        'data' => []
                    ], 404);
                }
            } else if ($user->isAdmin() || $user->isOwner()) {
                $customOrder = CustomOrder::where('custom_order_unique_id', $custom_order_unique_id)
                    ->with('masterBahan', 'approvedByUser', 'colors.sizes', 'user', 'orders', 'transactions','deliveryProof')
                    ->first();
                
                if ($customOrder) {
                    return response()->json([
                        'message' => 'Data retrieved successfully',
                        'data' => $customOrder
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Custom order not found',
                        'data' => []
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }
    private function getPaymentDetails($payment_method)
    {
        $payment_details = [
            'BCA' => 'Bank BCA: 2670342134 a.n. Andi Setiawan',
            'E-Wallet_Dana' => 'DANA: 0857-4851-3790 a.n. Andi Setiawan',
        ];

        return $payment_details[$payment_method] ?? 'Bank BCA: 2670342134 a.n. Andi Setiawan';
    }

    public function index(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        
        // Filter options
        $status = $request->query('status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        
        if ($user->isAdmin() || $user->isOwner()) {
            try {
                // Build base query
                $query = CustomOrder::with([
                    'masterBahan', 
                    'approvedByUser', 
                    'user', 
                    'orders',
                    // 'orders.deliveryProof',
                    'colors.sizes',
                    'transactions',
                    'deliveryProof' // Add colors and sizes relationship
                ]);
                
                // Apply filters if provided
                if ($status) {
                    $query->where('status', $status);
                }
                
                if ($dateFrom && $dateTo) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($dateFrom)->startOfDay(),
                        Carbon::parse($dateTo)->endOfDay()
                    ]);
                }
                
                // Apply ordering
                $query->orderBy($request->query('sort_by', 'created_at'), $request->query('sort_order', 'desc'));
                
                // Paginate or get all results
                $perPage = $request->query('per_page', 10);
                $customOrders = ($perPage === 'all') ? $query->get() : $query->paginate($perPage);
                
                if ($customOrders->isEmpty()) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Tidak ada data custom order',
                        'data' => []
                    ], 200);
                }
                
                // Format response based on pagination or not
                $responseData = [
                    'status' => true,
                    'message' => 'Berhasil mendapatkan data custom order',
                    'data' => [
                        'custom_orders' => $customOrders
                    ]
                ];
                
                // Add summary info for admin/owner
                $responseData['data']['summary'] = [
                    'total_pending' => CustomOrder::where('status', 'pending')->count(),
                    'total_disetujui' => CustomOrder::where('status', 'disetujui')->count(),
                    'total_proses' => CustomOrder::where('status', 'proses')->count(),
                    'total_selesai' => CustomOrder::where('status', 'selesai')->count(),
                    'total_ditolak' => CustomOrder::where('status', 'ditolak')->count(),
                    'total_dibatalkan' => CustomOrder::where('status', 'dibatalkan')->count(),
                ];
                
                return response()->json($responseData, 200);
                
            } catch (\Exception $e) {
                Log::error('Custom order index error: ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendapatkan data custom order',
                    'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server'
                ], 500);
            }
        }

        if ($user->isUser()) {
            try {
                // Build query for user's orders with filtering
                $query = CustomOrder::where('user_id', $user->id)
                    ->with(['colors.sizes']); // Add colors and sizes relationship
                
                // Apply filters if provided
                if ($status) {
                    $query->where('status', $status);
                }
                
                if ($dateFrom && $dateTo) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($dateFrom)->startOfDay(),
                        Carbon::parse($dateTo)->endOfDay()
                    ]);
                }
                
                // Apply ordering
                $query->orderBy($request->query('sort_by', 'created_at'), $request->query('sort_order', 'desc'));
                
                // Paginate results
                $perPage = $request->query('per_page', 10);
                $customOrders = ($perPage === 'all') ? $query->get() : $query->paginate($perPage);
                
                if ($customOrders->isEmpty()) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Anda belum memiliki custom order',
                        'data' => []
                    ], 200);
                }
                
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil mendapatkan data custom order',
                    'data' => $customOrders
                ], 200);
                
            } catch (\Exception $e) {
                Log::error('Custom order index error for user: ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendapatkan data custom order',
                    'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server'
                ], 500);
            }
        }
        
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized',
        ], 403);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function propose(Request $request)
    {
        try {
            $user = User::findOrFail(Auth::id());
            // Check permissions
            if (!$user || !($user->isAdmin() || $user->isOwner() || $user->isUser())) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
    
            // Validate data
            $validateData = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'no_telp' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'jenis_baju' => 'required|string|max:255',
                'detail_bahan' => 'nullable|string|max:255',
                'sumber_kain' => 'required|in:konveksi,sendiri',
                'catatan' => 'nullable|string|max:1000',
                'colors' => 'required|array',
                'colors.*.color_name' => 'required|string',
                'colors.*.sizes' => 'required|array',
                'colors.*.sizes.*.size' => 'required|in:S,M,L,XL',
                'colors.*.sizes.*.jumlah' => 'required|integer|min:1',
                'gambar_referensi' => 'nullable|array',
                'gambar_referensi.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            // Handle multiple file uploads
            $gambarReferensi = [];
            if ($request->hasFile('gambar_referensi')) {
                foreach ($request->file('gambar_referensi') as $file) {
                    // Make sure the upload directory exists
                    $uploadPath = public_path('uploads/custom_order');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }
                    
                    // Generate a unique filename
                    $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    
                    // Move the file with error handling
                    if ($file->move($uploadPath, $fileName)) {
                        $gambarReferensi[] = "uploads/custom_order/{$fileName}";
                    } else {
                        throw new \Exception('Failed to upload image');
                    }
                }
            }
            
            DB::beginTransaction();
            
            try {
                // Create record for custom order
                $customOrder = CustomOrder::create([
                    'user_id' => $user->id,
                    'custom_order_unique_id' => 'CST-' . strtoupper(uniqid()),
                    'nama_lengkap' => $validateData['nama_lengkap'],
                    'no_telp' => $validateData['no_telp'],
                    'email' => $validateData['email'],
                    'jenis_baju' => $validateData['jenis_baju'],
                    'sumber_kain' => $validateData['sumber_kain'],
                    'detail_bahan' => $validateData['detail_bahan'] ?? null,
                    'status' => 'pending',
                    'status_pembayaran' => 'belum_bayar',
                    'catatan' => $validateData['catatan'] ?? null,
                    'gambar_referensi' => !empty($gambarReferensi) ? json_encode($gambarReferensi) : null, // Encode sebagai JSON
                ]);
                
                // Simpan data warna dan ukuran
                foreach ($validateData['colors'] as $colorData) {
                    // Buat warna baru untuk custom order ini
                    $color = new \App\Models\Custom_order_colors([
                        'custom_order_id' => $customOrder->id,
                        'color_name' => $colorData['color_name'],
                    ]);
                    $color->save();
                    
                    // Simpan ukuran untuk warna ini
                    foreach ($colorData['sizes'] as $sizeData) {
                        $size = new \App\Models\Custom_order_sizes([
                            'custom_order_id' => $customOrder->id,
                            'custom_order_color_id' => $color->id,
                            'size' => $sizeData['size'],
                            'jumlah' => $sizeData['jumlah'],
                        ]);
                        $size->save();
                    }
                }
                
                DB::commit();
                
                // Load relasi colors dan sizes untuk ditampilkan di response
                $customOrder->load('colors.sizes');
                
                return response()->json([
                    'status' => true,
                    'message' => 'Custom order berhasil dibuat',
                    'data' => $customOrder
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ValidationException $e) {
            // Hapus file yang sudah terupload jika terjadi error validasi
            if (isset($gambarReferensi)) {
                foreach ($gambarReferensi as $path) {
                    if (file_exists(public_path($path))) {
                        unlink(public_path($path));
                    }
                }
            }
            
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Hapus file yang sudah terupload jika terjadi error
            if (isset($gambarReferensi)) {
                foreach ($gambarReferensi as $path) {
                    if (file_exists(public_path($path))) {
                        unlink(public_path($path));
                    }
                }
            }
            
            // Log error for debugging
            Log::error('Custom order creation failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat custom order',
                'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server'
            ], 500);
        }
    }

    public function autoRejectProposals()
    {
        try {
            // Cari custom orders dengan status "pending" yang dibuat lebih dari 2 hari yang lalu
            $twoDaysAgo = now()->subDays(2);
            $pendingOrders = CustomOrder::where('status', 'pending')
                ->where('created_at', '<=', $twoDaysAgo)
                ->get();

            foreach ($pendingOrders as $order) {
                // Ubah status menjadi "ditolak"
                $order->status = 'ditolak';
                $order->save();

                // Log aktivitas penolakan otomatis
                \Illuminate\Support\Facades\Log::info('Custom order automatically rejected', [
                    'custom_order_id' => $order->id,
                    'created_at' => $order->created_at,
                    'rejected_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pending proposals older than 2 days have been rejected.',
                'data' => $pendingOrders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting proposals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function acceptPropose(Request $request)
    {
         // Authorize the action
         $user = User::findOrFail(Auth::id());
         if (!$user || !($user->isOwner() || $user->isAdmin())) {
             return response()->json([
                 'status' => false,
                 'message' => 'Unauthorized access'
             ], 403);
        }
        try {
            // Validate input
            $validatedData = $request->validate([
                'custom_order_id' => 'required|exists:custom_orders,id',
            ]);

            // Find the order
            $customOrder = CustomOrder::findOrFail($validatedData['custom_order_id']);
            
            // Check if the order is in pending status
            if ($customOrder->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Hanya proposal dengan status pending yang dapat disetujui'
                ], 422);
            }

            // Update the status and any additional fields
            $customOrder->status = 'disetujui';
            
            // Record who approved it and when
            $customOrder->responded_by = $user->id;
            $customOrder->responded_at = now();
            

            // Save changes
            $customOrder->save();

            // create transaction 
            return response()->json([
                'status' => true,
                'message' => 'Custom order berhasil disetujui',
                'data' => $customOrder
            ], 200);
            
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Custom order tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Accept custom order failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengubah status custom order',
                'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server'
            ], 500);
        }
    }


    public function RejectResponse(Request $request)
    {
        // Authorize the action
        $user = User::findOrFail(Auth::id());
        if (!$user || !($user->isOwner() || $user->isAdmin())) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }
        try {
            // Validate input
            $validatedData = $request->validate([
                'custom_order_id' => 'required|exists:custom_orders,id',
                'alasan_diTolak' => 'required|text',
            ]);

            // Find the order
            $customOrder = CustomOrder::findOrFail($validatedData['custom_order_id']);
            
            // Check if the order is in pending status
            if ($customOrder->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Hanya proposal dengan status pending yang dapat disetujui'
                ], 422);
            }

            // Update the status and any additional fields
            $customOrder->status = 'ditolak';
            $customOrder->alasan_diTolak = $validatedData['alasan_diTolak'];
            
            // Record who approved it and when
            $customOrder->responded_by = $user->id;
            $customOrder->responded_at = now();
            
            // Save changes
            $customOrder->save();
            
            return response()->json([
                'status' => true,
                'message' => 'Custom order berhasil ditolak',
                'data' => $customOrder
            ], 200);
            
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Custom order tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Reject custom order failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengubah status custom order',
                'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server'
            ], 500);
        }
    }

    
    public function cancelPropose(Request $request)
    {
        // Authorize the action
        $user = User::findOrFail(Auth::id());
        if (!$user || !($user->isOwner() || $user->isAdmin())) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }
        try {
            // Validate input
            $validatedData = $request->validate([
                'custom_order_id' => 'required|exists:custom_orders,id',
                'alasan_diTolak' => 'required|text',
            ]);

            // Find the order
            $customOrder = CustomOrder::findOrFail($validatedData['custom_order_id']);
            
            // Check if the order is in pending status
            if ($customOrder->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Hanya proposal dengan status pending yang dapat disetujui'
                ], 422);
            }

            // Update the status and any additional fields
            $customOrder->status = 'dibatalkan';
            $customOrder->alasan_diTolak = $validatedData['alasan_diTolak'];
            
            // Record who approved it and when
            $customOrder->reponded_by = $user->id;
            $customOrder->reponded_at = now();
            
            // Save changes
            $customOrder->save();
            
            return response()->json([
                'status' => true,
                'message' => 'Custom order berhasil ditolak',
                'data' => $customOrder
            ], 200);
            
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Custom order tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Reject custom order failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengubah status custom order',
                'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server'
            ], 500);
        }
    }

    public function updateStatusToOrder(Request $request, $id){
        $user = User::findOrFail(Auth::id());

        if($user->isAdmin() || $user->isOwner()){
            
            DB::beginTransaction();
            
            try {
                // Validate input
                $validatedData = $request->validate([
                    // 'custom_order_id' => 'required|exists:custom_orders,id',
                    'detail_bahan' => 'sometimes|string|max:255',
                    'total_harga' => 'required|integer|min:0', 
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after:start_date',
                    'payment_method' => 'required|in:BCA,E-Wallet_Dana,Cash'
                ]);

                // Find the custom order
                $customOrder = CustomOrder::with('colors.sizes')
                ->findOrFail($id);

                // Debug the loaded relations
                Log::info('Custom Order Colors:', [
                'order_id' => $customOrder->id,
                'has_colors' => $customOrder->colors ? 'yes' : 'no',
                'colors_count' => $customOrder->colors ? count($customOrder->colors) : 0
                ]);

                // Check if the order is in the correct status (disetujui)
                if ($customOrder->status !== 'disetujui') {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Hanya custom order dengan status disetujui yang dapat difinalisasi'
                ], 422);
                }

                // Calculate estimasi_waktu in days
                $startDate = new \DateTime($validatedData['start_date']);
                $endDate = new \DateTime($validatedData['end_date']);
                $interval = $startDate->diff($endDate);
                $estimasiWaktu = $interval->days . ' hari';

                // Add safety checks before iterating
                $totalQuantity = 0;
                
                // Format color and size data for storage in order
                $colorData = [];
                $sizeData = [];
                
                if ($customOrder->colors && count($customOrder->colors) > 0) {
                    foreach ($customOrder->colors as $color) {
                        $colorName = $color->color_name;
                        $colorData[] = $colorName;
                        
                        if ($color->sizes && count($color->sizes) > 0) {
                            foreach ($color->sizes as $size) {
                                $totalQuantity += $size->jumlah ?? 0;
                                
                                // Store sizes with their quantities by color
                                if (!isset($sizeData[$colorName])) {
                                    $sizeData[$colorName] = [];
                                }
                                $sizeData[$colorName][] = [
                                    'size' => $size->size,
                                    'jumlah' => $size->jumlah
                                ];
                            }
                        }
                    }
                }
                
                // Create a new order without mass assignment
                $order = new Order();
                $order->order_unique_id = 'ORD-CST' . strtoupper(uniqid());
                $order->user_id = $customOrder->user_id ?? $user->id;
                $order->catalog_id = null; // Using null for custom orders
                $order->custom_order_id = $customOrder->id;
                $order->jumlah = $totalQuantity;
                $order->total_harga = $validatedData['total_harga'];
                $order->type = 'Pemesanan';
                $order->status = 'Menunggu_Pembayaran';
                $order->bukti_pembayaran = null;
                
                // Store color and size information as JSON
                $order->color = !empty($colorData) ? json_encode($colorData) : null;
                $order->size = !empty($sizeData) ? json_encode($sizeData) : null;
                
                $order->save();

                // Update the custom order status to 'proses'
                $customOrder->update([
                    'detail_bahan' => $validatedData['detail_bahan'] ?? $customOrder->detail_bahan,
                    'status' => 'proses',
                    'status_pembayaran' => 'belum_bayar',
                    'total_harga' => $validatedData['total_harga'],
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                    'estimasi_waktu' => $estimasiWaktu,
                    'order_id' => $order->order_unique_id,
                ]);

                // Create transaction
                $transaction = transaction::create([
                    'order_id' => $order->id,
                    'transaction_unique_id' => 'TRX-' . strtoupper(uniqid()),
                    'status' => 'pending',
                    'tujuan_transfer' => $this->getPaymentDetails($validatedData['payment_method']),
                    'amount' => $validatedData['total_harga'],
                    'payment_method' => $validatedData['payment_method'],
                ]);
                
                // Update order with transaction ID
                $order->transaction_id = $transaction->id;
                $order->save();
                
                // Update customer's total order count
                if ($customOrder->user_id) {
                    User::where('id', $customOrder->user_id)->increment('total_order');
                }
                
                // Log the activity
                Log::info('Custom order deal finalized', [
                    'admin_id' => $user->id,
                    'admin_name' => $user->name,
                    'custom_order_id' => $customOrder->id,
                    'order_id' => $order->id,
                    'transaction_id' => $transaction->id,
                    'amount' => $validatedData['total_harga'],
                    'payment_method' => $validatedData['payment_method'],
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                    'estimasi_waktu' => $estimasiWaktu,
                    'timestamp' => now()->toDateTimeString()
                ]);

                try {
                    if ($customOrder->email) {
                        Mail::to($customOrder->email)->send(new CustomerorderMail($customOrder, $order));
                    }
                    
                    // Optionally, also notify the admin team
                    $adminEmail = config('contact.recipient_email', 'jrkonveksiemail@gmail.com');
                    Mail::to($adminEmail)->send(new AdminOrderEmail($customOrder, $order));
                    
                } catch (\Exception $mailException) {
                    // Log error but don't fail the process
                    Log::error('Failed to send email notification: ' . $mailException->getMessage());
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Custom order berhasil difinalisasi dan siap untuk pembayaran',
                    'data' => [
                        'custom_order' => $customOrder,
                        'order' => $order,
                        'transaction' => $transaction,
                        'payment_details' => $this->getPaymentDetails($validatedData['payment_method'])
                    ]
                ], 200);
                
            } catch (ValidationException $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            } catch (ModelNotFoundException $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Custom order tidak ditemukan'
                ], 404);
            } catch (\Exception $e) {
                DB::rollBack();
                // Log error for debugging
                Log::error('Finalize custom order failed: ' . $e->getMessage(), [
                    'custom_order_id' => $request->custom_order_id ?? null,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal memfinalisasi custom order',
                    'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server'
                ], 500);
            }
        }
    }

}
