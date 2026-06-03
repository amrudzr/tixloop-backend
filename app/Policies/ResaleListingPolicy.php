<?php

namespace App\Policies;

use App\Models\ResaleListing;
use App\Models\User;

class ResaleListingPolicy
{
    /**
     * Determine if the user can browse pending listings.
     */
    public function viewPending(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can verify a listing.
     */
    public function verify(User $user, ResaleListing $listing): bool
    {
        return $user->hasRole('admin') && $listing->verification_status === 'pending';
    }

    /**
     * Determine if the user can reject a listing.
     */
    public function reject(User $user, ResaleListing $listing): bool
    {
        return $user->hasRole('admin') && $listing->verification_status === 'pending';
    }
}
