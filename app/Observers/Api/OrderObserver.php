<?php

namespace App\Observers\Api;

use App\Models\Catalog;
use App\Models\History;
use App\Models\keuangan;
use App\Models\Order;
use App\Models\transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    //
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Decrement stock when order is created
        if ($order->status == 'Diproses') {
            $catalog = Catalog::find($order->catalog_id);
            if ($catalog) {
                $catalog->stok -= $order->jumlah;
                $catalog->save();
            }
        }

        // Create history record
        $this->recordHistory($order, 'created');
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Handle status changes
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status;
    
            // === KURANGI STOK SAAT DIPROSES ===
            if ($oldStatus === 'Menunggu Konfirmasi' && $newStatus === 'Diproses') {
                $catalog = Catalog::find($order->catalog_id);
                if ($catalog && $catalog->stok >= $order->jumlah) {
                    $catalog->stok -= $order->jumlah;
                    $catalog->save();
                }
    
                // Catat pemasukan keuangan jika transaksi sukses
                if ($order->transaction_id) {
                    $transaction = Transaction::find($order->transaction_id);
                    if ($transaction && $transaction->status == 'success') {
                        $this->createKeuanganRecord($order, 'pemasukan');
                    }
                }
            }
    
            // === KEMBALIKAN STOK JIKA ORDER DIBATALKAN SEBELUM DIPROSES ===
            if (in_array($oldStatus, ['Menunggu_Pembayaran', 'Menunggu Konfirmasi']) &&
                !in_array($newStatus, ['Menunggu_Pembayaran', 'Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai'])) {
                $catalog = Catalog::find($order->catalog_id);
                if ($catalog) {
                    $catalog->stok += $order->jumlah;
                    $catalog->save();
                }
            }
    
            // === OPSIONAL: Logika jika order sudah dikirim atau selesai ===
            if (in_array($newStatus, ['Dikirim', 'Selesai'])) {
                // Tambahkan logika lain kalau dibutuhkan
            }
        }
    
        // Catat riwayat perubahan
        $this->recordHistory($order, 'updated');
    }
    

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // If order is deleted and it was in 'Menunggu_Pembayaran' status, restore stock
        if (in_array($order->status, ['Menunggu_Pembayaran', 'Menunggu Konfirmasi'])) {
            $catalog = Catalog::find($order->catalog_id);
            if ($catalog) {
                $catalog->stok += $order->jumlah;
                $catalog->save();
            }
        }

        // Create history record
        $this->recordHistory($order, 'deleted');
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        // If order is restored to pending status, decrement stock again
        if (in_array($order->status, ['Diproses'])) {
            $catalog = Catalog::find($order->catalog_id);
            if ($catalog) {
                $catalog->stok -= $order->jumlah;
                $catalog->save();
            }
        }

        // Create history record
        $this->recordHistory($order, 'restored');
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        // Create history record
        $this->recordHistory($order, 'force deleted');
    }

    /**
     * Record order actions in history
     */
    private function recordHistory(Order $order, string $action): void
    {
        $userId = Auth::id() ?? $order->user_id;
        
        History::create([
            'items_id' => $order->id,
            'item_type' => 'Order',
            'user_id' => $userId,
            'action' => $action,
            'reason' => request('reason') ?? ucfirst($action) . ' by system',
            'new_value' => $action != 'deleted' ? $order->getAttributes() : [],
            'old_value' => in_array($action, ['updated', 'deleted']) ? $order->getOriginal() : []
        ]);
    }

    /**
     * Create a keuangan record for the order
     */
    private function createKeuanganRecord(Order $order, string $jenis_keuangan): void
    {
        try {
            $transaction = transaction::find($order->transaction_id);
            $catalog = Catalog::find($order->catalog_id);
            
            if ($transaction && $catalog) {
                $description = ($order->type == 'Pembelian' ? 'Pembelian ' : 'Pemesanan ') . 
                               $catalog->nama_katalog . ' (' . $order->jumlah . ' pcs)';
                
                keuangan::create([
                    'order_id' => $order->id,
                    'nama_keuangan' => $description,
                    'nominal' => $order->total_harga,
                    'tanggal' => Carbon::now()->toDateString(),
                    'jenis_keuangan' => $jenis_keuangan
                ]);
            }
        } catch (\Exception $e) {
            // Log the error but don't interrupt the process
            Log::error('Failed to create keuangan record: ' . $e->getMessage());
        }
    }
}
