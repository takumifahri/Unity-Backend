<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class CustomOrder extends Model
{
    //
    use HasApiTokens, SoftDeletes, HasFactory, Notifiable;
    protected $fillable = [
        'user_id',
        'order_id',
        'custom_order_unique_id',
        'nama_lengkap',
        'no_telp',
        'email',
        'jenis_baju',
        'colors',
        'total_harga',
        'sumber_kain',
        'detail_bahan',
        'status',
        'status_pembayaran',
        'gambar_referensi',
        'responded_by',
        'alasan_diTolak',
        'catatan',
        'responded_at',
        'estimasi_waktu'
    ];

    protected $casts = [
        'colors' => 'array', // Cast sebagai array
        'gambar_referensi' => 'array', // Cast sebagai array

    ];
    public function masterBahan()
    {
        return $this->belongsTo(master_bahan::class, 'master_bahan_id');
    }
    
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'responded_by', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'custom_order_id', 'id');
    }

    public function sizes()
    {
        return $this->hasMany(Custom_order_sizes::class, 'custom_order_color_id');
    }

    public function colors()
    {
        return $this->hasMany(\App\Models\Custom_order_colors::class, 'custom_order_id');
    }
}
