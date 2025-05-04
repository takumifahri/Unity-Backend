<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewsProduct extends Model
{
    //
    protected $table = 'reviews_products';
    protected $fillable = [
        'user_id',
        'order_id',
        'gambar_produk',
        'ulasan',
        'ratings',
        'balasan_admin',
        'reply_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'reply_by');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

}
