<?php

namespace Database\Seeders;

use App\Models\TicketOwnershipHistory;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TicketOwnershipHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all completed or paid transactions
        $transactions = Transaction::whereIn('status', ['completed', 'paid'])->get();

        foreach ($transactions as $transaction) {
            $ticket = $transaction->ticket;
            $previousOwnerId = $ticket->current_owner_id;

            // Update ticket owner to the buyer (simulate ownership transfer)
            $ticket->update([
                'current_owner_id' => $transaction->buyer_id,
            ]);

            // Create history entry
            TicketOwnershipHistory::create([
                'ticket_id' => $ticket->id,
                'transaction_id' => $transaction->id,
                'previous_owner_id' => $previousOwnerId,
                'new_owner_id' => $transaction->buyer_id,
                'transferred_at' => $transaction->paid_at ?? now(),
            ]);
        }
    }
}
