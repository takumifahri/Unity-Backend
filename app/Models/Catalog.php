<?php

namespace App\Models;

use App\HistoryTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Catalog extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HistoryTrait;
    //
    protected $table = 'Catalogs';
    protected $fillable = [
        'nama_katalog',
        'deskripsi',
        'details',
        'stok',
        'tipe_bahan_id',
        'jenis_katalog_id',
        'price',
        'feature',
        'size',
        'size_guide',
        'gambar',
        'colors',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function tipe_bahan()
    {
        return $this->belongsTo(master_bahan::class, 'tipe_bahan_id');
    }

    public function jenis_katalog()
    {
        return $this->belongsTo(master_jenis_katalogs::class, 'jenis_katalog_id');
    }
}
