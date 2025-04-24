<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CartControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
     public function Index()
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
