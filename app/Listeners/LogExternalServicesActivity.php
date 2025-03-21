<?php

namespace App\Listeners;

use App\Services\HistoryServices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogExternalServicesActivity
{
    protected $historyServices;
    /**
     * Create the event listener.
     */
    public function __construct(HistoryServices $historyServices)
    {
        //
        $this->historyServices = $historyServices;
    }

    /**
     * Handle the event.
     */
    public function handle( $event): void
    {
        //
    }
}
