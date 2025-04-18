<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\DeliveryProof;
use App\Models\keuangan;
use App\Models\Order;
use App\Models\transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderControllerApi extends Controller
{
    private function getPaymentDetails($payment_method)
    {
        $payment_details = [
            'bank_transfer' => 'Bank BCA: 1234567890 a.n. Nama Toko',
            'gopay' => 'GoPay: 081234567890 a.n. Nama Toko',
            'ovo' => 'OVO: 081234567890 a.n. Nama Toko',
            'dana' => 'DANA: 081234567890 a.n. Nama Toko',
        ];

        return $payment_details[$payment_method] ?? 'Bank BCA: 1234567890 a.n. Nama Toko';
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
                ->select('orders.id as id', 'orders.catalog_id', 'orders.jumlah', 'orders.total_harga', 
                        'orders.bukti_pembayaran', 'orders.transaction_id')
                ->where('orders.bukti_pembayaran', null)
                ->with('catalog')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'catalog_id' => $item->catalog_id,
                        'product_name' => $item->catalog->nama_katalog,
                        'price' => $item->catalog->harga,
                        'quantity' => $item->jumlah,
                        'subtotal' => $item->jumlah * $item->catalog->harga,
                        'image' => $item->catalog->gambar,
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
            $totalPrice = $catalog->harga * $validated['jumlah'];
            
            // Check if item already in cart
            $addCart = Order::where('user_id', $user->id)
                        ->where('catalog_id', $validated['catalog_id'])
                        ->where('status', 'Menunggu Pembayaran')
                        ->first();
            
            if ($addCart) {
                // Update existing cart item
                $addCart->jumlah += $validated['jumlah'];
                $addCart->total_harga = $catalog->harga * $addCart->jumlah;
                $addCart->bukti_pembayaran = null;
                $addCart->save();
            } else {
                // Create new cart item
                $addCart = Order::create([
                    'user_id' => $user->id,
                    'catalog_id' => $validated['catalog_id'],
                    'jumlah' => $validated['jumlah'],
                    'total_harga' => $totalPrice,
                    'alamat' => 'Depok', // Default alamat value
                    'status' => 'Menunggu Pembayaran',
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
                'message' => 'Error',
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
                        ->where('status', 'Menunggu Pembayaran')
                        ->first();
            
            if($order->isEmpty()) {
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

    /**
     * Display the specified resource.
     */
    public function checkout(Request $request)
    {
    $user = User::findOrFail(Auth::id());
        
        try {
            if($user->orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty'
                ], 400);
            } else if($user){
                $validator = Validator::make($request->all(), [
                    'payment_method' => 'required|in:bank_transfer,gopay,ovo,dana',
                    'alamat' => 'required|string',
                    'type' => 'required|in:Pembelian,Pemesanan',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                
                // Get all cart items
                $cartItems = Order::where('user_id', $user->id)
                            ->where('status', 'Menunggu Pembayaran')
                            ->get();
                
                if($cartItems->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart is empty'
                    ], 400);
                }
                
                // Calculate total amount
                $totalAmount = $cartItems->sum('total_harga');
                
                // Get the first order ID to use as a reference
                $firstOrderId = $cartItems->first()->id;
                
                // Create transaction
                $transaction = transaction::create([
                    'order_id' => $firstOrderId,
                    'status' => 'pending',
                    'tujuan_transfer' => $this->getPaymentDetails($request->payment_method),
                    'amount' => $totalAmount,
                    'payment_method' => $request->payment_method,
                ]);
                
                // Update all cart items with transaction ID and address
                foreach ($cartItems as $item) {
                    $item->transaction_id = $transaction->id;
                    $item->alamat = $request->alamat;
                    $item->type = $request->type;
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
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
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
                        'status' => 'Menunggu Konfirmasi'
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

    // Untuk admin verifikasi pembayaran
    public function AdminVerifPayment(Request $request, $id)
    {
    $user = User::findOrFail(Auth::id());
        
        // Verify admin role
        if ($user->role != 'admin' && $user->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:approve,reject',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            $transaction = Transaction::findOrFail($id);
            
            if ($request->status == 'approve') {
                $transaction->status = 'success';
                
                // Update all orders associated with this transaction
                $orders = Order::where('transaction_id', $transaction->id)
                    ->get();
                    
                foreach ($orders as $order) {
                    $order->status = 'Diproses';
                    $order->save();
                    
                    // Catat ke tabel keuangan untuk setiap order yang diverifikasi
                    $keuangan = new keuangan();
                    $keuangan->order_id = $order->id;
                    $keuangan->nama_keuangan = 'Pembayaran Order #' . $order->id;
                    $keuangan->nominal = $order->total_harga; // Pastikan kolom ini ada di tabel orders
                    $keuangan->tanggal = now();
                    $keuangan->jenis_keuangan = 'pemasukan';
                    $keuangan->save();
                }
                
            } else {
                $transaction->status = 'failure';
                
                // Update all orders associated with this transaction
                Order::where('transaction_id', $transaction->id)
                    ->update(['status' => 'Menunggu Pembayaran']);
            }
            
            $transaction->save();
            
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Payment verification successful',
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
    }

    public function getMyOrders()
    {
    $user = User::findOrFail(Auth::id());
        
        try {
            $orders = Order::where('user_id', $user->id)
                ->where('status', '!=', 'Menunggu Pembayaran')
                ->with(['catalog', 'transaction'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            $groupedOrders = $orders->groupBy('transaction_id')->map(function($items, $transactionId) {
                $transaction = Transaction::findOrFail($transactionId);
                $firstItem = $items->first();
                
                return [
                    'transaction_id' => $transactionId,
                    'order_id' => $transaction->order_id,
                    'date' => $firstItem->created_at->format('Y-m-d H:i:s'),
                    'status' => $firstItem->status,
                    'payment_method' => $transaction->payment_method,
                    'total_amount' => $transaction->amount,
                    'bukti_pembayaran' => $transaction->bukti_transfer,
                    'alamat' => $firstItem->alamat,
                    'items' => $items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->catalog->nama_katalog,
                            'price' => $item->catalog->harga,
                            'quantity' => $item->jumlah,
                            'subtotal' => $item->total_harga,
                            'image' => $item->catalog->gambar,
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
    public function shipOrder(Request $request, $id)
    {
    $user = User::findOrFail(Auth::id());
        
        // Verify admin role
        if ($user->role != 'admin' && $user->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
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
            if ($order->status != 'Diproses') {
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
                'admin_id' => $user->id,
                'image_path' => $imagePath,
                'description' => $request->description,
                'delivery_date' => now(),
                'receiver_name' => $request->receiver_name,
                'notes' => $request->notes,
                'status' => 'delivered'
            ]);
            
            // Update order status
            $order->status = 'Dikirim';
            $order->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Order has been shipped successfully',
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
    }

    /**
     * Get all orders with delivery status
     */
    public function getOrdersWithDeliveryStatus()
    {
    $user = User::findOrFail(Auth::id());
        
        // Verify admin role
        if ($user->role != 'admin' && $user->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        try {
            $orders = Order::whereIn('status', ['Diproses', 'Dikirim', 'Selesai'])
                ->with(['catalog', 'transaction', 'user', 'deliveryProof'])
                ->orderBy('created_at', 'desc')
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
                        'product' => [
                            'id' => $order->catalog->id,
                            'name' => $order->catalog->nama_katalog,
                            'price' => $order->catalog->harga,
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
    }

    /**
     * Mark order as completed
     */
    public function completeOrder($id)
    {
    $user = User::findOrFail(Auth::id());
        
        // Verify admin role
        if ($user->role != 'admin' && $user->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        try {
            // Find the order
            $order = Order::findOrFail($id);
            
            // Check if order is in the correct status to be completed
            if ($order->status != 'Dikirim') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be completed. Current status: ' . $order->status
                ], 400);
            }
            
            // Update order status
            $order->status = 'Selesai';
            $order->save();
            
            // Update catalog stock if type is Pembelian
            if ($order->type == 'Pembelian') {
                $catalog = Catalog::findOrFail($order->catalog_id);
                $catalog->stok -= $order->jumlah;
                $catalog->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Order has been completed successfully',
                'data' => $order
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error completing order',
                'error' => $e->getMessage()
            ], 500);
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
            if ($order->status != 'Dikirim') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be confirmed. Current status: ' . $order->status
                ], 400);
            }
            
            // Update order status
            $order->status = 'Selesai';
            $order->save();
            
            // Update catalog stock if type is Pembelian
            if ($order->type == 'Pembelian') {
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
            if ($user->role != 'admin' && $user->role != 'owner' && $order->user_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
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
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving delivery proof',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
