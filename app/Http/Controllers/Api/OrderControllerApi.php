<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\CompleteOrdersMails;
use App\Mail\RecievedByUsersMails;
use App\Mail\SendDeliveryMails;
use App\Mail\VerifPaymentMails;
use App\Models\Catalog;
use App\Models\CustomOrder;
use App\Models\DeliveryProof;
use App\Models\keuangan;
use App\Models\Order;
use App\Models\transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OrderControllerApi extends Controller
{
    private function getPaymentDetails($payment_method)
    {
        $payment_details = [
            'BCA' => 'Bank BCA: 2670342134 a.n. Andi Setiawan',
            'E-Wallet_Dana' => 'DANA: 0857-4851-3790 a.n. Andi Setiawan',
        ];

        return $payment_details[$payment_method] ?? 'Bank BCA: 2670342134 a.n. Andi Setiawan';
    }

    public function getAllCustonAndOrder(Request $request){
        $user = User::findOrFail(Auth::id());
        if($user->isAdmin() || $user->isOwner()){
            try {
                $orders = Order::with(['catalog', 'Transactions', 'customOrder', 'deliveryProof'])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $customOrder = CustomOrder::with(['approvedByUser'])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $transaction = transaction::with(['order', 'order.catalog', 'order.customOrder'])
                    ->orderBy('created_at', 'asc')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'orders' => $orders,
                        'custom_orders' => $customOrder,
                        'transactions' => $transaction
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error retrieving orders',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    }
    
    public function getOrderHaventDone(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        if ($user){
            try {
                $orders = Order::where('status', '!=', 'Selesai')
                    ->where('user_id', $user->id)
                    ->with(['catalog', 'transaction', 'customOrder', 'deliveryProof'])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $customOrder = CustomOrder::where('status', '!=', 'selesai')
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'asc')
                    ->get();
                return response()->json([
                    'success' => true,
                    'data' => [
                        'orders' => $orders,
                        'custom_orders' => $customOrder
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error retrieving orders',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    }

    public function getMonthly(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            try {
                // Get current month and last month
                $currentMonth = now()->format('Y-m');
                $lastMonth = now()->subMonth()->format('Y-m');

                // Calculate revenue and order count for the current month
                $currentMonthData = Order::where('status', 'Selesai')
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->selectRaw('SUM(total_harga) as revenue, COUNT(*) as order_count')
                    ->first();

                // Calculate revenue and order count for the last month
                $lastMonthData = Order::where('status', 'Selesai')
                    ->whereYear('created_at', now()->subMonth()->year)
                    ->whereMonth('created_at', now()->subMonth()->month)
                    ->selectRaw('SUM(total_harga) as revenue, COUNT(*) as order_count')
                    ->first();

                // Calculate the difference and percentage change for revenue
                $difference = $currentMonthData->revenue - $lastMonthData->revenue;
                $percentageChange = $lastMonthData->revenue > 0 
                    ? ($difference / $lastMonthData->revenue) * 100 
                    : ($currentMonthData->revenue > 0 ? 100 : 0);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'current_month' => $currentMonth,
                        'last_month' => $lastMonth,
                        'current_month_revenue' => (int) $currentMonthData->revenue,
                        'current_month_order_count' => $currentMonthData->order_count,
                        'last_month_revenue' => $lastMonthData->revenue,
                        'last_month_order_count' => $lastMonthData->order_count,
                        'difference' => $difference,
                        'percentage_change' => round($percentageChange, 2)
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error retrieving monthly data',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    }

    public function index() {
        $user = User::findOrFail(Auth::id());
        if($user->isUser()) {
           try{
                $orders = Order::with(['catalog', 'transaction', 'customOrder', 'customOrder.approvedByUser', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $orders
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error retrieving orders',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else if($user->isAdmin() || $user->isOwner()) {
            try {
                $orders = Order::with(['catalog', 'transaction', 'customOrder', 'customOrder.approvedByUser', 'user'])
                    ->orderBy('created_at', 'asc')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $orders
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error retrieving orders',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function CartIndex()
    {
        $user = User::findOrFail(Auth::id());
        
        try {
            $order_items = Order::where('user_id', $user->id)
                ->leftJoin('transactions', 'orders.transaction_id', '=', 'transactions.id')
                ->select('orders.id as id', 'orders.catalog_id', 'orders.jumlah', 'orders.total_harga', 'orders.status',
                        'orders.bukti_pembayaran', 'orders.transaction_id')
                ->where('orders.bukti_pembayaran', null)
                ->where('orders.status', 'Menunggu_Pembayaran')
                ->with('catalog', 'catalog.sizes', 'catalog.colors')
                ->get()
                ->map(function($item) {
                    return [
                        'order' => $item
                        // 'id' => $item->id,
                        // 'catalog_id' => $item->catalog_id,
                        // 'status' => $item->status,
                        // 'product_name' => $item->catalog->nama_katalog,
                        // 'price' => $item->catalog->price,
                        // 'quantity' => $item->jumlah,
                        // 'subtotal' => $item->jumlah * $item->catalog->price,
                        // 'image' => $item->catalog->gambar,
                        // 'catalog' =>$item->catalog
                    ];
                });
            if($order_items->isEmpty()) {
                return response()->json([
                    'message' => 'Cart is empty',
                    'data' => []
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Data retrieved successfully',
                    'data' => $order_items
                ], 200);
            }
          
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($cart_id)
    {
        $user = User::findOrFail(Auth::id());
        try {
            $order_items = Order::where('user_id', $user->id)
                ->where('order_unique_id', $cart_id)
                ->with('catalog', 'transaction', 'color', 'size')
                ->first();
            if($order_items) {
                return response()->json([
                    'message' => 'Data retrieved successfully',
                    'data' => $order_items
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Cart not found',
                    'data' => []
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function addCart(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        try {
            $validated = $request->validate([
                'catalog_id' => 'required|exists:catalogs,id',
                'jumlah' => 'required|integer|min:1',
                'size' => 'required|exists:catalog_sizes,id',
                'color' => 'required|exists:catalog_colors,id',
            ]);
            
            $catalog = Catalog::findOrFail($validated['catalog_id']);
            
            // Check stock availability
            if ($catalog->stok < $validated['jumlah']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock not available'
                ], 400);
            }
            
            // Calculate total price
            $totalPrice = $catalog->price * $validated['jumlah'];
            
            // Check if item already in cart with the same size and color
            $addCart = Order::where('user_id', $user->id)
                        ->where('catalog_id', $validated['catalog_id'])
                        ->where('status', 'Menunggu_Pembayaran')
                        ->where('size', $validated['size'])
                        ->where('color', $validated['color'])
                        ->first();
            if ($addCart) {
                // Update existing cart item
                $addCart->jumlah += $validated['jumlah'];
                $addCart->total_harga = $catalog->price * $addCart->jumlah;
                $addCart->save();
            } else {
                // Create new cart item
                $addCart = Order::create([
                    'user_id' => $user->id,
                    'catalog_id' => $validated['catalog_id'],
                    'jumlah' => $validated['jumlah'],
                    'total_harga' => $totalPrice,
                    'size' => $validated['size'],
                    'color' => $validated['color'],
                    'status' => 'Menunggu_Pembayaran',
                    'bukti_pembayaran' => null
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'data' => $addCart
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding item to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function removeItems(Request $request)
    {
        //
        $user = User::findOrFail(Auth::id());
        try {
            if($user->orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty'
                ], 404);
            }else{
                
            }
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);
            
            $order = Order::where('user_id', $user->id)
                        ->where('id', $validated['order_id'])
                        ->where('status', 'Menunggu_Pembayaran')
                        ->first();
            
            if(is_null($order)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in cart'
                ], 404);
            } else {
                $order->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from cart'
                ], 200);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addQuantity(Request $request){
        $user = User::findOrFail(Auth::id());
        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'jumlah' => 'required|integer|min:1',
            ]);
            
            $order = Order::where('user_id', $user->id)
                        ->where('id', $validated['order_id'])
                        ->where('status', 'Menunggu_Pembayaran')
                        ->first();
            
            if(is_null($order)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in cart'
                ], 404);
            } else {
                $order->jumlah += $validated['jumlah'];
                $order->total_harga = $order->catalog->price * $order->jumlah;
                $order->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Quantity updated successfully',
                    'data' => $order
                ], 200);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function checkout(Request $request)
    {
        try {
            $user = User::findOrFail(Auth::id());
            
            // Validate basic inputs
            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|in:BCA,E-Wallet_Dana,Cash',
                'type' => 'required|in:Pembelian,Pemesanan',
                'catatan' => 'nullable|string|max:500',
                'order_ids' => 'required|array',  // Array of order IDs to checkout
                'order_ids.*' => 'exists:orders,id'  // Validate each order ID exists
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            // Get selected cart items that belong to the user and have 'Menunggu_Pembayaran' status
            $cartItems = Order::where('user_id', $user->id)
                        ->where('status', 'Menunggu_Pembayaran')
                        ->whereIn('id', $request->order_ids)
                        ->get();
            
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid items selected for checkout'
                ], 400);
            }
            
            // Verify all selected orders belong to the user
            if ($cartItems->count() !== count($request->order_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more selected orders are invalid or do not belong to you'
                ], 403);
            }
            
            // Calculate total amount
            $totalAmount = $cartItems->sum('total_harga');
            
            // Get the first order ID to use as a reference
            $firstOrderId = $cartItems->first()->id;
            
            // Create transaction
            $transaction = Transaction::create([
                'order_id'=> $firstOrderId,
                'status' => 'pending',
                'tujuan_transfer' => $this->getPaymentDetails($request->payment_method),
                'amount' => $totalAmount,
                'payment_method' => $request->payment_method,
            ]);

            // Update status of all selected orders to 'Menunggu_Konfirmasi', add unique_id, and catatan
            foreach ($cartItems as $item) {
                $item->status = 'Menunggu_Konfirmasi';
                $item->order_unique_id = 'ORD-CTL' . strtoupper(uniqid()); // Generate unique_id
                $item->transaction_id = $transaction->id;
                $item->type = $request->type;
                $item->catatan = $request->catatan; // Add catatan to the order
                $item->save();
            }
            
            // Update user's total_order
            $user->total_order += 1;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Checkout successful',
                'data' => [
                    'transaction' => $transaction,
                    'orders' => $cartItems,
                    'payment_details' => $this->getPaymentDetails($request->payment_method),
                    'total_amount' => $totalAmount
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during checkout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadPaymentProof(Request $request)
    {
    $user = User::findOrFail(Auth::id());
        
        try {
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|exists:transactions,id',
                'bukti_pembayaran' => 'required|image|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $transaction = Transaction::findOrFail($request->transaction_id);
            
            // Verify that this transaction belongs to the user
            $orderBelongsToUser = Order::where('transaction_id', $transaction->id)
                                ->where('user_id', $user->id)
                                ->exists();
            
            if (!$orderBelongsToUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Upload file bukti pembayaran
            if ($request->hasFile('bukti_pembayaran')) {
                $path = $request->file('bukti_pembayaran')->store('payment_proofs', 'public');
                
                // Update transaction
                $transaction->bukti_transfer = $path;
                $transaction->save();
                
                // Update all orders associated with this transaction
                Order::where('transaction_id', $transaction->id)
                    ->update([
                        'bukti_pembayaran' => $path,
                        'status' => 'Menunggu_Konfirmasi'
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload',
                'data' => $transaction
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading payment proof',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function convertPayment($payment_method)
    {
        $eWallets = ['E-Wallet_Dana'];
        $bankTransfers = ['BCA', 'BNI'];
        $cash = ['Cash'];

        if (in_array($payment_method, $eWallets)) {
            return 'E-Wallet';
        } elseif (in_array($payment_method, $bankTransfers)) {
            return 'Transfer';
        } elseif (in_array($payment_method, $cash)) {
            return 'Cash';
        }

        return 'Unknown';
    }

    // public function updateStatus(Request $request, $id)
    // {
    //     $user = User::findOrFail(Auth::id());

    //     if($user->isAdmin() || $user->isOwner())
    // }


    public function sendToDelivery(Request $request, $id)
    {
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            try {
                $validator = Validator::make($request->all(), [
                    'status' => 'required|in:Sedang_Dikirim,Sudah_Terkirim',
                    'image' => 'nullable|image|max:2048',
                    'description' => 'nullable|string',
                    'receiver_name' => 'nullable|string',
                    'notes' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                $order = Order::with(['catalog', 'customOrder', 'user'])->findOrFail($id);
                
                // Check if custom_order_id is provided and exists
                $customOrder = $order->customOrder ?? null;
                
                // Check if order status is valid for this action
                if($order->status != 'Diproses') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order cannot be sent to delivery. Current status: ' . $order->status
                    ], 400);
                }
                
                // Update status sesuai request
                $order->status = $request->status;
                
                if ($customOrder) {
                    $customOrder->status = 'Selesai';
                    $customOrder->save();
                }
                
                $order->save();
                
                // Upload bukti pengiriman jika ada
                $deliveryProof = null;
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('delivery_proofs', 'public');
                    
                    // Create delivery proof
                    $deliveryProof = DeliveryProof::create([
                        'order_id' => $order->id,
                        'delivery_proof_unique_id' => 'DVPR-' . strtoupper(uniqid()),
                        'admin_id' => $user->id,
                        'image_path' => $imagePath,
                        'description' => $request->description,
                        'delivery_date' => now(),
                        'receiver_name' => $request->receiver_name ?? 'Pelanggan',
                        'notes' => $request->notes,
                        'status' => $request->status
                    ]);
                }
                
                // Get customer data
                $customer = User::findOrFail($order->user_id);
                
                // Send email notification to customer based on status
                try {
                    if ($request->status == 'Sedang_Dikirim') {
                        // Use SendDeliveryMails for in-transit orders
                        Mail::to($customer->email)->send(new SendDeliveryMails($order, $customOrder ?? new CustomOrder(), $customer));
                        Log::info('Delivery notification email sent to customer: ' . $customer->email);
                    } else if ($request->status == 'Sudah_Terkirim' && $deliveryProof) {
                        // Use RecievedByUsersMails for delivered orders with proof
                        Mail::to($customer->email)->send(new RecievedByUsersMails($order, $deliveryProof, $customer));
                        Log::info('Delivery receipt email sent to customer: ' . $customer->email);
                    }
                } catch (\Exception $emailException) {
                    Log::error('Error sending delivery email: ' . $emailException->getMessage());
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Order status updated successfully and notification email sent',
                    'data' => [
                        'order' => $order,
                        'delivery_proof' => $deliveryProof
                    ]
                ], 200);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating order status',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    }

    // Untuk admin verifikasi pembayaran
    // public function AdminVerifPayment(Request $request, $id)
    // {
    //     $user = User::findOrFail(Auth::id());
        
    //     if ($user->isOwner() || $user->isAdmin()) {
    //         try {
    //             DB::beginTransaction();
                
    //             $transaction = Transaction::findOrFail($id);
                
    //             if ($request->status == 'approve') {
    //                 $transaction->status = 'success';
    //                 $transaction->save();
    //                 // Update all orders associated with this transaction
    //                 $orders = Order::where('transaction_id', $transaction->id)->get();
                    
    //                 $catalogIds = $orders->pluck('catalog_id')->toArray();
    //                 $catalogs = Catalog::whereIn('id', $catalogIds)->get();
    //                 $customOrderIds = $orders->pluck('custom_order_id')->unique()->toArray();
    //                 $customOrders = CustomOrder::whereIn('id', $customOrderIds)->get();

    //                 foreach ($orders as $order) {
    //                     $order->status = 'Diproses';
    //                     $order->save();
                        
    //                     // Catat ke tabel keuangan untuk setiap order yang diverifikasi
    //                     $keuangan = new keuangan();
    //                     $keuangan->catalog_id = $order->catalog_id;
    //                     $keuangan->order_id = $order->id;
    //                     $keuangan->user_id = $order->user_id;
    //                     $catalog = $catalogs->where('id', $order->catalog_id)->first();
    //                     $customOrder = $customOrders->where('id', $order->custom_order_id)->first();
    //                     $keuangan->keterangan = 'Pembayaran ' . ($catalog ? "Order {$catalog->nama_katalog}" : ($customOrder ? "Custom Order {$customOrder->jenis_baju}" : 'Unknown Catalog'));
    //                     $keuangan->jenis_pembayaran = $this->convertPayment($transaction->payment_method); // Default value, can be adjusted
    //                     $keuangan->nominal = $order->total_harga;
    //                     $keuangan->tanggal = now();
    //                     $keuangan->jenis_keuangan = 'pemasukan';
    //                     $keuangan->save();
    
    //                     // Tambahkan jumlah barang yang terjual ke kolom `sold` di tabel `catalogs`
    //                     if ($catalog) {
    //                         $catalog->sold += $order->jumlah;
    //                         $catalog->save();
    //                     }
    //                 }

    //                 // Update status_payment of custom_orders to 'sudah_bayar'
    //                 foreach ($customOrders as $customOrder) {
    //                     $customOrder->status_pembayaran = 'sudah_bayar';
    //                     $customOrder->save();
    //                 }
                    
    //             } else {
    //                 $transaction->status = 'failure';
                    
    //                 // Update all orders associated with this transaction
    //                 Order::where('transaction_id', $transaction->id)
    //                     ->update(['status' => 'Menunggu_Pembayaran']);
    //             }
                
    //             DB::commit();
        
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Payment verification successful',
    //                 'data' => $transaction
    //             ]);
                
    //         } catch (\Exception $e) {
    //             DB::rollBack();
                
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Error verifying payment',
    //                 'error' => $e->getMessage()
    //             ], 500);
    //         }
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unauthorized'
    //         ], 403);
    //     }
    // }

    public function AdminVerifPayment(Request $request, $id)
    {
        $user = User::findOrFail(Auth::id());
        
        if ($user->isOwner() || $user->isAdmin()) {
            try {
                DB::beginTransaction();
                
                $transaction = Transaction::with(['order', 'order.catalog', 'order.customOrder', 'order.user'])
                    ->findOrFail($id);
                // Verify that this transaction belongs to the user
                
                $orders = Order::where('transaction_id', $transaction->id)
                ->with(['catalog', 'customOrder', 'user'])
                ->get();
                
                // Get all custom orders related to these orders
                $customOrderIds = $orders->pluck('custom_order_id')->unique()->filter()->toArray();
                $customOrders = !empty($customOrderIds) ? CustomOrder::whereIn('id', $customOrderIds)->with('user')->get() : collect();
                
                // Get catalogs
                $catalogIds = $orders->pluck('catalog_id')->toArray();
                $catalogs = Catalog::whereIn('id', $catalogIds)->get();

                // Get customer for email - prefer custom order's user if available
                $customer = null;
                if ($customOrders->isNotEmpty() && $customOrders->first()->user) {
                    $customer = $customOrders->first()->user;
                } else {
                    $customer = $orders->first()->user;
                }
                // Ensure we have a customer to email
                if (!$customer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer not found for this transaction'
                    ], 404);
                }
                if ($request->status == 'approve') {
                    $transaction->status = 'success';
                    $transaction->save();
                    
                    foreach ($orders as $order) {
                        $order->status = 'Diproses';
                        $order->save();
                        
                        // Catat ke tabel keuangan untuk setiap order yang diverifikasi
                        $keuangan = new keuangan();
                        $keuangan->catalog_id = $order->catalog_id;
                        $keuangan->order_id = $order->id;
                        $keuangan->user_id = $order->user_id;
                        $catalog = $catalogs->where('id', $order->catalog_id)->first();
                        $customOrder = $customOrders->where('id', $order->custom_order_id)->first();
                        $keuangan->keterangan = 'Pembayaran ' . ($catalog ? "Order {$catalog->nama_katalog}" : ($customOrder ? "Custom Order {$customOrder->jenis_baju}" : 'Unknown Catalog'));
                        $keuangan->jenis_pembayaran = $this->convertPayment($transaction->payment_method);
                        $keuangan->nominal = $order->total_harga;
                        $keuangan->tanggal = now();
                        $keuangan->jenis_keuangan = 'pemasukan';
                        $keuangan->save();

                        // Tambahkan jumlah barang yang terjual ke kolom `sold` di tabel `catalogs`
                        if ($catalog) {
                            $catalog->sold += $order->jumlah;
                            $catalog->save();
                        }
                    }

                    // Update status_payment of custom_orders to 'sudah_bayar'
                    foreach ($customOrders as $customOrder) {
                        $customOrder->status_pembayaran = 'sudah_bayar';
                        $customOrder->save();
                    }
                    
                } else {
                    $transaction->status = 'failure';
                    
                    // Update all orders associated with this transaction
                    Order::where('transaction_id', $transaction->id)
                        ->update(['status' => 'Menunggu_Pembayaran']);
                }
                
                DB::commit();
                
                // Send email notification to customer
                try {
                    Mail::to($customer->email)->send(new VerifPaymentMails(
                        $transaction, 
                        $orders, 
                        $customer, 
                        $request->status
                    ));
                    
                    Log::info('Payment verification email sent to customer: ' . $customer->email);
                } catch (\Exception $emailException) {
                    // Log email sending error but continue with the process
                    Log::error('Error sending payment verification email: ' . $emailException->getMessage());
                }
        
                return response()->json([
                    'success' => true,
                    'message' => 'Payment verification successful and notification email sent',
                    'data' => $transaction
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error verifying payment',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    }
    public function Riwayat(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        
        try {
            // Query untuk mengambil semua pesanan dengan status 'Selesai' berdasarkan user_id  
            $orders = Order::where('user_id', $user->id)
                ->where('status', 'Selesai')
                ->with(['catalog', 'transaction', 'customOrder'])
                ->orderBy('created_at', 'asc')
                ->get();

            // Buat variable untuk catalogs dan customOrders
            $catalogIds = $orders->pluck('catalog_id')->unique()->toArray();
            $customOrderIds = $orders->pluck('custom_order_id')->unique()->toArray();
            $catalogs = Catalog::whereIn('id', $catalogIds)->get();
            $customOrders = CustomOrder::whereIn('id', $customOrderIds)->get();

            $groupedOrders = $orders->groupBy('transaction_id')->map(function($items, $transactionId) use ($catalogs, $customOrders) {
                $transaction = Transaction::find($transactionId);
                $firstItem = $items->first();
                
                return [
                    'transaction_id' => $transactionId,
                    'order_id' => $transaction ? $transaction->order_id : null,
                    'date' => $firstItem->created_at->format('Y-m-d H:i:s'),
                    'status' => $firstItem->status,
                    'payment_method' => $transaction ? $transaction->payment_method : null,
                    'total_amount' => $transaction ? $transaction->amount : null,
                    'bukti_pembayaran' => $transaction ? $transaction->bukti_transfer : null,
                    'alamat' => $firstItem->alamat,
                    'items' => $items->map(function($item) use ($catalogs, $customOrders) {
                        $catalog = $catalogs->where('id', $item->catalog_id)->first();
                        $customOrder = $customOrders->where('id', $item->custom_order_id)->first();
                        
                        $productName = $catalog 
                            ? $catalog->nama_katalog 
                            : ($customOrder ? $customOrder->jenis_baju : 'Unknown Product');
                        
                        return [
                            'id' => $item->id,
                            'product_name' => $customOrder ? $customOrder->jenis_baju : $productName,
                            'price' => $customOrder ? $customOrder->total_harga : ($catalog ? $catalog->price : 0),
                            'quantity' => $item->jumlah,
                            'subtotal' => $item->total_harga,
                            'image' => $catalog ? $catalog->gambar : ($customOrder ? $customOrder->gambar_referensi : null),
                        ];
                    }),
                    'delivery_proof' => DeliveryProof::where('order_id', $items->first()->id)->first(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $groupedOrders
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Update order status to shipped and create delivery proof
     */
    public function RecievedUser(Request $request, $id)
    {
        $user = User::findOrFail(Auth::id());
            
        // Verify admin role
        if($user->isOwner() || $user->isAdmin()) {
            // User is admin or owner
            try {
                $validator = Validator::make($request->all(), [
                    'image' => 'required|image|max:2048',
                    'description' => 'nullable|string',
                    'receiver_name' => 'required|string',
                    'notes' => 'nullable|string',
                ]);
    
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
    
                // Find the order
                $order = Order::findOrFail($id);
                
                // Check if order is in the correct status to be shipped
                if ($order->status != 'Sedang_Dikirim') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order cannot be shipped. Current status: ' . $order->status
                    ], 400);
                }
                
                // Upload delivery proof image
                $imagePath = $request->file('image')->store('delivery_proofs', 'public');
                
                // Create delivery proof
                $deliveryProof = DeliveryProof::create([
                    'order_id' => $order->id,
                    'delivery_proof_unique_id' => 'DVPR-' . strtoupper(uniqid()),
                    'admin_id' => $user->id,
                    'image_path' => $imagePath,
                    'description' => $request->description,
                    'delivery_date' => now(),
                    'receiver_name' => $request->receiver_name,
                    'notes' => $request->notes,
                    'status' => 'delivered'
                ]); 
                
                // Update order status
                $order->status = 'Sudah_Terkirim';
                $order->save();
                
                // Load related customer data
                $customer = User::findOrFail($order->user_id);
                
                // Send email notification to customer
                try {
                    Mail::to($customer->email)->send(new RecievedByUsersMails($order, $deliveryProof, $customer));
                    // Log successful email dispatch
                    Log::info('Delivery email sent to customer: ' . $customer->email);
                } catch (\Exception $emailException) {
                    // Log email sending error but continue with the process
                    Log::error('Error sending delivery email: ' . $emailException->getMessage());
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Order has been shipped successfully and notification email sent',
                    'data' => [
                        'order' => $order,
                        'delivery_proof' => $deliveryProof
                    ]
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating order status',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    }

    /**
     * Get all orders with delivery status
     */
    public function getOrdersWithDeliveryStatus()
    {
    $user = User::findOrFail(Auth::id());
        // Verify admin role
        if ($user->isOwner() || $user->isAdmin()) {
            try {
                $orders = Order::whereIn('status', ['Diproses', 'Dikirim', 'Selesai'])
                    ->with(['catalog', 'transaction', 'user', 'deliveryProof', 'customOrder'])
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function($order) {
                        return [
                            'id' => $order->id,
                            'transaction_id' => $order->transaction_id,
                            'user' => [
                                'id' => $order->user->id,
                                'name' => $order->user->name,
                                'email' => $order->user->email
                            ],
                            'product' => $order->customOrder ? [
                                'id' => $order->customOrder->id,
                                'name' => $order->customOrder->jenis_baju,
                                'price' => $order->customOrder->total_harga,
                                'image' => $order->customOrder->gambar_referensi
                            ] : [
                                'id' => $order->catalog->id,
                                'name' => $order->catalog->nama_katalog,
                                'price' => $order->catalog->price,
                                'image' => $order->catalog->gambar
                            ],
                            'quantity' => $order->jumlah,
                            'total_price' => $order->total_harga,
                            'address' => $order->alamat,
                            'status' => $order->status,
                            'type' => $order->type,
                            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                            'delivery_info' => $order->deliveryProof ? [
                                'id' => $order->deliveryProof->id,
                                'image' => $order->deliveryProof->image_path,
                                'admin_id' => $order->deliveryProof->admin_id,
                                'description' => $order->deliveryProof->description,
                                'delivery_date' => $order->deliveryProof->delivery_date,
                                'receiver_name' => $order->deliveryProof->receiver_name,
                                'notes' => $order->deliveryProof->notes,
                                'status' => $order->deliveryProof->status
                            ] : null
                        ];
                    });
                
                return response()->json([
                    'success' => true,
                    'data' => $orders
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error retrieving orders',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else{
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
       
    }

    /**
     * Mark order as completed
     */
    public function completeOrder($id)
    {
        $user = User::findOrFail(Auth::id());
        
        // Verify admin role
        if ($user->isOwner() || $user->isAdmin()) {
        
            try {
                // Find the order
                $order = Order::findOrFail($id);
                
                // Check if order is in the correct status to be completed
                if ($order->status != 'Sudah_Terkirim') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order cannot be completed. Current status: ' . $order->status
                    ], 400);
                }
                
                // Update order status
                $order->status = 'Selesai';
                $order->save();
                
                // Update catalog stock if type is Pembelian and catalog exists
                if ($order->type === 'Pembelian' && $order->catalog_id) {
                    $catalog = Catalog::findOrFail($order->catalog_id);
                    $catalog->stok -= $order->jumlah;
                    $catalog->save();
                }
                
                // Get customer data
                $customer = User::findOrFail($order->user_id);
                
                // Send email notification to customer
                try {
                    Mail::to($customer->email)->send(new CompleteOrdersMails($order, $customer));
                    
                    // Log successful email dispatch
                    Log::info('Order completion email sent to customer: ' . $customer->email);
                } catch (\Exception $emailException) {
                    // Log email sending error but continue with the process
                    Log::error('Error sending order completion email: ' . $emailException->getMessage());
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Order has been completed successfully and notification email sent',
                    'data' => $order
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error completing order',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    }
    /**
     * User confirming order receipt
     */
    public function confirmReceipt($id)
    {
        $user = User::findOrFail(Auth::id());
        
        try {
            // Find the order
            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or not authorized'
                ], 404);
            }
            
            // Check if order is in the correct status to be confirmed
            if ($order->status != 'Sudah_Terkirim') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be confirmed. Current status: ' . $order->status
                ], 400);
            }
            
            // Update order status
            $order->status = 'Selesai';
            $order->save();
            
            // Update catalog stock if type is Pembelian
            if ($order->type === 'Pembelian') {
                $catalog = Catalog::findOrFail($order->catalog_id);
                $catalog->stok -= $order->jumlah;
                $catalog->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Order receipt confirmed successfully',
                'data' => $order
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error confirming receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get delivery proof details
     */
    public function getDeliveryProof($id)
    {
    $user = User::findOrFail(Auth::id());
        
        try {
            $order = Order::findOrFail($id);
            
            // Check if user has permission to view this order
            if ($user->isOwner() || $user->isAdmin()) {
                $deliveryProof = DeliveryProof::where('order_id', $id)->first();
            
                if (!$deliveryProof) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Delivery proof not found'
                    ], 404);
                }
                
                // Get admin details
                $admin = User::findOrFail($deliveryProof->admin_id);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'delivery_proof' => $deliveryProof,
                        'admin' => [
                            'id' => $admin->id,
                            'name' => $admin->name
                        ],
                        'order_status' => $order->status
                    ]
                ]);
            }else{
                
            }
            
           
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving delivery proof',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
  
}
