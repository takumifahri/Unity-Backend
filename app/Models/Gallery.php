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

    public function reponsible_person()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
