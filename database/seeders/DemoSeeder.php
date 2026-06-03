<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            TicketSeeder::class,
            ResaleListingSeeder::class,
            TransactionSeeder::class,
            TicketOwnershipHistorySeeder::class,
        ]);
    }
}
