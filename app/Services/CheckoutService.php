<?php

namespace App\Services;

use App\Models\ResaleListing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckoutService
{
    /**
     * Initiate a checkout transaction for a resale listing.
     *
     * Uses row-level locking and a database partial unique index
     * to prevent concurrent double-purchase race conditions.
     *
     * @throws AccessDeniedHttpException
     * @throws ValidationException
     */
    public function initiateCheckout(User $buyer, ResaleListing $listing): Transaction
    {
        return DB::transaction(function () use ($buyer, $listing) {
            $lockedListing = ResaleListing::where('id', $listing->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertBuyerIsNotSeller($buyer, $lockedListing);
            $this->assertListingIsAvailable($lockedListing);
            $this->assertNoActiveTransaction($lockedListing);

            return Transaction::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $lockedListing->seller_id,
                'resale_listing_id' => $lockedListing->id,
                'ticket_id' => $lockedListing->ticket_id,
                'amount' => $lockedListing->current_asking_price,
                'service_fee' => 0.00,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Verify the buyer is not the seller.
     */
    private function assertBuyerIsNotSeller(User $buyer, ResaleListing $listing): void
    {
        if ($buyer->id === $listing->seller_id) {
            throw new AccessDeniedHttpException('Anda tidak dapat membeli tiket Anda sendiri.');
        }
    }

    /**
     * Verify the listing is available for checkout.
     */
    private function assertListingIsAvailable(ResaleListing $listing): void
    {
        if ($listing->listing_status !== 'aktif' || $listing->verification_status !== 'verified') {
            throw ValidationException::withMessages([
                'listing' => ['Tiket ini tidak tersedia untuk dibeli.'],
            ]);
        }
    }

    /**
     * Verify no active transaction exists for this listing.
     */
    private function assertNoActiveTransaction(ResaleListing $listing): void
    {
        $exists = Transaction::where('resale_listing_id', $listing->id)
            ->whereIn('status', ['pending', 'paid'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'listing' => ['Tiket ini sedang dalam proses transaksi lain.'],
            ]);
        }
    }
}
