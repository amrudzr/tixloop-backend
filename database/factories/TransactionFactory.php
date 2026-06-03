<?php

namespace Database\Factories;

use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'buyer_id' => User::factory(),
            'seller_id' => User::factory(),
            'resale_listing_id' => ResaleListing::factory(),
            'ticket_id' => Ticket::factory(),
            'amount' => $this->faker->randomFloat(2, 100000, 2000000),
            'service_fee' => 0.00,
            'status' => 'pending',
            'escrow_status' => null,
            'payment_reference' => null,
            'paid_at' => null,
            'released_at' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the transaction is completed with escrow released.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'escrow_status' => 'released',
            'paid_at' => now(),
            'released_at' => now(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the transaction has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'escrow_status' => null,
        ]);
    }

    /**
     * Indicate that the transaction is paid with escrow held.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'escrow_status' => 'held',
            'paid_at' => now(),
        ]);
    }
}
