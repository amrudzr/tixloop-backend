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
        ]);
    }

    /**
     * Indicate that the listing is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'rejected',
        ]);
    }
}
