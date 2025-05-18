<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class DeliveryProof extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'delivery_proof_unique_id', // Unique ID untuk bukti pengiriman
        'order_id',       // ID pesanan yang dikirim
        'admin_id',       // ID admin yang melakukan pengiriman
        'image_path',     // Path gambar bukti pengiriman
        'description',    // Deskripsi tambahan tentang pengiriman
        'delivery_date',  // Tanggal pengiriman
        'receiver_name',  // Nama penerima barang
        'notes',          // Catatan tambahan (opsional)
        'status',         // Status pengiriman (misalnya: delivered, failed, etc.)
    ];

    // Relasi ke Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi ke Admin/User
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
