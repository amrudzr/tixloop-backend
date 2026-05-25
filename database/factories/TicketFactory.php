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
            'seller_id' => User::factory(),
            'ticket_type' => $this->faker->randomElement(['VIP', 'CAT 1', 'CAT 2', 'General Admission']),
            'seat_number' => $this->faker->optional(0.8)->bothify('??-##'),
            'price' => $this->faker->randomFloat(2, 50000, 1500000),
            'ticket_file_path' => 'proofs/'.$this->faker->uuid().'.pdf',
            'is_verified' => $this->faker->boolean(40),
        ];
    }
}
