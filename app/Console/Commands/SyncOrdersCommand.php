<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Trade;
use App\Services\AlpacaService;
use Illuminate\Support\Facades\Log;

class SyncOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize the status of pending/queued orders with Alpaca';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting background order status synchronization...");

        $trades = Trade::whereNotIn('status', ['filled', 'rejected', 'canceled', 'cancelled', 'expired'])
            ->whereNotNull('broker_order_id')
            ->where('broker_order_id', '!=', '')
            ->get();

        if ($trades->isEmpty()) {
            $this->info("No pending trades to synchronize.");
            return Command::SUCCESS;
        }

        $this->info("Found {$trades->count()} pending trades. Sincronizando...");

        foreach ($trades as $trade) {
            $user = $trade->user;
            if (!$user) {
                $this->warn("Trade ID {$trade->id} has no associated user. Skipping.");
                continue;
            }

            $isPaper = $trade->is_dry_run;
            $keyId = $isPaper ? ($user->alpaca_key_id ?? '') : ($user->alpaca_live_key_id ?? '');
            $secretKey = $isPaper ? ($user->alpaca_secret_key ?? '') : ($user->alpaca_live_secret_key ?? '');
            $accountId = $isPaper ? $user->alpaca_account_id : $user->alpaca_live_account_id;

            $service = new AlpacaService($keyId, $secretKey, $accountId, $isPaper);

            if (!$service->isConfigured()) {
                $this->warn("Alpaca credentials not configured for User ID {$user->id}. Skipping Trade ID {$trade->id}.");
                continue;
            }

            $order = $service->getOrder($trade->broker_order_id);
            if ($order && isset($order['status'])) {
                $newStatus = strtolower($order['status']);
                if ($trade->status !== $newStatus) {
                    $this->info("Updating Trade ID {$trade->id} ({$trade->symbol}): {$trade->status} -> {$newStatus}");
                    Log::info("Order Sync: Trade ID {$trade->id} ({$trade->symbol}) status updated from {$trade->status} to {$newStatus}");
                    
                    $updateData = ['status' => $newStatus];
                    
                    if ($newStatus === 'filled' && isset($order['filled_avg_price']) && (float)$order['filled_avg_price'] > 0) {
                        $updateData['price'] = (float)$order['filled_avg_price'];
                    }

                    $trade->update($updateData);
                } else {
                    $this->line("Trade ID {$trade->id} ({$trade->symbol}) remains {$trade->status}");
                }
            } else {
                $this->error("Failed to fetch order status for Trade ID {$trade->id} (Broker ID: {$trade->broker_order_id})");
            }
        }

        $this->info("Order synchronization completed.");
        return Command::SUCCESS;
    }
}
