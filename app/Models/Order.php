<?php

namespace App\Models;

use App\HistoryTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Order extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HistoryTrait, SoftDeletes;
    protected $table = 'orders';
    protected $fillable = [
        'user_id',
        'catalog_id',
        'custom_order_id',
        'transaction_id',
        'jumlah',
        'total_harga',
        // 'alamat',
        'type',
        'status',
        'bukti_pembayaran',
        'isReviewed',
        'ulasan_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function catalog()
    {
        return $this->belongsTo(Catalog::class, 'catalog_id');
    }

    public function deliveryProof()
    {
        return $this->hasOne(DeliveryProof::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class, 'custom_order_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function Transactions()
    {
        return $this->hasOne(Transaction::class, 'order_id');
    }
    public function reviews()
    {
        return $this->hasOne(ReviewsProduct::class, 'order_id');
    }
}
