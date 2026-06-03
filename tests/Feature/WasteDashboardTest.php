<?php

use App\Models\Event;
use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

test('retrieves potential impact metrics correctly', function () {
    $seller = User::factory()->create();
    $event = Event::factory()->create(['event_category' => 'Music']);
    $ticket1 = Ticket::factory()->create(['event_id' => $event->id, 'current_owner_id' => $seller->id]);
    $ticket2 = Ticket::factory()->create(['event_id' => $event->id, 'current_owner_id' => $seller->id]);
    $ticket3 = Ticket::factory()->create(['event_id' => $event->id, 'current_owner_id' => $seller->id]);

    // 2 verified + active listings
    ResaleListing::factory()->create([
        'ticket_id' => $ticket1->id,
        'seller_id' => $seller->id,
        'verification_status' => 'verified',
        'listing_status' => 'aktif',
        'current_asking_price' => 500000,
    ]);
    ResaleListing::factory()->create([
        'ticket_id' => $ticket2->id,
        'seller_id' => $seller->id,
        'verification_status' => 'verified',
        'listing_status' => 'aktif',
        'current_asking_price' => 300000,
    ]);

    // 1 pending listing (should count in breakdown but NOT in potential impact)
    ResaleListing::factory()->create([
        'ticket_id' => $ticket3->id,
        'seller_id' => $seller->id,
        'verification_status' => 'pending',
        'listing_status' => 'aktif',
        'current_asking_price' => 200000,
    ]);

    $response = $this->getJson('/api/v1/dashboard/waste');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.potential_impact.potentially_rescued_tickets', 2)
        ->assertJsonPath('data.potential_impact.verified_listings', 2)
        ->assertJsonPath('data.potential_impact.active_listings', 3)
        ->assertJsonPath('data.potential_impact.listing_value_available', 800000)
        ->assertJsonPath('data.listings_breakdown.total_listings', 3)
        ->assertJsonPath('data.listings_breakdown.pending_listings', 1)
        ->assertJsonPath('data.listings_breakdown.rejected_listings', 0)
        ->assertJsonPath('data.listings_breakdown.total_listing_value', 1000000)
        ->assertJsonPath('data.listings_breakdown.verified_listing_value', 800000);
});

test('response structure contains expected keys and no future placeholders', function () {
    ResaleListing::factory()->verified()->create(['current_asking_price' => 100000]);

    $response = $this->getJson('/api/v1/dashboard/waste');

    $response->assertStatus(200)
        ->assertJson(function (AssertableJson $json) {
            $json->has('data.potential_impact.potentially_rescued_tickets')
                ->has('data.potential_impact.verified_listings')
                ->has('data.potential_impact.active_listings')
                ->has('data.potential_impact.listing_value_available')
                ->has('data.listings_breakdown.total_listings')
                ->has('data.listings_breakdown.pending_listings')
                ->has('data.listings_breakdown.rejected_listings')
                ->has('data.listings_breakdown.total_listing_value')
                ->has('data.listings_breakdown.verified_listing_value')
                ->has('data.metadata.filters')
                ->has('data.metadata.calculated_at')
                ->missing('data.actual_impact_future')
                ->etc();
        });
});

test('filters metrics by event category', function () {
    $musicEvent = Event::factory()->create(['event_category' => 'Music']);
    $sportsEvent = Event::factory()->create(['event_category' => 'Sports']);

    $musicTicket = Ticket::factory()->create(['event_id' => $musicEvent->id]);
    $sportsTicket = Ticket::factory()->create(['event_id' => $sportsEvent->id]);

    ResaleListing::factory()->verified()->create([
        'ticket_id' => $musicTicket->id,
        'current_asking_price' => 500000,
    ]);
    ResaleListing::factory()->verified()->create([
        'ticket_id' => $sportsTicket->id,
        'current_asking_price' => 300000,
    ]);

    $response = $this->getJson('/api/v1/dashboard/waste?category=Music');

    $response->assertStatus(200)
        ->assertJsonPath('data.potential_impact.potentially_rescued_tickets', 1)
        ->assertJsonPath('data.potential_impact.listing_value_available', 500000)
        ->assertJsonPath('data.listings_breakdown.total_listings', 1)
        ->assertJsonPath('data.metadata.filters.category', 'Music');
});

test('filters metrics by date range', function () {
    $seller = User::factory()->create();
    $event = Event::factory()->create();

    // Listing created in January
    $oldTicket = Ticket::factory()->create(['event_id' => $event->id, 'current_owner_id' => $seller->id]);
    ResaleListing::factory()->verified()->create([
        'ticket_id' => $oldTicket->id,
        'seller_id' => $seller->id,
        'current_asking_price' => 400000,
        'created_at' => '2026-01-15 10:00:00',
    ]);

    // Listing created in June
    $newTicket = Ticket::factory()->create(['event_id' => $event->id, 'current_owner_id' => $seller->id]);
    ResaleListing::factory()->verified()->create([
        'ticket_id' => $newTicket->id,
        'seller_id' => $seller->id,
        'current_asking_price' => 600000,
        'created_at' => '2026-06-01 10:00:00',
    ]);

    $response = $this->getJson('/api/v1/dashboard/waste?start_date=2026-06-01&end_date=2026-06-30');

    $response->assertStatus(200)
        ->assertJsonPath('data.potential_impact.potentially_rescued_tickets', 1)
        ->assertJsonPath('data.potential_impact.listing_value_available', 600000)
        ->assertJsonPath('data.listings_breakdown.total_listings', 1);
});

test('returns empty metrics when no listings exist', function () {
    $response = $this->getJson('/api/v1/dashboard/waste');

    $response->assertStatus(200)
        ->assertJsonPath('data.potential_impact.potentially_rescued_tickets', 0)
        ->assertJsonPath('data.potential_impact.verified_listings', 0)
        ->assertJsonPath('data.potential_impact.active_listings', 0)
        ->assertJsonPath('data.potential_impact.listing_value_available', 0)
        ->assertJsonPath('data.listings_breakdown.total_listings', 0);
});

test('validation fails for invalid event_id format', function () {
    $response = $this->getJson('/api/v1/dashboard/waste?event_id=not-a-ulid');

    $response->assertStatus(422);
});

test('validation fails when end_date is before start_date', function () {
    $response = $this->getJson('/api/v1/dashboard/waste?start_date=2026-06-30&end_date=2026-06-01');

    $response->assertStatus(422);
});

test('rejected listings count in breakdown correctly', function () {
    ResaleListing::factory()->rejected()->create(['current_asking_price' => 150000]);
    ResaleListing::factory()->rejected()->create(['current_asking_price' => 250000]);
    ResaleListing::factory()->verified()->create(['current_asking_price' => 500000]);

    $response = $this->getJson('/api/v1/dashboard/waste');

    $response->assertStatus(200)
        ->assertJsonPath('data.listings_breakdown.rejected_listings', 2)
        ->assertJsonPath('data.listings_breakdown.total_listings', 3)
        ->assertJsonPath('data.potential_impact.potentially_rescued_tickets', 1);
});
