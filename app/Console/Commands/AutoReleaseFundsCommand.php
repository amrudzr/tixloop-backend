<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use App\Services\EscrowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoReleaseFundsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tixloop:auto-release-funds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically release escrow funds for events completed more than 3 days ago';

    /**
     * Execute the console command.
     */
    public function handle(EscrowService $escrowService)
    {
        $this->info('Starting auto-release funds process...');
        Log::info('Starting auto-release funds process...');

        $admin = User::role('admin')->first();

        if (! $admin) {
            $this->error('System admin user not found. Aborting.');
            Log::error('System admin user not found. Aborting auto-release.');

            return Command::FAILURE;
        }

        $threeDaysAgo = now()->subDays(3);

        $query = Transaction::query()
            ->where('status', 'paid')
            ->where('escrow_status', 'held')
            ->whereHas('ticket.event', function ($query) use ($threeDaysAgo) {
                $query->where('event_datetime', '<=', $threeDaysAgo);
            });

        $totalFound = $query->count();
        $this->info("Found {$totalFound} transactions eligible for auto-release.");
        Log::info("Found {$totalFound} transactions eligible for auto-release.");

        $successCount = 0;
        $failCount = 0;

        $query->chunkById(100, function ($transactions) use ($escrowService, $admin, &$successCount, &$failCount) {
            foreach ($transactions as $transaction) {
                try {
                    $escrowService->releaseEscrow($transaction, $admin);
                    $successCount++;
                    $this->info("Successfully released funds for transaction ID: {$transaction->id}");
                } catch (Throwable $e) {
                    $failCount++;
                    $this->error("Failed to release funds for transaction ID: {$transaction->id}. Error: {$e->getMessage()}");
                    Log::error("Failed to release funds for transaction ID: {$transaction->id}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        });

        $this->info('Auto-release funds process completed.');
        $this->info("Total Processed: {$totalFound}");
        $this->info("Success: {$successCount}");
        $this->info("Failed: {$failCount}");

        Log::info('Auto-release funds process completed.', [
            'total_found' => $totalFound,
            'success_count' => $successCount,
            'fail_count' => $failCount,
        ]);

        return Command::SUCCESS;
    }
}
