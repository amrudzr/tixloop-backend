<?php

namespace App\Services;

use App\Models\TicketOwnershipHistory;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OwnershipTransferService
{
    /**
     * Simulate payment and execute full ownership transfer atomically.
     *
     * Wraps all state transitions in a single DB transaction:
     * - transaction status update
     * - escrow status update
     * - ticket ownership transfer
     * - ownership history creation
     * - listing status transition
     *
     * @throws ValidationException
     */
    public function simulatePaymentAndTransfer(Transaction $transaction): Transaction
    {
        $this->assertTransactionIsPending($transaction);

        return DB::transaction(function () use ($transaction) {
            $transaction->update([
                'status' => 'paid',
                'escrow_status' => 'held',
                'paid_at' => now(),
            ]);

            $ticket = $transaction->ticket;
            $previousOwnerId = $ticket->current_owner_id;

            $ticket->update([
                'current_owner_id' => $transaction->buyer_id,
            ]);

            TicketOwnershipHistory::create([
                'ticket_id' => $ticket->id,
                'transaction_id' => $transaction->id,
                'previous_owner_id' => $previousOwnerId,
                'new_owner_id' => $transaction->buyer_id,
                'transferred_at' => now(),
            ]);

            $transaction->resaleListing->update([
                'listing_status' => 'terjual',
                'sold_at' => now(),
            ]);

            $transaction->update([
                'status' => 'completed',
                'escrow_status' => 'released',
                'released_at' => now(),
                'completed_at' => now(),
            ]);

            return $transaction->fresh(['ticket', 'resaleListing', 'ownershipHistory']);
        });
    }

    /**
     * Assert that the transaction is in a valid state for payment simulation.
     */
    private function assertTransactionIsPending(Transaction $transaction): void
    {
        if ($transaction->status !== 'pending') {
            throw ValidationException::withMessages([
                'transaction' => ['This transaction is not in a valid state for payment.'],
            ]);
        }
    }
}
