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
        'nama_lengkap',
        'no_telp',
        'email',
        'jenis_baju',
        'ukuran',
        'status',
        'sumber_kain',
        'master_bahan_id',
        'gambar_referensi',
        'estimasi_waktu'
    ];

    public function masterBahan()
    {
        return $this->belongsTo(master_bahan::class, 'master_bahan_id');
    }
    
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

}
