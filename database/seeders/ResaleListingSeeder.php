<?php

namespace Database\Seeders;

use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResaleListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $student = User::where('email', 'student.seller@tixloop.com')->firstOrFail();
        $enthusiast = User::where('email', 'music.enthusiast@tixloop.com')->firstOrFail();

        // 1. Seed the 10 manual listings using specific tickets from TicketSeeder

        // Coldplay Ticket 1 (Budi/student): Verified Sold (to be linked to TX-001)
        $cpTicket1 = Ticket::where('ticket_code', 'like', 'CP-STU-%')->firstOrFail();
        ResaleListing::create([
            'id' => '01h35p38c4b140432eb162c781',
            'ticket_id' => $cpTicket1->id,
            'seller_id' => $student->id,
            'original_price' => 500000.00,
            'current_asking_price' => 550000.00,
            'floor_price' => 225000.00,
            'hard_cap_price' => 575000.00,
            'verification_status' => 'verified',
            'listing_status' => 'terjual',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'listed_at' => now()->subHours(10),
        ]);

        // Coldplay Ticket 2 (Budi/student): Verified Active
        $cpTicket2 = Ticket::where('ticket_code', 'like', 'CP-STU-%')->where('id', '!=', $cpTicket1->id)->firstOrFail();
        ResaleListing::create([
            'id' => '01h35p38c4b140432eb162c782',
            'ticket_id' => $cpTicket2->id,
            'seller_id' => $student->id,
            'original_price' => 500000.00,
            'current_asking_price' => 450000.00,
            'floor_price' => 225000.00,
            'hard_cap_price' => 575000.00,
            'verification_status' => 'verified',
            'listing_status' => 'aktif',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'listed_at' => now()->subHours(8),
        ]);

        // Coldplay Ticket 3 (Rian/enthusiast): Pending
        $cpTicket3 = Ticket::where('ticket_code', 'like', 'CP-ENT-%')->firstOrFail();
        ResaleListing::create([
            'id' => '01h35p38c4b140432eb162c783',
            'ticket_id' => $cpTicket3->id,
            'seller_id' => $enthusiast->id,
            'original_price' => 500000.00,
            'current_asking_price' => 900000.00,
            'floor_price' => 225000.00,
            'hard_cap_price' => 575000.00,
            'verification_status' => 'pending',
            'listing_status' => 'ditangguhkan',
            'listed_at' => now()->subHours(6),
        ]);

        // DWP Ticket 1 (Budi/student): Pending
        $dwpTicket1 = Ticket::where('ticket_code', 'like', 'DWP-STU-%')->firstOrFail();
        ResaleListing::create([
            'id' => '01h35p38c4b140432eb162c784',
            'ticket_id' => $dwpTicket1->id,
            'seller_id' => $student->id,
            'original_price' => 700000.00,
            'current_asking_price' => 800000.00,
            'floor_price' => 315000.00,
            'hard_cap_price' => 805000.00,
            'verification_status' => 'pending',
            'listing_status' => 'ditangguhkan',
            'listed_at' => now()->subHours(12),
        ]);

        // DWP Ticket 2 (Rian/enthusiast): Verified Sold (to be linked to TX-002)
        $dwpTicket2 = Ticket::where('ticket_code', 'like', 'DWP-ENT-%')->firstOrFail();
        ResaleListing::create([
            'id' => '01h35p38c4b140432eb162c785',
            'ticket_id' => $dwpTicket2->id,
            'seller_id' => $enthusiast->id,
            'original_price' => 700000.00,
            'current_asking_price' => 750000.00,
            'floor_price' => 315000.00,
            'hard_cap_price' => 805000.00,
            'verification_status' => 'verified',
            'listing_status' => 'terjual',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'listed_at' => now()->subHours(10),
        ]);

        // DWP Ticket 3 (Rian/enthusiast): Verified Active (with TX-003 pending checkout)
        $dwpTicket3 = Ticket::where('ticket_code', 'like', 'DWP-ENT-%')->where('id', '!=', $dwpTicket2->id)->firstOrFail();
        ResaleListing::create([
            'id' => '01h35p38c4b140432eb162c786',
            'ticket_id' => $dwpTicket3->id,
            'seller_id' => $enthusiast->id,
            'original_price' => 700000.00,
            'current_asking_price' => 450000.00,
            'floor_price' => 315000.00,
            'hard_cap_price' => 805000.00,
            'verification_status' => 'verified',
            'listing_status' => 'aktif',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'listed_at' => now()->subHours(5),
        ]);

        // Sports Ticket 1 (Budi/student): Verified Active (with TX-004 failed checkout)
        $sportsTicket1 = Ticket::where('ticket_code', 'like', 'SPO-STU-%')->firstOrFail();
        ResaleListing::create([
            'id' => '01h35p38c4b140432eb162c787',
            'ticket_id' => $sportsTicket1->id,
            'seller_id' => $student->id,
            'original_price' => 900000.00,
            'current_asking_price' => 1000000.00,
            'floor_price' => 405000.00,
            'hard_cap_price' => 1035000.00,
            'verification_status' => 'verified',
            'listing_status' => 'aktif',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'listed_at' => now()->subHours(24),
        ]);

        // Sports Ticket 2 (Rian/enthusiast): Rejected
        $sportsTicket2 = Ticket::where('ticket_code', 'like', 'SPO-ENT-%')->firstOrFail();
        ResaleListing::create([
            'id' => '01h35p38c4b140432eb162c788',
            'ticket_id' => $sportsTicket2->id,
            'seller_id' => $enthusiast->id,
            'original_price' => 900000.00,
            'current_asking_price' => 600000.00,
            'floor_price' => 405000.00,
            'hard_cap_price' => 1035000.00,
            'verification_status' => 'rejected',
            'listing_status' => 'ditangguhkan',
            'verified_by' => $admin->id,
            'rejection_reason' => 'Ticket proof is unreadable or forged.',
            'listed_at' => now()->subHours(2),
        ]);

        // Tech Summit Ticket 1 (Rian/enthusiast): Verified Active
        $seminarTicket1 = Ticket::where('ticket_code', 'like', 'SEM-ENT-%')->firstOrFail();
        ResaleListing::create([
            'id' => '01h35p38c4b140432eb162c789',
            'ticket_id' => $seminarTicket1->id,
            'seller_id' => $enthusiast->id,
            'original_price' => 250000.00,
            'current_asking_price' => 300000.00,
            'floor_price' => 112500.00,
            'hard_cap_price' => 287500.00,
            'verification_status' => 'verified',
            'listing_status' => 'aktif',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'listed_at' => now()->subHours(4),
        ]);

        // Taylor Swift Ticket 1 (Budi/student): Verified Active (expired, ticket hangus)
        $taylorTicket1 = Ticket::where('ticket_code', 'like', 'TS-STU-B-%')->firstOrFail();
        ResaleListing::create([
            'id' => '01h35p38c4b140432eb162c790',
            'ticket_id' => $taylorTicket1->id,
            'seller_id' => $student->id,
            'original_price' => 1500000.00,
            'current_asking_price' => 1500000.00,
            'floor_price' => 675000.00,
            'hard_cap_price' => 1725000.00,
            'verification_status' => 'verified',
            'listing_status' => 'aktif',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'listed_at' => now()->subDays(5),
        ]);

        // 2. Seed 25 remaining listings using other tickets

        // Let's get the tickets that are not listed yet
        $listedTicketIds = ResaleListing::pluck('ticket_id')->toArray();
        $availableTickets = Ticket::whereNotIn('id', $listedTicketIds)->get();

        // Target remaining:
        // - 11 verified active
        // - 2 verified sold
        // - 8 pending
        // - 4 rejected

        // We will loop and create them
        $index = 0;

        // Verified Active (11)
        for ($i = 0; $i < 11; $i++) {
            $ticket = $availableTickets->get($index++);
            ResaleListing::factory()->verified()->create([
                'ticket_id' => $ticket->id,
                'seller_id' => $ticket->current_owner_id,
            ]);
        }

        // Verified Sold (2)
        for ($i = 0; $i < 2; $i++) {
            $ticket = $availableTickets->get($index++);
            ResaleListing::factory()->verified()->create([
                'ticket_id' => $ticket->id,
                'seller_id' => $ticket->current_owner_id,
                'listing_status' => 'terjual',
                'sold_at' => now()->subHours(1),
            ]);
        }

        // Pending (8)
        for ($i = 0; $i < 8; $i++) {
            $ticket = $availableTickets->get($index++);
            ResaleListing::factory()->pending()->create([
                'ticket_id' => $ticket->id,
                'seller_id' => $ticket->current_owner_id,
            ]);
        }

        // Rejected (4)
        for ($i = 0; $i < 4; $i++) {
            $ticket = $availableTickets->get($index++);
            ResaleListing::factory()->rejected()->create([
                'ticket_id' => $ticket->id,
                'seller_id' => $ticket->current_owner_id,
                'verified_by' => $admin->id,
            ]);
        }
    }
}
