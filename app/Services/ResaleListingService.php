<?php

namespace App\Services;

use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ResaleListingService
{
    private const MARKUP_CAP = 1.15;

    private const FLOOR_RATIO = 0.45;

    /**
     * Retrieve listings owned by the given seller with optional status filter.
     */
    public function getSellerListings(User $seller, ?string $status, int $perPage): LengthAwarePaginator
    {
        $perPage = min($perPage, 50);

        $query = ResaleListing::with(['ticket.event'])
            ->where('seller_id', $seller->id);

        if ($status) {
            $query->where('listing_status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Browse marketplace listings with filtering and pagination.
     */
    public function browseListings(?string $search, int $perPage): LengthAwarePaginator
    {
        $perPage = min($perPage, 50);

        $query = ResaleListing::with(['ticket.event', 'seller'])
            ->where('listing_status', 'aktif')
            ->where('verification_status', 'verified');

        if ($search) {
            $query->whereHas('ticket.event', function ($q) use ($search) {
                $q->where('event_name', 'like', '%'.$search.'%')
                    ->orWhere('venue_name', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%')
                    ->orWhere('event_category', 'like', '%'.$search.'%');
            });
        }

        return $query->latest('listed_at')->paginate($perPage);
    }

    /**
     * Create a marketplace listing for an existing ticket.
     *
     * @throws AccessDeniedHttpException
     * @throws ValidationException
     */
    public function createListing(User $user, Ticket $ticket, float $currentAskingPrice): ResaleListing
    {
        $this->assertOwnership($user, $ticket);
        $this->assertTicketIsActive($ticket);
        $this->assertNoActiveListing($ticket);

        $originalPrice = $this->resolveOriginalPrice($ticket);
        $floorPrice = round($originalPrice * self::FLOOR_RATIO, 2);
        $hardCapPrice = round($originalPrice * self::MARKUP_CAP, 2);

        $this->assertPriceBoundaries($currentAskingPrice, $floorPrice, $hardCapPrice);

        return DB::transaction(function () use ($ticket, $user, $originalPrice, $currentAskingPrice, $floorPrice, $hardCapPrice) {
            return ResaleListing::create([
                'ticket_id' => $ticket->id,
                'seller_id' => $user->id,
                'original_price' => $originalPrice,
                'current_asking_price' => $currentAskingPrice,
                'floor_price' => $floorPrice,
                'hard_cap_price' => $hardCapPrice,
                'verification_status' => 'pending',
                'listing_status' => 'ditangguhkan',
                'listed_at' => now(),
            ]);
        });
    }

    /**
     * Verify the user owns the ticket.
     */
    private function assertOwnership(User $user, Ticket $ticket): void
    {
        if ($ticket->current_owner_id !== $user->id) {
            throw new AccessDeniedHttpException('Anda tidak memiliki tiket ini.');
        }
    }

    /**
     * Verify the ticket status is aktif.
     */
    private function assertTicketIsActive(Ticket $ticket): void
    {
        if ($ticket->status !== 'aktif') {
            throw ValidationException::withMessages([
                'ticket_id' => ['Status tiket ini tidak aktif.'],
            ]);
        }
    }

    /**
     * Prevent duplicate active or suspended listings for the same ticket.
     */
    private function assertNoActiveListing(Ticket $ticket): void
    {
        $exists = ResaleListing::where('ticket_id', $ticket->id)
            ->whereIn('listing_status', ['aktif', 'ditangguhkan'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'ticket_id' => ['Tiket ini sudah ditawarkan untuk dijual.'],
            ]);
        }
    }

    /**
     * Retrieve original_price from ticket metadata.
     */
    private function resolveOriginalPrice(Ticket $ticket): float
    {
        $metadata = $ticket->ticket_metadata;

        if (! is_array($metadata) || ! isset($metadata['original_price'])) {
            throw ValidationException::withMessages([
                'ticket_id' => ['Tiket tidak memiliki data harga beli asli.'],
            ]);
        }

        return (float) $metadata['original_price'];
    }

    /**
     * Validate that asking price falls within computed boundaries.
     */
    private function assertPriceBoundaries(float $askingPrice, float $floorPrice, float $hardCapPrice): void
    {
        $errors = [];

        if ($askingPrice < $floorPrice) {
            $errors['current_asking_price'][] = "Harga jual tidak boleh lebih rendah dari batas bawah ({$floorPrice}).";
        }

        if ($askingPrice > $hardCapPrice) {
            $errors['current_asking_price'][] = "Harga jual tidak boleh melebihi batas atas ({$hardCapPrice}).";
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Cancel a marketplace listing.
     *
     * @throws AccessDeniedHttpException
     * @throws ValidationException
     */
    public function cancelListing(User $user, string $listingId): ResaleListing
    {
        return DB::transaction(function () use ($user, $listingId) {
            $listing = ResaleListing::where('id', $listingId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($listing->seller_id !== $user->id) {
                throw new AccessDeniedHttpException('Anda tidak berhak membatalkan listing tiket ini.');
            }

            if (! in_array($listing->listing_status, ['aktif', 'ditangguhkan', 'ditolak'])) {
                if ($listing->listing_status === 'dibatalkan') {
                    throw ValidationException::withMessages([
                        'listing' => ['Listing ini sudah dibatalkan sebelumnya.'],
                    ]);
                }
                throw ValidationException::withMessages([
                    'listing' => ['Listing dengan status ini tidak dapat dibatalkan.'],
                ]);
            }

            $hasActiveTransaction = $listing->transactions()
                ->whereIn('status', ['pending', 'paid', 'completed'])
                ->exists();

            if ($hasActiveTransaction) {
                throw ValidationException::withMessages([
                    'listing' => ['Listing tidak dapat dibatalkan karena memiliki transaksi yang sedang berjalan.'],
                ]);
            }

            $listing->update([
                'listing_status' => 'dibatalkan',
            ]);

            return $listing;
        });
    }
}
