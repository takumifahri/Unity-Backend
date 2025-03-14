<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function CartIndex()
    {
        $user = Auth::user();
        
        try {
            $order_items = Order::where('user_id', $user->id)
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
        $user = Auth::user();
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
                $addCart->save();
            } else {
                // Create new cart item
                $addCart = Order::create([
                    'user_id' => $user->id,
                    'catalog_id' => $validated['catalog_id'],
                    'jumlah' => $validated['jumlah'],
                    'total_harga' => $totalPrice,
                    'alamat' => 'Depok', // Default alamat value
                    'type' => 'Pembelian', // Default value, can be changed later
                    'status' => 'Menunggu Pembayaran'
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
