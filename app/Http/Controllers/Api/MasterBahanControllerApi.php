<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\master_bahan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class MasterBahanControllerApi extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $bahan = master_bahan::all();
        $user = User::findOrFail(Auth::id());
        if($user->role == 'admin' || $user->role == 'owner'){
            try{
                return response()->json([
                    'message' => 'Success',
                    'data' => $bahan,
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
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
        

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Cek apakah user memiliki role 'admin'
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() | $user->isOwner() ){
            try{
                // Kode validasi dan penyimpanan tetap sama
                $validate = $request->validate([
                    'nama_bahan' => 'required|string|max:255',
                    'deskripsi' => 'required|string',
                    'stok' => 'required|numeric|min:0',
                    'harga' => 'required|numeric|min:0',
                    'satuan' => 'required|string|max:255',
                    'gambar_bahan' => 'nullable|mimes: jpeg,jpg,png,webp|max:2048',
                ],
                [
                    'nama_bahan.required' => 'Nama bahan harus diisi',
                    'deskripsi.required' => 'Deskripsi harus diisi',
                    'stok.required' => 'Stok harus diisi',
                    'harga.required' => 'Harga harus diisi',
                    'satuan.required' => 'Satuan harus diisi',
                    'gambar_bahan.image' => 'File harus berupa gambar',
                    'gambar_bahan.max' => 'Ukuran gambar maksimal 2MB',
                ]);


                // Handle upload gambar jika ada
                if ($request->hasFile('gambar_bahan')) {
                    $fileName = time() . '.' . $request->gambar_bahan->extension();
                    $request->gambar_bahan->move(public_path('uploads/master_bahan/'), $fileName);
                    $data['gambar_bahan'] = 'uploads/master_bahan/'.$fileName;
                } else {
                    $data['gambar_bahan'] = null;
                }

                $bahan = master_bahan::create([
                    'nama_bahan' => $validate['nama_bahan'],
                    'deskripsi' => $validate['deskripsi'],
                    'stok' => $validate['stok'],
                    'harga' => $validate['harga'],
                    'satuan' => $validate['satuan'],
                    'gambar_bahan' => $data['gambar_bahan'],
                ]);

                return response()->json([
                    'message' => 'Bahan created successfully',
                    'data' => $bahan,
                    'status' => 'success'
                ], 201);
            }catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status' => 'error'
                ]);
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
        $bahanId = master_bahan::find($id);
        $user = User::findOrFail(Auth::id());
        if($user->role == 'admin' || $user->role == 'owner'){
            try{
                return response()->json([
                    'message' => 'Data master bahan is retrieved successfully',
                    'data' => $bahanId,
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
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Cek apakah data bahan ada
        $bahan = master_bahan::find($id);
        
        if (!$bahan) {
            return response()->json([
                'message' => 'Data master bahan tidak ditemukan',
                'status' => 'error'
            ], 404);
        }
        
    $user = User::findOrFail(Auth::id());

        // Cek role user
        if ($user->isAdmin() | $user->isOwner()) {
            try {
                $validate = $request->validate([
                    'nama_bahan' => 'sometimes|required|string|max:255',
                    'deskripsi' => 'sometimes|required|string',
                    'stok' => 'sometimes|required|numeric|min:0',
                    'harga' => 'sometimes|required|numeric|min:0',
                    'satuan' => 'sometimes|required|string|max:255',
                    'gambar_bahan' => 'nullable|file|image|max:2048',
                ],
                [
                    'nama_bahan.required' => 'Nama bahan harus diisi',
                    'deskripsi.required' => 'Deskripsi harus diisi',
                    'stok.required' => 'Stok harus diisi',
                    'harga.required' => 'Harga harus diisi',
                    'satuan.required' => 'Satuan harus diisi',
                    'gambar_bahan.image' => 'File harus berupa gambar',
                    'gambar_bahan.max' => 'Ukuran gambar maksimal 2MB',
                ]);

                // Siapkan data update, menggunakan data yang sudah ada jika tidak ada input baru
                $dataToUpdate = [
                    'nama_bahan' => $request->has('nama_bahan') ? $validate['nama_bahan'] : $bahan->nama_bahan,
                    'deskripsi' => $request->has('deskripsi') ? $validate['deskripsi'] : $bahan->deskripsi,
                    'stok' => $request->has('stok') ? $validate['stok'] : $bahan->stok,
                    'harga' => $request->has('harga') ? $validate['harga'] : $bahan->harga,
                    'satuan' => $request->has('satuan') ? $validate['satuan'] : $bahan->satuan,
                    'gambar_bahan' => $bahan->gambar_bahan, // Default ke gambar lama
                ];

                // Proses upload gambar baru jika ada
                if ($request->hasFile('gambar_bahan')) {
                    // Hapus gambar lama jika perlu
                    if ($bahan->gambar_bahan && file_exists(public_path('uploads/' . $bahan->gambar_bahan))) {
                        unlink(public_path('uploads/' . $bahan->gambar_bahan));
                    }
                    
                    $fileName = time() . '.' . $request->gambar_bahan->extension();
                    $request->gambar_bahan->move(public_path('uploads'), $fileName);
                    $dataToUpdate['gambar_bahan'] = $fileName;
                }

                // Update data bahan
                $bahan->update($dataToUpdate);

                return response()->json([
                    'message' => 'Data master bahan updated successfully',
                    'data' => $bahan,
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $bahan = master_bahan::find($id);
        
    $user = User::findOrFail(Auth::id());

        if ($bahan !== null){
            if ($user->isAdmin() | $user->isOwner()) {
                try {
                    // Hapus gambar jika ada
                    if ($bahan->gambar_bahan && file_exists(public_path('uploads/' . $bahan->gambar_bahan))) {
                        unlink(public_path('uploads/' . $bahan->gambar_bahan));
                    }
                    $bahan->delete();
                    return response()->json([
                        'message' => 'Data master bahan deleted successfully',
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
                'message' => 'Bahan not found',
                'status' => 'error'
            ], 404);
        }
    }
}
