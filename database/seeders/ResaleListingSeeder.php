<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\ResaleListing;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResaleListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $event = Event::first();
        $user = User::first();

        ResaleListing::create([
            'event_id' => $event->id,
            'seller_id' => $user->id,
            'ticket_type' => 'Festival',
            'original_price' => 2500000,
            'current_price' => 1850000,
            'floor_price' => 1100000,
            'verified_seller' => true,
        ]);
    }
}
