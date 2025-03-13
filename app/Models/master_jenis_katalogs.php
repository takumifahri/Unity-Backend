<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class master_jenis_katalogs extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'master_jenis_katalogs';
    protected $fillable = [
        'nama_jenis_katalog',
        'deskripsi',
    ];
}
