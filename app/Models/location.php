<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class location extends Model
{
    use HasApiTokens, Notifiable, HasFactory;
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
