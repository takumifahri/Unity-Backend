<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class location extends Model
{
    use HasApiTokens, Notifiable, HasFactory, SoftDeletes;
    //
    protected $fillable = [
        'user_id',
        'label',
        'latitude',
        'longitude',
    ];

    protected $table = 'locations';
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
