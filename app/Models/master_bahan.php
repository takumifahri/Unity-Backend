<?php

namespace App\Models;

use App\HistoryTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class master_bahan extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HistoryTrait;
    protected $table = 'master_bahans';
    protected $fillable = [
        'nama_bahan',
        'harga',
        'stok',
        'satuan',
        'gambar_bahan',
        'deskripsi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
