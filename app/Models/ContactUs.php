<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ContactUs extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    //
    protected $table = 'contact_us';    
    protected $fillable = [
        'name',
        'email',
        'no_hp',
        'message',
    ];
}
