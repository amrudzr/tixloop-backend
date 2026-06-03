<?php

namespace Database\Seeders;

use App\Models\ResaleListing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siti = User::where('email', 'concert.hunter@tixloop.com')->firstOrFail();
        $diana = User::where('email', 'event.collector@tixloop.com')->firstOrFail();

        $buyerUsers = User::role('buyer')
            ->whereNotIn('email', [$siti->email, $diana->email])
            ->get();

        // TX-001: Coldplay, Budi -> Siti, status completed
        $listing1 = ResaleListing::where('id', '01h35p38c4b140432eb162c781')->firstOrFail();
        Transaction::create([
            'id' => '01h35p38c4b140432eb162tx01',
            'buyer_id' => $siti->id,
            'seller_id' => $listing1->seller_id,
            'resale_listing_id' => $listing1->id,
            'ticket_id' => $listing1->ticket_id,
            'amount' => 550000.00,
            'service_fee' => 0.00,
            'status' => 'completed',
            'escrow_status' => 'released',
            'paid_at' => now()->subHours(4),
            'released_at' => now()->subHours(3),
            'completed_at' => now()->subHours(3),
        ]);

        // TX-002: DWP, Rian -> Diana, status paid
        $listing2 = ResaleListing::where('id', '01h35p38c4b140432eb162c785')->firstOrFail();
        Transaction::create([
            'id' => '01h35p38c4b140432eb162tx02',
            'buyer_id' => $diana->id,
            'seller_id' => $listing2->seller_id,
            'resale_listing_id' => $listing2->id,
            'ticket_id' => $listing2->ticket_id,
            'amount' => 750000.00,
            'service_fee' => 0.00,
            'status' => 'paid',
            'escrow_status' => 'held',
            'paid_at' => now()->subHours(2),
        ]);

        // TX-003: DWP, Rian -> Siti, status pending
        $listing3 = ResaleListing::where('id', '01h35p38c4b140432eb162c786')->firstOrFail();
        Transaction::create([
            'id' => '01h35p38c4b140432eb162tx03',
            'buyer_id' => $siti->id,
            'seller_id' => $listing3->seller_id,
            'resale_listing_id' => $listing3->id,
            'ticket_id' => $listing3->ticket_id,
            'amount' => 450000.00,
            'service_fee' => 0.00,
            'status' => 'pending',
            'escrow_status' => null,
        ]);

        // TX-004: Sports, Budi -> Diana, status failed
        $listing4 = ResaleListing::where('id', '01h35p38c4b140432eb162c787')->firstOrFail();
        Transaction::create([
            'id' => '01h35p38c4b140432eb162tx04',
            'buyer_id' => $diana->id,
            'seller_id' => $listing4->seller_id,
            'resale_listing_id' => $listing4->id,
            'ticket_id' => $listing4->ticket_id,
            'amount' => 1000000.00,
            'service_fee' => 0.00,
            'status' => 'failed',
            'escrow_status' => null,
        ]);

        // Fetch two factory sold listings to link with TX-005 and TX-006
        $soldListings = ResaleListing::where('listing_status', 'terjual')
            ->whereNotIn('id', [$listing1->id, $listing2->id])
            ->get();

        // TX-005: Factory -> Factory, status completed
        $listing5 = $soldListings->get(0);
        Transaction::create([
            'id' => '01h35p38c4b140432eb162tx05',
            'buyer_id' => $buyerUsers->random()->id,
            'seller_id' => $listing5->seller_id,
            'resale_listing_id' => $listing5->id,
            'ticket_id' => $listing5->ticket_id,
            'amount' => $listing5->current_asking_price,
            'service_fee' => 0.00,
            'status' => 'completed',
            'escrow_status' => 'released',
            'paid_at' => now()->subHours(6),
            'released_at' => now()->subHours(5),
            'completed_at' => now()->subHours(5),
        ]);

        // TX-006: Factory -> Factory, status paid
        $listing6 = $soldListings->get(1);
        Transaction::create([
            'id' => '01h35p38c4b140432eb162tx06',
            'buyer_id' => $buyerUsers->random()->id,
            'seller_id' => $listing6->seller_id,
            'resale_listing_id' => $listing6->id,
            'ticket_id' => $listing6->ticket_id,
            'amount' => $listing6->current_asking_price,
            'service_fee' => 0.00,
            'status' => 'paid',
            'escrow_status' => 'held',
            'paid_at' => now()->subHours(1),
        ]);

        // TX-007: Factory -> Factory, status pending
        // Get one active verified listing (not manual)
        $activeListings = ResaleListing::where('listing_status', 'aktif')
            ->where('verification_status', 'verified')
            ->whereNotIn('id', [$listing3->id, $listing4->id])
            ->get();
        $listing7 = $activeListings->random();

        // Find a buyer that is not the seller of this listing
        $buyer7 = $buyerUsers->where('id', '!=', $listing7->seller_id)->first();
        if (! $buyer7) {
            $buyer7 = $siti;
        }

        Transaction::create([
            'id' => '01h35p38c4b140432eb162tx07',
            'buyer_id' => $buyer7->id,
            'seller_id' => $listing7->seller_id,
            'resale_listing_id' => $listing7->id,
            'ticket_id' => $listing7->ticket_id,
            'amount' => $listing7->current_asking_price,
            'service_fee' => 0.00,
            'status' => 'pending',
            'escrow_status' => null,
        ]);
    }
}
