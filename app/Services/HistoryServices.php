<?php

namespace App\Services;

use App\Jobs\LogHistoryJob;
use Illuminate\Support\Facades\Auth;

class HistoryServices
{
    public function logActivity($model, $reason, $itemId = null, $userId = null)
    {
        $userId = $userId ?? Auth::id() ?? 0;
        $itemId = $itemId ?? $model->id ?? 0;
        $modelType = get_class($model);
        
        // Data untuk history log
        $historyData = [
            'items_id' => $itemId,
            'model_type' => $modelType,
            'user_id' => $userId,
            'reason' => $reason,
        ];
        
        // Untuk created event
        if (!isset($model->getChanges) || empty($model->getChanges())) {
            $historyData['new_value'] = json_encode($model->toArray() ?? []);
            $historyData['old_value'] = json_encode([]);
        } else {
            // Untuk updated event
            $changes = $model->getChanges();
            $original = $model->getOriginal();
            $oldValues = array_intersect_key($original, $changes);
            
            $historyData['new_value'] = json_encode($changes);
            $historyData['old_value'] = json_encode($oldValues);
        }
        
        // Dispatch job ke queue
        LogHistoryJob::dispatch($historyData);
    }
}