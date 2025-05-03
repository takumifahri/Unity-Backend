<?php

namespace App\Listeners;

use App\Events\CustomOrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Queue;
class AutoRejectCustomOrder
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CustomOrderCreated $event): void
    {
        //
        $customOrder = $event->customOrder;
                // Tambahkan job ke queue untuk dijalankan setelah 2 hari
        Queue::later(now()->addDays(2), function () use ($customOrder) {
            if ($customOrder->status === 'pending') {
                $customOrder->status = 'ditolak';
                $customOrder->save();
            }
        });
    }
}
