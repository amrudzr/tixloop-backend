<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine if the user can view the transaction.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->buyer_id
            || $user->id === $transaction->seller_id
            || $user->hasRole('admin');
    }

    /**
     * Determine if the user can simulate payment for the transaction.
     */
    public function simulatePayment(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->buyer_id
            && $transaction->status === 'pending';
    }

    /**
     * Determine if the user can release escrow for the transaction.
     *
     * Only the buyer or an admin can release. Seller cannot.
     */
    public function releaseEscrow(User $user, Transaction $transaction): bool
    {
        $isAuthorized = $user->id === $transaction->buyer_id
            || $user->hasRole('admin');

        return $isAuthorized
            && $transaction->status === 'paid'
            && $transaction->escrow_status === 'held';
    }
}
