<?php

namespace Database\Factories;

use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResaleListing>
 */
class ResaleListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalPrice = $this->faker->randomFloat(2, 100000, 2000000);
        $floorPrice = round($originalPrice * 0.45, 2);
        $hardCapPrice = round($originalPrice * 1.15, 2);

        return [
            'ticket_id' => Ticket::factory(),
            'seller_id' => User::factory(),
            'original_price' => $originalPrice,
            'current_asking_price' => $this->faker->randomFloat(2, $floorPrice, $hardCapPrice),
            'floor_price' => $floorPrice,
            'hard_cap_price' => $hardCapPrice,
            'verification_status' => 'pending',
            'listing_status' => 'aktif',
            'listed_at' => now(),
            'sold_at' => null,
        ];
    }

    /**
     * Indicate that the listing is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'verified',
            'listing_status' => 'aktif',
            'verified_at' => now(),
            'verified_by' => User::factory(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the listing is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'rejected',
            'listing_status' => 'ditolak',
            'verified_by' => User::factory(),
            'rejection_reason' => 'Ticket proof is unreadable or forged.',
        ]);
    }

    /**
     * Indicate that the listing is sold.
     */
    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'verified',
            'listing_status' => 'terjual',
            'verified_at' => now(),
            'verified_by' => User::factory(),
            'sold_at' => now(),
        ]);
    }

    /**
     * Indicate that the listing is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'pending',
            'listing_status' => 'ditangguhkan',
            'verified_at' => null,
            'verified_by' => null,
            'rejection_reason' => null,
        ]);
    }
}
