<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $gallery = Gallery::with(['reponsible_person'])->get();
        try{
            if (request()->has('search') && request('search')) {
                $gallery->where('title', 'like', '%' . request('search') . '%');
            }
            if (request()->has('sort_by') && request()->has('sort_type')) {
                $gallery->orderBy(request('sort_by'), request('sort_type'));
            } else {
                $gallery->orderBy('created_at', 'asc');
            }

            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendapatkan data',
                'data' => $gallery
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Gagal mendapatkan data',
                'data' => null
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
