<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Catalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\catalog_colors;
class catalog_sizes extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    protected $table = 'catalog_sizes';
    protected $fillable = [
        'catalog_id',
        'catalog_colors_id',
        'size',
        'stok',
    ];
    public function catalog()
    {
        return $this->belongsTo(Catalog::class, 'catalog_id');
    }
    public function colors()
    {
        return $this->belongsTo(catalog_colors::class, 'catalog_colors_id');
    }
}
