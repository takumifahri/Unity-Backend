<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class keuangan extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    protected $table = 'keuangans';
    protected $fillable = [
        'user_id',
        'catalog_id',
        'order_id',
        'keterangan',
        'jenis_pembayaran',
        'nominal',
        'tanggal',
        'jenis_keuangan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function catalog()
    {
        return $this->belongsTo(Catalog::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    
}
