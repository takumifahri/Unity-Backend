<?php

namespace App\Models;

use App\HistoryTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class transaction extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HistoryTrait, SoftDeletes;
    protected $fillable = [
        'order_id',  // ID dari order service
        'status',    // status pembayaran
        'tujuan_transfer', // tujuan transfer
        'amount',    // jumlah pembayaran
        'payment_method', // jenis pembayaran (credit_card, bank_transfer, gopay, ovo, dana)
        'bukti_transfer', // bukti transfer
    ];

    protected $casts = [
        'payment_details' => 'json',
        'expired_at' => 'datetime',
    ];

}
