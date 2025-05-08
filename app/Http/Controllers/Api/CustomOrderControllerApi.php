<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AdminOrderEmail;
use App\Mail\CustomerorderMail;
use App\Models\CustomOrder;
use App\Models\Order;
use App\Models\transaction;
use App\Models\User;
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
    private function getPaymentDetails($payment_method)
    {
        $payment_details = [
            'BCA' => 'Bank BCA: 2670342134 a.n. Andi Setiawan',
            'E-Wallet_Dana' => 'DANA: 0857-4851-3790 a.n. Andi Setiawan',
        ];

        return $payment_details[$payment_method] ?? 'Bank BCA: 2670342134 a.n. Andi Setiawan';
    }

    public function index(){
        $user = User::findOrFail(Auth::id());
        if($user->isAdmin() || $user->isOwner()){
            try{
                $customOrders = CustomOrder::with('masterBahan', 'approvedByUser')->get();
                // $order = Order::with()
                if ($customOrders->isEmpty()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Tidak ada data custom order'
                    ], 404);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil mendapatkan data custom order',
                    'data' => [
                        'custom_orders' => $customOrders,
                        
                    ]
                ], 200);
            }catch(\Exception $e){
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendapatkan data custom order',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        if($user->isUser()){
            try{
                $customOrders = CustomOrder::where('user_id', $user->id)->get();
                if ($customOrders->isEmpty()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Tidak ada data custom order'
                    ], 404);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil mendapatkan data custom order',
                    'data' => $customOrders
                ], 200);
            }catch(\Exception $e){
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendapatkan data custom order',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }

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
    
            // Validate data first without the file
            $validateData = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'no_telp' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'jenis_baju' => 'required|string|max:255',
                'ukuran' => 'required|string|max:255',
                'jumlah' => 'required|integer|min:1',
                'detail_bahan' => 'nullable|string|max:255',
                'sumber_kain' => 'required|in:konveksi,sendiri',
                'catatan' => 'nullable|string|max:1000',
                'estimasi_waktu' => 'nullable|string|max:255'
            ]);
            
            // Handle file upload separately
            $gambarReferensi = null;
            if ($request->hasFile('gambar_referensi')) {
                // Additional file validation
                $request->validate([
                    'gambar_referensi' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);
                
                // Make sure the upload directory exists
                $uploadPath = public_path('uploads/custom_order');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Generate a unique filename
                $fileName = time() . '_' . uniqid() . '.' . $request->file('gambar_referensi')->getClientOriginalExtension();
                
                // Move the file with error handling
                if ($request->file('gambar_referensi')->move($uploadPath, $fileName)) {
                    $gambarReferensi = "uploads/custom_order/{$fileName}";
                } else {
                    throw new \Exception('Failed to upload image');
                }
            }
            
            // Create record
            $customOrder = CustomOrder::create([
                'user_id' => $user->id,
                'nama_lengkap' => $validateData['nama_lengkap'],
                'no_telp' => $validateData['no_telp'],
                'email' => $validateData['email'],
                'jenis_baju' => $validateData['jenis_baju'],
                'ukuran' => $validateData['ukuran'],
                'status' => 'pending',
                'jumlah' => $validateData['jumlah'],
                'sumber_kain' => $validateData['sumber_kain'],
                'catatan' => $validateData['catatan'] ?? null,
                'detail_bahan' => $validateData['detail_bahan'] ?? null,
                // 'master_bahan_id' => $validateData['master_bahan_id'] ?? null,
                'gambar_referensi' => $gambarReferensi,
                'estimasi_waktu' => $validateData['estimasi_waktu'] ?? null
            ]);
            
            return response()->json([
                'status' => true,
                'message' => 'Custom order berhasil dibuat',
                'data' => $customOrder
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log error for debugging
            // Log::error('Custom order creation failed: ' . $e->getMessage());
            
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
            // $customOrder->status_pembayaran = 'belum_bayar'; // Set default payment status

            // Update estimasi_waktu if provided
            // if (isset($validatedData['estimasi_waktu'])) {
            //     $customOrder->estimasi_waktu = $validatedData['estimasi_waktu'];
            // }
            
            // Add notes if provided
            // if (isset($validatedData['catatan'])) {
            //     $customOrder->catatan = $validatedData['catatan'];
            // }
            
            // Record who approved it and when
            $customOrder->approved_by = $user->id;
            $customOrder->approved_at = now();
            
            // Save changes
            $customOrder->save();
            
            // // Create a new order and associate it with the custom order
            // $order = Order::create([
            //     'user_id' => $customOrder->user_id ?? $user->id,
            //     'catalog_id' => null, // Assuming this is not applicable for custom orders
            //     'custom_order_id' => $customOrder->id,
            //     'transaction_id' => null, // This can be updated later when a transaction is created
            //     'jumlah' => 1, // Assuming 1 for custom orders
            //     'total_harga' => 0, // This can be updated later based on pricing
            //     'type' => 'Pemesanan',
            //     'status' => 'Menunggu_Pembayaran',
            //     'bukti_pembayaran' => null,
            // ]);

            // // Update the custom order to reference the newly created order
            // $customOrder->order_id = $order->id;
            // $customOrder->save();
            // Send email notification to the customer
            // try {
                
            //     if ($customOrder->email) {
            //         Mail::to($customOrder->email)->send(new CustomerorderMail($customOrder));
            //     }
                
            //     // Optionally, also notify the admin team
            //     $adminEmail = config('contact.recipient_email', 'jrkonveksiemail@gmail.com');
            //     Mail::to($adminEmail)->send(new AdminOrderEmail($customOrder));
                
            // } catch (\Exception $mailException) {
            //     // Log error but don't fail the process
            //     Log::error('Failed to send email notification: ' . $mailException->getMessage());
            // }
            
            

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
            // $customOrder->status_pembayaran = 'belum_bayar'; // Set default payment status

            // Update estimasi_waktu if provided
            // if (isset($validatedData['estimasi_waktu'])) {
            //     $customOrder->estimasi_waktu = $validatedData['estimasi_waktu'];
            // }
            
            // Add notes if provided
            // if (isset($validatedData['catatan'])) {
            //     $customOrder->catatan = $validatedData['catatan'];
            // }
            
            // Record who approved it and when
            $customOrder->approved_by = $user->id;
            $customOrder->approved_at = now();
            
            // Save changes
            $customOrder->save();
            
            // // Create a new order and associate it with the custom order
            // $order = Order::create([
            //     'user_id' => $customOrder->user_id ?? $user->id,
            //     'catalog_id' => null, // Assuming this is not applicable for custom orders
            //     'custom_order_id' => $customOrder->id,
            //     'transaction_id' => null, // This can be updated later when a transaction is created
            //     'jumlah' => 1, // Assuming 1 for custom orders
            //     'total_harga' => 0, // This can be updated later based on pricing
            //     'type' => 'Pemesanan',
            //     'status' => 'Menunggu_Pembayaran',
            //     'bukti_pembayaran' => null,
            // ]);

            // // Update the custom order to reference the newly created order
            // $customOrder->order_id = $order->id;
            // $customOrder->save();
            // Send email notification to the customer
            // try {
                
            //     if ($customOrder->email) {
            //         Mail::to($customOrder->email)->send(new CustomerorderMail($customOrder));
            //     }
                
            //     // Optionally, also notify the admin team
            //     $adminEmail = config('contact.recipient_email', 'jrkonveksiemail@gmail.com');
            //     Mail::to($adminEmail)->send(new AdminOrderEmail($customOrder));
                
            // } catch (\Exception $mailException) {
            //     // Log error but don't fail the process
            //     Log::error('Failed to send email notification: ' . $mailException->getMessage());
            // }
            
            

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
    public function updateStatus(Request $request, $id){
        $user = User::findOrFail(Auth::id());

        if($user->isAdmin() || $user->isOwner()){
            
            DB::beginTransaction();
            
            try {
                // Validate input
                $validatedData = $request->validate([
                    // 'custom_order_id' => 'required|exists:custom_orders,id',
                    'detail_bahan' => 'sometimes|string|max:255',
                    'total_harga' => 'required|integer|min:0', 
                    'payment_method' => 'required|in:BCA,E-Wallet_Dana,Cash'
                ]);

                // Find the custom order
                $customOrder = CustomOrder::findOrFail($id);
                
                // Check if the order is in the correct status (disetujui)
                if ($customOrder->status !== 'disetujui') {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Hanya custom order dengan status disetujui yang dapat difinalisasi'
                    ], 422);
                }

                // Update the custom order status to 'proses'
                $customOrder->update([
                    'detail_bahan' => $validatedData['detail_bahan'] ?? $customOrder->detail_bahan,
                    'status' => 'proses',
                    'status_pembayaran' => 'belum_bayar',
                    'total_harga' => $validatedData['total_harga'] // Update with final negotiated price
                ]);
                
                // Create a new order
                $order = Order::create([
                    'user_id' => $customOrder->user_id ?? $user->id,
                    'catalog_id' => null, // Using 0 for custom orders as it's required in schema
                    'custom_order_id' => $customOrder->id,
                    'jumlah' => $customOrder->jumlah,
                    'total_harga' => $validatedData['total_harga'],
                    'type' => 'Pemesanan',
                    'status' => 'Menunggu_Konfirmasi', // Directly set to waiting for confirmation
                    'bukti_pembayaran' => null,
                ]);

                // Create transaction
                $transaction = transaction::create([
                    'order_id' => $order->id,
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
                    'timestamp' => now()->toDateTimeString()
                ]);

                // Send email notification about deal finalization and payment
                // try {
                //     if ($customOrder->email) {
                //         // You might want to create a specific email for deal finalization
                //         Mail::to($customOrder->email)->send(new CustomOrderFinalizedMail($customOrder, $transaction));
                //     }
                // } catch (\Exception $mailException) {
                //     // Log error but don't fail the process
                //     Log::error('Failed to send finalization email: ' . $mailException->getMessage());
                // }
                try {
                
                    if ($customOrder->email) {
                        Mail::to($customOrder->email)->send(new CustomerorderMail($customOrder));
                    }
                    
                    // Optionally, also notify the admin team
                    $adminEmail = config('contact.recipient_email', 'jrkonveksiemail@gmail.com');
                    Mail::to($adminEmail)->send(new AdminOrderEmail($customOrder));
                    
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
