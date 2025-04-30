<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AdminOrderEmail;
use App\Mail\CustomerorderMail;
use App\Models\CustomOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomOrderControllerApi extends Controller
{
    //
    public function index(){
        $user = User::findOrFail(Auth::id());
        if($user->isAdmin() || $user->isOwner()){
            try{
                $customOrders = CustomOrder::with('masterBahan')->get();
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
                'sumber_kain' => 'required|in:konveksi,sendiri',
                'master_bahan_id' => $request->input('sumber_kain') === 'konveksi' ? 'required|exists:master_bahans,id' : 'nullable|exists:master_bahans,id',
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
                'nama_lengkap' => $validateData['nama_lengkap'],
                'no_telp' => $validateData['no_telp'],
                'email' => $validateData['email'],
                'jenis_baju' => $validateData['jenis_baju'],
                'ukuran' => $validateData['ukuran'],
                'status' => 'pending',
                'sumber_kain' => $validateData['sumber_kain'],
                'master_bahan_id' => $validateData['master_bahan_id'] ?? null,
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
            // \Log::error('Custom order creation failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat custom order',
                'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server'
            ], 500);
        }
    }
    public function acceptPropose(Request $request)
    {
        try {
            // Authorize the action
            $user = User::findOrFail(Auth::id());
            if (!$user || !($user->isOwner() || $user->isAdmin())) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Validate input
            $validatedData = $request->validate([
                'custom_order_id' => 'required|exists:custom_orders,id',
                'estimasi_waktu' => 'nullable|string|max:255',
                'catatan' => 'nullable|string|max:1000'
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
            $customOrder->status = 'proses';
            
            // Update estimasi_waktu if provided
            if (isset($validatedData['estimasi_waktu'])) {
                $customOrder->estimasi_waktu = $validatedData['estimasi_waktu'];
            }
            
            // Add notes if provided
            if (isset($validatedData['catatan'])) {
                $customOrder->catatan = $validatedData['catatan'];
            }
            
            // Record who approved it and when
            $customOrder->approved_by = $user->id;
            $customOrder->approved_at = now();
            
            // Save changes
            $customOrder->save();
            
            // Send email notification to the customer
            try {
                
                if ($customOrder->email) {
                    Mail::to($customOrder->email)->send(new CustomerorderMail($customOrder));
                }
                
                // Optionally, also notify the admin team
                $adminEmail = config('contact.recipient_email', 'jrkonveksiemail@gmail.com');
                Mail::to($adminEmail)->send(new AdminOrderEmail($customOrder));
                
            } catch (\Exception $mailException) {
                // Log error but don't fail the process
                \Illuminate\Support\Facades\Log::error('Failed to send email notification: ' . $mailException->getMessage());
            }
            
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
            \Illuminate\Support\Facades\Log::error('Accept custom order failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengubah status custom order',
                'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server'
            ], 500);
        }
    }
}
