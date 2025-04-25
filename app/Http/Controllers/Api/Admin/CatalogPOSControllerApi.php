<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogPOSControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            $query = Catalog::with(['colors.sizes']);

            // Filter by master_jenis_id if provided
            if ($request->has('master_jenis_id')) {
                $query->where('jenis_katalog_id', $request->master_jenis_id);
            }
            
            // Filter by name or description using 'like' if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama_katalog', 'like', "%$search%")
                    ->orWhere('deskripsi', 'like', "%$search%");
                });
            }

            $productCatalogs = $query->get();

            return response()->json([
                'message' => 'Success',
                'data' => $productCatalogs
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses untuk melihat data ini'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            $catalog = Catalog::with('colors.sizes')->findOrFail($id);
            if ($catalog !== null) {
                return response()->json([
                    'message' => 'Success',
                    'data' => $catalog
                ]);
            } else {
                return response()->json([
                    'message' => 'Catalog not found'
                ], 404);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses untuk melihat data ini'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
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
                        ->where('status', 'Menunggu_Pembayaran')
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
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
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
