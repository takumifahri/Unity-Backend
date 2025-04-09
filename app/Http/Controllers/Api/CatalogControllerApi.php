<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\History;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
class CatalogControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $productCatalogs = Catalog::all();
        return response()->json([
            'message' => 'Success',
            'data' => $productCatalogs
        ]);
    }

     /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) 
    {
        $user = User::findOrFail(Auth::id());
        // Cek apakah user memiliki role 'admin'
        if ($user->isAdmin() || $user->isOwner()) {
            try{
                $validate = $request->validate([
                    'nama_katalog' => 'required|string|max:255',
                    'deskripsi' => 'required|string',
                    'tipe_bahan' => 'required|exists:master_bahans,id',
                    'stok' => 'required|numeric|min:0',
                    'jenis_katalog' => 'required|exists:master_jenis_katalogs,id',
                    'harga' => 'required|numeric|min:0',
                    'gambar' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
                ]);
                // Handle file upload
                if ($request->hasFile('gambar')) {
                    $fileName = time() . '.' . $request->gambar->extension();
                    $request->gambar->move(public_path('uploads'), $fileName);
                    
                }
                $catalog = Catalog::create([
                    'nama_katalog' => $validate['nama_katalog'],
                    'deskripsi' => $validate['deskripsi'],
                    'stok' => $validate['stok'],
                    'tipe_bahan_id' => $validate['tipe_bahan'],
                    'jenis_katalog_id' => $validate['jenis_katalog'],
                    'harga' => $validate['harga'],
                    'gambar' => 'uploads/catalog/' . $fileName,
                ]);
                return response()->json([
                    'message' => 'Catalog created successfully',
                    'data' => $catalog,
                    'status' => 'success'
                ], 201);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to create catalog',
                    'detail message' => $e->getMessage(),
                    'status' => 'failed'
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
        $catalog = Catalog::find($id);
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
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function addStock(Request $request, $id)
    {
        //
        $catalog = Catalog::find($id);
        $user = User::findOrFail(Auth::id());
        
        if ($catalog !== null){
            if ($user->isAdmin() || $user->isOwner()) {
                try{
                    $validateData = $request->validate([
                        'stok' => 'required|numeric|min:0'
                    ]);
                    $catalog->stok += $validateData['stok'];
                    $catalog->save();
                    return response()->json([
                        'message' => 'Stock added successfully',
                        'data' => $catalog,
                        'status' => 'success'
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to add stock',
                        'detail message' => $e->getMessage(),
                        'status' => 'failed'
                    ], 500);
                }
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only admin can access this feature',
                    'status' => 'error'
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'Catalog not found',
                'status' => 'error'
            ], 404);
        }
       
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $catalog = Catalog::find($id);
        $user = User::findOrFail(Auth::id());

        if ($user->isAdmin() || $user->isOwner()) {
            try{
                $validate = $request->validate([
                    'nama_katalog' => 'sometimes|string|max:255',
                    'deskripsi' => 'sometimes|string',
                    'tipe_bahan' => 'sometimes|exists:master_bahans,id',
                    'jenis_katalog' => 'sometimes|exists:master_jenis_katalogs,id',
                    'harga' => 'sometimes|numeric|min:0',
                    'gambar' => 'sometimes|file|mimes:jpeg,png,jpg,gif|max:2048',
                ],
                [
                    'nama_katalog.sometimes' => 'Nama katalog harus diisi',
                    'deskripsi.sometimes' => 'Deskripsi harus diisi',
                    'tipe_bahan.sometimes' => 'Tipe bahan harus diisi',
                    'jenis_katalog.sometimes' => 'Jenis katalog harus diisi',
                    'harga.sometimes' => 'Harga harus diisi',
                    'gambar.sometimes' => 'Gambar harus diisi',
                ]);
                // Handle file upload
                if ($request->hasFile('gambar')) {
                    $fileName = time() . '.' . $request->gambar->extension();
                    $request->gambar->move(public_path('uploads'), $fileName);
                }
                
                $catalog->update([
                    'nama_katalog' => $request->has('nama_katalog') ? $validate['nama_katalog'] : $catalog->nama_katalog,
                    'deskripsi' => $request->has('deskripsi') ? $validate['deskripsi'] : $catalog->deskripsi,
                    'tipe_bahan_id' => $request->has('tipe_bahan') ? $validate['tipe_bahan'] : $catalog->tipe_bahan_id,
                    'jenis_katalog_id' => $request->has('jenis_katalog') ? $validate['jenis_katalog'] : $catalog->jenis_katalog_id,
                    'harga' => $request->has('harga') ? $validate['harga'] : $catalog->harga,
                    'gambar' => $request->hasFile('gambar') ? 'uploads/' . $fileName : $catalog->gambar,
                ]);

                return response()->json([
                    'message' => 'Catalog Updated successfully',
                    'data' => $catalog,
                    'status' => 'success'
                ], 201);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to create catalog',
                    'detail message' => $e->getMessage(),
                    'status' => 'failed'
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $catalog = Catalog::find($id);
    $user = User::findOrFail(Auth::id());
        if ($catalog !== null) {
            if ($user->isAdmin() || $user->isOwner()) {
                $catalog->delete();
                return response()->json([
                    'message' => 'Catalog deleted successfully',
                    'status' => 'success'
                ]);
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only admin can access this feature',
                    'status' => 'error'
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'Catalog not found',
                'status' => 'error'
            ], 404);
        }
    }

    public function destroyReason(Request $request, $id)
    {
    $user = User::findOrFail(Auth::id());
        // Validasi bahwa user adalah admin
        if($user->isAdmin() || $user->isOwner()){
            try{
                $request->validate([
                    'reason' => 'required|string'
                ]);
        
                $catalog = Catalog::deleteWithReason($id, $request->reason);
                
                return response()->json([
                    'message' => 'Catalog deleted successfully'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to delete catalog',
                    'detail message' => $e->getMessage()
                ], 500);
            }
        } else{
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
        
    }

    // Restore katalog yang telah di-soft delete
    public function restore($id)
    {
    $user = User::findOrFail(Auth::id());
        $catalog = Catalog::withTrashed()->findOrFail($id);
        if($user->isAdmin() || $user->isOwner()) {
            $catalog->restore();

            // Catat history restore
            History::create([
                'items_id' => $catalog->id,
                'item_type' => 'Catalog',
                'user_id' => Auth::id(),
                'action' => 'restore',
                'reason' => request('reason') ?? 'Restored by admin',
                'new_value' => $catalog->getAttributes(),
                'old_value' => []
            ]);
            
            return response()->json([
                'message' => 'Catalog restored successfully',
                'data' => $catalog
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
       
        
        
    }
}
