<?php

use App\Models\Event;
use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Authentication ──────────────────────────────────────────────────

test('guest cannot access seller listings', function () {
    $response = $this->getJson('/api/v1/marketplace/my-listings');

    $response->assertStatus(401);
});

// ── Retrieval ───────────────────────────────────────────────────────

test('seller retrieves own listings across all statuses', function () {
    $seller = User::factory()->create();
    $event = Event::factory()->create();

    $ticket1 = Ticket::factory()->create(['event_id' => $event->id, 'current_owner_id' => $seller->id]);
    $ticket2 = Ticket::factory()->create(['event_id' => $event->id, 'current_owner_id' => $seller->id]);
    $ticket3 = Ticket::factory()->create(['event_id' => $event->id, 'current_owner_id' => $seller->id]);
    $ticket4 = Ticket::factory()->create(['event_id' => $event->id, 'current_owner_id' => $seller->id]);

    ResaleListing::factory()->pending()->create(['ticket_id' => $ticket1->id, 'seller_id' => $seller->id]);
    ResaleListing::factory()->verified()->create(['ticket_id' => $ticket2->id, 'seller_id' => $seller->id]);
    ResaleListing::factory()->rejected()->create(['ticket_id' => $ticket3->id, 'seller_id' => $seller->id]);
    ResaleListing::factory()->sold()->create(['ticket_id' => $ticket4->id, 'seller_id' => $seller->id]);

    $response = $this->actingAs($seller, 'sanctum')
        ->getJson('/api/v1/marketplace/my-listings');

    $response->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonCount(4, 'data');
});

test('seller does not see other sellers listings', function () {
    $seller = User::factory()->create();
    $otherSeller = User::factory()->create();

    $ticket1 = Ticket::factory()->create(['current_owner_id' => $seller->id]);
    $ticket2 = Ticket::factory()->create(['current_owner_id' => $otherSeller->id]);

    ResaleListing::factory()->verified()->create(['ticket_id' => $ticket1->id, 'seller_id' => $seller->id]);
    ResaleListing::factory()->verified()->create(['ticket_id' => $ticket2->id, 'seller_id' => $otherSeller->id]);

    $response = $this->actingAs($seller, 'sanctum')
        ->getJson('/api/v1/marketplace/my-listings');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.ticket.id', $ticket1->id);
});

// ── Status Filter ───────────────────────────────────────────────────

test('seller can filter listings by status', function () {
    $seller = User::factory()->create();

    $ticket1 = Ticket::factory()->create(['current_owner_id' => $seller->id]);
    $ticket2 = Ticket::factory()->create(['current_owner_id' => $seller->id]);

    ResaleListing::factory()->verified()->create(['ticket_id' => $ticket1->id, 'seller_id' => $seller->id]);
    ResaleListing::factory()->rejected()->create(['ticket_id' => $ticket2->id, 'seller_id' => $seller->id]);

    $response = $this->actingAs($seller, 'sanctum')
        ->getJson('/api/v1/marketplace/my-listings?status=ditolak');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.listing_status', 'ditolak');
});

// ── Rejection Reason ────────────────────────────────────────────────

test('rejected listing includes rejection reason', function () {
    $seller = User::factory()->create();
    $ticket = Ticket::factory()->create(['current_owner_id' => $seller->id]);

    ResaleListing::factory()->rejected()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $seller->id,
        'rejection_reason' => 'Bukti tiket tidak terbaca.',
    ]);

    $response = $this->actingAs($seller, 'sanctum')
        ->getJson('/api/v1/marketplace/my-listings');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.rejection_reason', 'Bukti tiket tidak terbaca.')
        ->assertJsonPath('data.0.verification_status', 'rejected');
});

test('non-rejected listing does not expose rejection reason', function () {
    $seller = User::factory()->create();
    $ticket = Ticket::factory()->create(['current_owner_id' => $seller->id]);

    ResaleListing::factory()->verified()->create(['ticket_id' => $ticket->id, 'seller_id' => $seller->id]);

    $response = $this->actingAs($seller, 'sanctum')
        ->getJson('/api/v1/marketplace/my-listings');

    $response->assertStatus(200);
    $this->assertArrayNotHasKey('rejection_reason', $response->json('data.0'));
});

// ── Event & Ticket Data ─────────────────────────────────────────────

test('seller listing includes ticket and event details', function () {
    $seller = User::factory()->create();
    $event = Event::factory()->create(['event_name' => 'Konser Dewa 19']);
    $ticket = Ticket::factory()->create([
        'event_id' => $event->id,
        'current_owner_id' => $seller->id,
        'seat_number' => 'A12',
    ]);

    ResaleListing::factory()->verified()->create(['ticket_id' => $ticket->id, 'seller_id' => $seller->id]);

    $response = $this->actingAs($seller, 'sanctum')
        ->getJson('/api/v1/marketplace/my-listings');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.ticket.seat_number', 'A12')
        ->assertJsonPath('data.0.ticket.event.name', 'Konser Dewa 19');
});

// ── Pagination ──────────────────────────────────────────────────────

test('seller listings are paginated with meta', function () {
    $seller = User::factory()->create();

    for ($i = 0; $i < 3; $i++) {
        $ticket = Ticket::factory()->create(['current_owner_id' => $seller->id]);
        ResaleListing::factory()->verified()->create(['ticket_id' => $ticket->id, 'seller_id' => $seller->id]);
    }

    $response = $this->actingAs($seller, 'sanctum')
        ->getJson('/api/v1/marketplace/my-listings?per_page=2');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.last_page', 2);
});

test('per page is capped at 50', function () {
    $seller = User::factory()->create();
    $ticket = Ticket::factory()->create(['current_owner_id' => $seller->id]);
    ResaleListing::factory()->verified()->create(['ticket_id' => $ticket->id, 'seller_id' => $seller->id]);

    $response = $this->actingAs($seller, 'sanctum')
        ->getJson('/api/v1/marketplace/my-listings?per_page=100');

    $response->assertStatus(200)
        ->assertJsonPath('meta.per_page', 50);
});
