<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HistoryResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $request->user();
        
        return [
            'id' => $this->id,
            'items_id' => $this->items_id,
            'model_type' => $this->model_type,
            'user_id' => $this->user_id,
            'user_name' => $this->user ? $this->user->name : 'System',
            'reason' => $this->reason,
            'new_value' => $this->new_value,
            'old_value' => $this->old_value,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Format untuk menentukan tampilan di frontend
            'is_deletion' => strpos($this->reason, 'dihapus:') !== false,
            'deletion_reason' => strpos($this->reason, 'dihapus:') !== false 
                ? trim(explode(':', $this->reason)[1]) 
                : null,
                
            // Format yang berbeda untuk admin dan user
            'formatted_message' => $user && $user->isAdmin()
                ? $this->getAdminFormattedMessage()
                : $this->getUserFormattedMessage(),
        ];
    }
    
    protected function getAdminFormattedMessage()
    {
        $userName = $this->user ? $this->user->name : 'System';
        return "{$userName} {$this->reason} pada " . $this->created_at->format('d M Y H:i');
    }
    
    protected function getUserFormattedMessage()
    {
        return ucfirst($this->reason) . " pada " . $this->created_at->format('d M Y H:i');
    }
}