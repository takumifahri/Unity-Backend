<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Custom_order_colors extends Model
{
    //
    use Notifiable, SoftDeletes, HasFactory, HasApiTokens;
    protected $table = 'custom_order_colors';
    protected $fillable = [
        'custom_order_id',
        'color_name',
    ];
    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class, 'custom_order_id');
    }
    public function sizes()
    {
        return $this->hasMany(Custom_order_sizes::class, 'custom_order_color_id', 'id');
    }
}
