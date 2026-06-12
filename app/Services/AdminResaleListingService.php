<?php

namespace App\Services;

use App\Models\ResaleListing;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminResaleListingService
{
    /**
     * Paginate pending listings with optional search.
     *
     * Search scope: event_name, seller name, ticket_code.
     */
    public function getPendingListings(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        $query = ResaleListing::with(['ticket.event', 'seller'])
            ->where('verification_status', 'pending');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('ticket.event', function ($eq) use ($search) {
                    $eq->where('event_name', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('seller', function ($sq) use ($search) {
                        $sq->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('ticket', function ($tq) use ($search) {
                        $tq->where('ticket_code', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Verify and activate a pending listing.
     *
     * @throws ValidationException
     */
    public function verifyListing(ResaleListing $listing, User $admin): ResaleListing
    {
        $this->assertPendingStatus($listing);

        return DB::transaction(function () use ($listing, $admin) {
            $listing->update([
                'verification_status' => 'verified',
                'listing_status' => 'aktif',
                'verified_at' => now(),
                'verified_by' => $admin->id,
                'rejection_reason' => null,
            ]);

            return $listing;
        });
    }

    /**
     * Reject a pending listing with a reason.
     *
     * @throws ValidationException
     */
    public function rejectListing(ResaleListing $listing, User $admin, string $reason): ResaleListing
    {
        $this->assertCanBeRejected($listing);

        return DB::transaction(function () use ($listing, $admin, $reason) {
            $listing->update([
                'verification_status' => 'rejected',
                'listing_status' => 'ditolak',
                'verified_at' => null,
                'verified_by' => $admin->id,
                'rejection_reason' => $reason,
            ]);

            return $listing;
        });
    }

    /**
     * Assert the listing is in pending status before any action.
     *
     * @throws ValidationException
     */
    private function assertPendingStatus(ResaleListing $listing): void
    {
        if ($listing->verification_status !== 'pending') {
            throw ValidationException::withMessages([
                'listing' => ['Tiket tidak dalam status menunggu verifikasi.'],
            ]);
        }
    }

    /**
     * Assert the listing can be rejected (is pending or active).
     *
     * @throws ValidationException
     */
    private function assertCanBeRejected(ResaleListing $listing): void
    {
        if (! in_array($listing->verification_status, ['pending', 'verified'])) {
            throw ValidationException::withMessages([
                'listing' => ['Tiket tidak dapat ditolak karena status verifikasi saat ini.'],
            ]);
        }

        if (! in_array($listing->listing_status, ['menunggu_verifikasi', 'aktif', 'ditangguhkan'])) {
            throw ValidationException::withMessages([
                'listing' => ['Tiket tidak dapat ditolak karena sedang diproses atau sudah terjual.'],
            ]);
        }
    }
}
