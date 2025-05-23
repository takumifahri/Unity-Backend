<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexExceptPending()
    {
        //
        $user = User::findOrFail(Auth::id());
        if($user && $user->isAdmin() || $user->isOwner()){
            $transaction = transaction::with(['orders' => function($query) {
                    $query->where('status', '!=', 'Menunggu_Pembayaran')
                          ->where('status', '=', 'Menunggu_konfirmasi');
                }, 'orders.catalog', 'orders.customOrder', 'orders.user', 'orders.color', 'orders.size'])
                ->orderBy('created_at', 'desc')
                ->get();
            if ($transaction->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No transactions found'
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Transactions retrieved successfully',
                'data' => $transaction
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ]);
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
        $user = User::findOrFail(Auth::id());
        if($user && $user->isAdmin() || $user->isOwner()){
            try{
                $transaction = transaction::with(['orders.catalog', 'orders.customOrder', 'orders.user', 'orders.color', 'orders.size'])
                    ->where('id', $id)
                    ->first();
                if ($transaction) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Transaction found',
                        'data' => $transaction
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Transaction not found'
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error: '.$e->getMessage()
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ]);
        }
      
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
