<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ContactUs extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    //
    protected $table = 'contact_us';    
    protected $fillable = [
        'name',
        'email',
        'subject',
        'no_hp',
        'message',
        'attachment',
    ];
}
