<?php

namespace App\Services;

use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\TicketOwnershipHistory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EscrowService
{
    /**
     * Release escrow: transfer ownership and complete transaction.
     *
     * This is Phase 2 of the two-phase escrow flow.
     * All state transitions are atomic under row-level locks.
     *
     * @throws ValidationException
     */
    public function releaseEscrow(Transaction $transaction, User $releasedBy): Transaction
    {
        return DB::transaction(function () use ($transaction, $releasedBy) {
            $now = now();

            $transaction = Transaction::where('id', $transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertEscrowIsHeld($transaction);

            $ticket = Ticket::where('id', $transaction->ticket_id)
                ->lockForUpdate()
                ->firstOrFail();

            $previousOwnerId = $ticket->current_owner_id;

            $ticket->update([
                'current_owner_id' => $transaction->buyer_id,
                'status' => 'terjual',
            ]);

            TicketOwnershipHistory::create([
                'ticket_id' => $ticket->id,
                'transaction_id' => $transaction->id,
                'previous_owner_id' => $previousOwnerId,
                'new_owner_id' => $transaction->buyer_id,
                'transferred_at' => $now,
            ]);

            $listing = ResaleListing::where('id', $transaction->resale_listing_id)
                ->lockForUpdate()
                ->firstOrFail();

            $listing->update([
                'listing_status' => 'terjual',
                'sold_at' => $now,
            ]);

            $transaction->update([
                'status' => 'completed',
                'escrow_status' => 'released',
                'released_at' => $now,
                'released_by' => $releasedBy->id,
                'completed_at' => $now,
            ]);

            return $transaction->fresh(['ticket', 'resaleListing', 'ownershipHistory']);
        });
    }

    /**
     * Assert transaction is in valid state for escrow release.
     */
    private function assertEscrowIsHeld(Transaction $transaction): void
    {
        if ($transaction->status !== 'paid' || $transaction->escrow_status !== 'held') {
            throw ValidationException::withMessages([
                'transaction' => ['Escrow is not in a valid state for release.'],
            ]);
        }
    }
}
