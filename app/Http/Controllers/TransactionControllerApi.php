<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransactionControllerApi extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }
    /**
     * Display a listing of the resource.
     */
    public function CreateTransaction(Request $request)
    {
        //
        try{
            $validate = $request->validate([
                'order_id' => 'required|string',
                'total_harga' => 'required|integer|min:1',
                'alamat' => 'required|string',
                'user_id' => 'required',
                'items' => 'required|array',
            ],
            [
                'order_id.required' => 'Order ID tidak boleh kosong',
                'total_harga.required' => 'Total harga tidak boleh kosong',
                'total_harga.integer' => 'Total harga harus berupa angka',
                'total_harga.min' => 'Total harga minimal 1',
                'alamat.required' => 'Alamat tidak boleh kosong',
                'user_id.required' => 'User ID tidak boleh kosong',
                'items.required' => 'Item tidak boleh kosong',
                'items.array' => 'Item harus berupa array',
            ]);
            // Ambil data user dari User Service
            $userResponse = Http::get(env('USER_SERVICE_URL') . '/api/users/' . $request->user_id);
        
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
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
