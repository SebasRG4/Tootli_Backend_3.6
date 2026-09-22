<?php

namespace App\Console\Commands;

use App\CentralLogics\CustomerLogic;
use App\Models\ProtectedTransaction;
use App\Models\ServiceJob;
use App\Models\StoreWallet;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoReleaseEscrowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tootli:auto-release-escrow';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Automatically releases escrow funds to sellers and service professionals after the protection period (48h) if no dispute is opened.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Tootli Protector auto-release escrow check...');

        // 1. Process Protected Transactions (P2P products / deals)
        $transactions = ProtectedTransaction::where('status', 'paid')
            ->whereNotNull('auto_release_at')
            ->where('auto_release_at', '<=', now())
            ->with(['dispute', 'seller'])
            ->get();

        $releasedTxCount = 0;
        foreach ($transactions as $tx) {
            // Skip if an active dispute exists
            if ($tx->dispute && in_array($tx->dispute->status, ['open', 'under_review'])) {
                continue;
            }

            try {
                DB::transaction(function () use ($tx) {
                    $tx->status = 'completed';
                    $tx->save();

                    if ($tx->seller_id) {
                        $seller = User::find($tx->seller_id);
                        if ($seller) {
                            $seller->wallet_balance += $tx->amount;
                            $seller->save();

                            CustomerLogic::create_wallet_transaction(
                                $seller->id,
                                $tx->amount,
                                'add_fund_by_admin',
                                'auto_release_protected_tx_' . $tx->id
                            );
                        }
                    }
                });

                $releasedTxCount++;
                $this->info("Auto-released funds for Protected Transaction #{$tx->id} ({$tx->amount} MXN to seller #{$tx->seller_id})");
            } catch (\Exception $e) {
                Log::error("Error auto-releasing Protected Transaction #{$tx->id}: " . $e->getMessage());
            }
        }

        // 2. Process Service Jobs (Professionals / Services)
        $jobs = ServiceJob::where('status', 'accepted')
            ->where('payment_status', 'paid')
            ->whereNotNull('auto_release_at')
            ->where('auto_release_at', '<=', now())
            ->with(['dispute', 'acceptedBid.store'])
            ->get();

        $releasedJobCount = 0;
        foreach ($jobs as $job) {
            // Skip if an active dispute exists
            if ($job->dispute && in_array($job->dispute->status, ['open', 'under_review'])) {
                continue;
            }

            try {
                DB::transaction(function () use ($job) {
                    $job->status = 'completed';
                    $job->save();

                    if ($job->acceptedBid && $job->acceptedBid->store) {
                        $store = $job->acceptedBid->store;
                        $store_wallet = StoreWallet::firstOrNew(['vendor_id' => $store->vendor_id]);
                        $store_wallet->total_earning += $job->acceptedBid->price;
                        $store_wallet->save();
                    }
                });

                $releasedJobCount++;
                $this->info("Auto-released funds for Service Job #{$job->id}");
            } catch (\Exception $e) {
                Log::error("Error auto-releasing Service Job #{$job->id}: " . $e->getMessage());
            }
        }

        $this->info("Auto-release completed: {$releasedTxCount} transactions and {$releasedJobCount} jobs processed.");
        return Command::SUCCESS;
    }
}
