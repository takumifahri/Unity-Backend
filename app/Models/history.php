<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
class history extends Model
{
    //

    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'histories';
    protected $fillable = [
        'items_id',
        'user_id',
        'reason',
        'new_value',
        'old_value',
        'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
