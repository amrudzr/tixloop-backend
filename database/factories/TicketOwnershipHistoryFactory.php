<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketOwnershipHistory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketOwnershipHistory>
 */
class TicketOwnershipHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'transaction_id' => Transaction::factory(),
            'previous_owner_id' => User::factory(),
            'new_owner_id' => User::factory(),
            'transferred_at' => now(),
        ];
    }
}
