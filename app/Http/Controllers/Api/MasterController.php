<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\master_bahan;
use App\Models\master_jenis_katalogs;
use App\Services\HistoryService;
use App\Services\HistoryServices;
use Illuminate\Http\Request;

// controller buat reson ketika delete
class MasterController extends Controller
{
    protected $historyService;
    
    public function __construct(HistoryServices $historyService)
    {
        $this->historyService = $historyService;
    }
    
    public function destroyBahan(Request $request, $id)
    {
        // Validasi bahwa user adalah admin
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'reason' => 'required|string|min:5'
        ]);
        
        $bahan = master_bahan::findOrFail($id);
        
        // Catat ke history dengan reason dari request
        $this->historyService->logActivity(
            $bahan, 
            'Data master bahan dihapus: ' . $request->reason, 
            $bahan->id
        );
        
        // Soft delete
        $bahan->delete();
        
        return response()->json([
            'message' => 'Data master bahan berhasil dihapus',
            'data' => $bahan
        ]);
    }
    
    public function destroyKategori(Request $request, $id)
    {
        // Validasi bahwa user adalah admin
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'reason' => 'required|string|min:5'
        ]);
        
        $kategori = master_jenis_katalogs::findOrFail($id);
        
        // Cek apakah kategori digunakan di produk
        if ($kategori->products()->count() > 0) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh produk'
            ], 422);
        }
        
        // Catat ke history dengan reason dari request
        $this->historyService->logActivity(
            $kategori, 
            'Data master kategori dihapus: ' . $request->reason, 
            $kategori->id
        );
        
        // Soft delete
        $kategori->delete();
        
        return response()->json([
            'message' => 'Data master kategori berhasil dihapus',
            'data' => $kategori
        ]);
    }
}