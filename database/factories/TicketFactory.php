<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'current_owner_id' => User::factory(),
            'original_buyer_id' => null,
            'ticket_code' => strtoupper($this->faker->bothify('TIX-####-????')),
            'seat_number' => $this->faker->optional(0.8)->bothify('??-##'),
            'ticket_metadata' => null,
            'ticket_proof_path' => null,
            'ticket_proof_type' => null,
            'proof_uploaded_at' => null,
            'qr_secret_key' => null,
            'device_binding_id' => null,
            'status' => 'aktif',
        ];
    }

    /**
     * Indicate that the ticket has proof uploaded.
     */
    public function withProof(): static
    {
        return $this->state(fn (array $attributes) => [
            'ticket_proof_path' => 'ticket_proofs/'.$this->faker->uuid().'.pdf',
            'ticket_proof_type' => $this->faker->randomElement(['application/pdf', 'image/jpeg', 'image/png']),
            'proof_uploaded_at' => now(),
        ]);
    }

    /**
     * Indicate the ticket has original price metadata (required for listing creation).
     */
    public function withOriginalPrice(float $price = 500000): static
    {
        return $this->state(fn (array $attributes) => [
            'ticket_metadata' => ['original_price' => $price],
        ]);
    }

    /**
     * Indicate that the ticket is active.
     */
    public function aktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'aktif',
        ]);
    }

    /**
     * Indicate that the ticket has been used.
     */
    public function digunakan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'digunakan',
            'used_at' => now(),
        ]);
    }

    /**
     * Indicate that the ticket is expired/burned.
     */
    public function hangus(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'hangus',
            'burned_at' => now(),
        ]);
    }
}
