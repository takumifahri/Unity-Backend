<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'items_id',
        'item_type',  // Menambahkan kolom untuk tipe item (catalog, transaction, master_bahan, dll)
        'user_id',
        'action',     // Menambahkan kolom untuk jenis aksi (create, update, delete, dll)
        'reason',
        'new_value',
        'old_value',
    ];

    protected $casts = [
        'new_value' => 'array',
        'old_value' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}