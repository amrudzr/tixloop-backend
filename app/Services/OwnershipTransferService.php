<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OwnershipTransferService
{
    /**
     * Simulate payment: mark transaction as paid and hold escrow.
     *
     * This is Phase 1 of the two-phase escrow flow.
     * Ownership is NOT transferred here — it happens on escrow release.
     *
     * @throws ValidationException
     */
    public function simulatePayment(Transaction $transaction): Transaction
    {
        return DB::transaction(function () use ($transaction) {
            $now = now();

            $transaction = Transaction::where('id', $transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertTransactionIsPending($transaction);

            $transaction->update([
                'status' => 'paid',
                'escrow_status' => 'held',
                'paid_at' => $now,
            ]);

            $ticket = Ticket::where('id', $transaction->ticket_id)
                ->lockForUpdate()
                ->firstOrFail();

            $ticket->update([
                'status' => 'dalam_escrow',
            ]);

            return $transaction->fresh(['ticket', 'resaleListing']);
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
