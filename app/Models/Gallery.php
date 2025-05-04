<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    //
    protected $table = 'galleries';

    protected $fillable = [
        'added_by',
        'image_path',
        'title',
        'description',
        'bahan',
        'ukuran',
    ];

}
