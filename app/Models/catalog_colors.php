<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\catalog_sizes as size;
use App\Models\Catalog;
class catalog_colors extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    
    protected $table = 'catalog_colors';
    protected $fillable = [
        'catalog_id',
        'color_name',
    ];

    public function sizes()
    {
        return $this->belongsTo(size::class, 'id');
    }
    public function catalog()
    {
        return $this->belongsTo(Catalog::class, 'catalog_id');
    }
}
