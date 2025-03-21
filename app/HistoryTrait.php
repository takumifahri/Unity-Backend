<?php

namespace App;

use App\Models\History;
use Illuminate\Support\Facades\Auth;

trait HistoryTrait
{
    //
    public static function bootHistoryTrait()
    {
        // Mencatat perubahan saat model diupdate
        static::updating(function ($model) {
            $original = $model->getOriginal();
            $changes = $model->getDirty();
            
            // Hanya catat jika ada perubahan
            if (!empty($changes)) {
                self::recordHistory($model, 'update', $changes, $original);
            }
        });

        // Mencatat saat model dibuat
        static::created(function ($model) {
            self::recordHistory($model, 'create', $model->getAttributes(), []);
        });

        // Mencatat saat model di-soft delete
        static::deleting(function ($model) {
            // Reason diambil dari request
            $reason = request('reason') ?? 'No reason provided';
            self::recordHistory($model, 'delete', [], $model->getAttributes(), $reason);
        });
    }

    private static function recordHistory($model, $action, $newValue, $oldValue, $reason = null)
    {
        // Dapatkan item_type berdasarkan nama model
        $itemType = class_basename($model);
        
        History::create([
            'items_id' => $model->id,
            'item_type' => $itemType,
            'user_id' => Auth::id() ?? 1, // Default ke admin ID 1 jika tidak ada auth
            'action' => $action,
            'reason' => $action === 'delete' ? $reason : null,
            'new_value' => $newValue,
            'old_value' => $oldValue,
        ]);
    }

    // Method untuk soft delete dengan alasan
    public static function deleteWithReason($id, $reason)
    {
        $model = self::findOrFail($id);
        request()->merge(['reason' => $reason]);
        $model->delete();
        return $model;
    }
}
