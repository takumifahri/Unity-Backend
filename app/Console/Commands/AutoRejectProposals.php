<?php

namespace App\Console\Commands;

use App\Models\CustomOrder;
use Illuminate\Console\Command;

class AutoRejectProposals extends Command
{
   /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-orders:auto-reject';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically reject custom orders that have been pending for more than 2 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $twoDaysAgo = now()->subDays(2);

        $pendingOrders = CustomOrder::where('status', 'pending')
            ->where('created_at', '<=', $twoDaysAgo)
            ->get();

        foreach ($pendingOrders as $order) {
            $order->status = 'ditolak';
            $order->save();

            $this->info("Custom order ID {$order->id} has been automatically rejected.");
        }

        $this->info('Auto-rejection process completed.');
    }
}
