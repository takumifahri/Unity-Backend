<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Visitors extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'ip_address',
        'user_agent',
        'url_visited',
    ];
}
