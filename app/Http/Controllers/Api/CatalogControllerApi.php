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
    public function index(Request $request)
    {
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
    }

     /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) 
    {
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            try {
                $validate = $request->validate([
                    'nama_katalog' => 'required|string|max:255',
                    'deskripsi' => 'required|string',
                    'details' => 'required|string',
                    'stok' => 'required|numeric|min:0',
                    'tipe_bahan' => 'required|exists:master_bahans,id',
                    'jenis_katalog' => 'required|exists:master_jenis_katalogs,id',
                    'price' => 'required|numeric|min:0',
                    'feature' => 'required|json',
                    'colors' => 'required|array', // Validasi untuk warna sebagai array
                    'colors.*.color_name' => 'required|string', // Validasi nama warna
                    'colors.*.sizes' => 'required|array', // Validasi untuk ukuran dalam warna
                    'colors.*.sizes.*.size' => 'required|in:S,M,L,XL', // Validasi setiap ukuran
                    'colors.*.sizes.*.stok' => 'required|numeric|min:0', // Validasi stok untuk setiap ukuran
                    'gambar' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
                ]);
    
                // Handle file upload
                if ($request->hasFile('gambar')) {
                    $fileName = time() . '.' . $request->gambar->extension();
                    $request->gambar->move(public_path('uploads/catalog'), $fileName);
                }
    
                // Buat catalog
                $catalog = Catalog::create([
                    'nama_katalog' => $validate['nama_katalog'],
                    'deskripsi' => $validate['deskripsi'],
                    'details' => $validate['details'],
                    'stok' => $validate['stok'],
                    'tipe_bahan_id' => $validate['tipe_bahan'],
                    'jenis_katalog_id' => $validate['jenis_katalog'],
                    'price' => $validate['price'],
                    'feature' => $validate['feature'],
                    'gambar' => 'uploads/catalog/' . $fileName,
                ]);
    
                // Simpan warna dan ukuran ke tabel catalog_colors dan catalog_sizes
                foreach ($validate['colors'] as $colorData) {
                    $color = $catalog->colors()->create([
                        'color_name' => $colorData['color_name'],
                    ]);
                
                    foreach ($colorData['sizes'] as $sizeData) {
                        $color->sizes()->create([
                            'catalog_id' => $catalog->id,
                            'catalog_colors_id' => $color->id, // Pastikan nilai catalog_colors_id diberikan
                            'size' => $sizeData['size'],
                            'stok' => $sizeData['stok'],
                        ]);
                    }
                }
    
                return response()->json([
                    'message' => 'Catalog created successfully',
                    'data' => $catalog->load('colors.sizes'), // Muat relasi colors dan sizes
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
        $catalog = Catalog::with('colors.sizes')->find($id);
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
                    'details' => 'sometimes|string',
                    'stok' => 'sometimes|numeric|min:0',
                    'tipe_bahan' => 'sometimes|exists:master_bahans,id',
                    'jenis_katalog' => 'sometimes|exists:master_jenis_katalogs,id',
                    'price' => 'sometimes|numeric|min:0',
                    'feature' => 'sometimes|json',
                    'size' => 'sometimes|in:S,M,L,XL',
                    'size_guide' => 'sometimes|array',
                    'colors' => 'sometimes|in:Brown,Black,Navy,Red,Green',
                    'gambar' => 'sometimes|file|mimes:jpeg,png,jpg,gif|max:2048',
                ]);

                // Handle file upload
                if ($request->hasFile('gambar')) {
                    $fileName = time() . '.' . $request->gambar->extension();
                    $request->gambar->move(public_path('uploads/catalog'), $fileName);
                }

                $catalog->update([
                    'nama_katalog' => $request->has('nama_katalog') ? $validate['nama_katalog'] : $catalog->nama_katalog,
                    'deskripsi' => $request->has('deskripsi') ? $validate['deskripsi'] : $catalog->deskripsi,
                    'details' => $request->has('details') ? $validate['details'] : $catalog->details,
                    'stok' => $request->has('stok') ? $validate['stok'] : $catalog->stok,
                    'tipe_bahan_id' => $request->has('tipe_bahan') ? $validate['tipe_bahan'] : $catalog->tipe_bahan_id,
                    'jenis_katalog_id' => $request->has('jenis_katalog') ? $validate['jenis_katalog'] : $catalog->jenis_katalog_id,
                    'price' => $request->has('price') ? $validate['price'] : $catalog->price,
                    'feature' => $request->has('feature') ? $validate['feature'] : $catalog->feature,
                    'size' => $request->has('size') ? $validate['size'] : $catalog->size,
                    'colors' => $request->has('colors') ? $validate['colors'] : $catalog->colors,
                    'gambar' => $request->hasFile('gambar') ? 'uploads/catalog/' . $fileName : $catalog->gambar,
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
