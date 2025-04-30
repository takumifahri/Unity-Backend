<?php

namespace App\Models;

use App\HistoryTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\catalog_colors;
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
        'sold',
        'gambar',
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
    public function sizes()
    {
        return $this->hasMany(catalog_sizes::class);
    }

    public function colors(){
        return $this->hasMany(catalog_colors::class);
    }
}
