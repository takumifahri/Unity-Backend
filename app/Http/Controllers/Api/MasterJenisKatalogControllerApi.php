<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\master_jenis_katalogs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterJenisKatalogControllerApi extends Controller
{
    public function index()
    {
        //
        $jenis = master_jenis_katalogs::all();
        if($jenis !== null){
            try{
                return response()->json([
                    'message' => 'Data master jenis katalog berhasil diambil',
                    'data' => $jenis,
                    'status' => 'success'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status' => 'error'
                ]);
            }
        } else {
            return response()->json([
                'message' => 'Data Data master jenis katalog tidak ditemukan',
                'status' => 'error'
            ], 404);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        // Cek apakah user memiliki role 'admin'
        if ($user->isOwner() || $user->isAdmin()) {
            try {
                $validate = $request->validate([
                    'nama_jenis_katalog' => 'required|string|max:255',
                    'deskripsi' => 'required|string',
                    'tata_cara_pemakaian' => 'nullable|json',
                ],
                [
                    'nama_jenis_katalog.required' => 'Nama Data master jenis katalog harus diisi',
                    'deskripsi.required' => 'Deskripsi harus diisi',
                    'tata_cara_pemakaia' => 'Tata cara pemakaian harus berupa format JSON yang valid',
                ]);

                // Simpan data jenis
                $jenis = master_jenis_katalogs::create([
                    'nama_jenis_katalog' => $validate['nama_jenis_katalog'],
                    'deskripsi' => $validate['deskripsi'],
                    'tata_cara_pemakaian' => $validate['tata_cara_pemakaian'] ?? null,
                ]);

                return response()->json([
                    'message' => 'Data master jenis katalog created successfully',
                    'data' => $jenis,
                    'status' => 'success'
                ], 201);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status' => 'error'
                ], 500);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
        
       
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $jenisId = master_jenis_katalogs::find($id);
        if($jenisId !== null){
            try{
                return response()->json([
                    'message' => 'Success',
                    'data' => $jenisId,
                    'status' => 'success'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status' => 'error'
                ]);
            }
        } else {
            return response()->json([
                'message' => 'Data master jenis katalog not found',
                'status' => 'error'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Cek apakah data jenis ada
        $jenis = master_jenis_katalogs::find($id);
        $user = User::findOrFail(Auth::id());

        if($jenis !== null){
            if ($user->isAdmin() || $user->isOwner()) {
                try {
                    $validate = $request->validate([
                        'nama_jenis_katalog' => 'sometimes|required|string|max:255',
                        'deskripsi' => 'sometimes|required|string',
                        'tata_cara_pemakaian' => 'nullable|json',
                    ],
                    [
                        'nama_jenis_katalog.required' => 'Nama Data master jenis katalog harus diisi',
                        'deskripsi.required' => 'Deskripsi harus diisi',
                        'tata_cara_pemakaian.json' => 'Tata cara pemakaian harus berupa format JSON yang valid',
                    ]);
    
                    // Update data jenis
                    $jenis->update([
                        'nama_jenis_katalog' => $validate['nama_jenis_katalog'] ?? $jenis->nama_jenis_katalog,
                        'deskripsi' => $validate['deskripsi'] ?? $jenis->deskripsi,
                    ]);
    
                    return response()->json([
                        'message' => 'Data master jenis katalog updated successfully',
                        'data' => $jenis,
                        'status' => 'success'
                    ], 200);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'status' => 'error'
                    ], 500);
                }
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only admin or owner can access this feature',
                    'status' => 'error'
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'Data master jenis katalog not found',
                'status' => 'error'
            ], 404);
        }
        // Cek role user
       
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $jenis = master_jenis_katalogs::find($id);
        
        $user = User::findOrFail(Auth::id());

        if ($jenis !== null){
            if ($user->isAdmin() || $user->isOwner()) {
                try {
                    // Hapus gambar jika ada
                    $jenis->delete();
                    return response()->json([
                        'message' => 'jenis deleted successfully',
                        'status' => 'success'
                    ], 200);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'status' => 'error'
                    ], 500);
                }
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only admin or owner can access this feature',
                    'status' => 'error'
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'Data master jenis katalog not found',
                'status' => 'error'
            ], 404);
        }
    }
}
