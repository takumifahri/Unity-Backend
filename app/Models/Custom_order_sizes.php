<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Custom_order_sizes extends Model
{
    use Notifiable, SoftDeletes, HasApiTokens, HasFactory;
    //
    protected $table = 'custom_order_sizes';
    protected $fillable = [
        'custom_order_id',
        'custom_order_color_id',
        'size',
        'jumlah',
    ];
    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class, 'custom_order_id');
    }
    public function customOrderColor()
    {
        return $this->belongsTo(Custom_order_colors::class, 'custom_order_color_id');
    }
}
