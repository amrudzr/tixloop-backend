<?php

use App\Models\Event;
use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

test('guest can browse active and verified listings', function () {
    $seller = User::factory()->create();
    $event = Event::factory()->create();
    $ticket = Ticket::factory()->create([
        'event_id' => $event->id,
        'current_owner_id' => $seller->id,
        'status' => 'aktif',
    ]);

    $listing = ResaleListing::factory()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $seller->id,
        'listing_status' => 'aktif',
        'verification_status' => 'verified',
        'current_asking_price' => 500000,
    ]);

    $response = $this->getJson('/api/v1/marketplace/listings');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $listing->id)
        ->assertJsonPath('data.0.seller.id', $seller->id)
        ->assertJsonPath('data.0.seller.name', $seller->name)
        ->assertJson(function (AssertableJson $json) {
            $json->has('data.0')
                ->missing('data.0.seller.email')
                ->missing('data.0.original_price')
                ->etc();
        });
});

test('suspended or pending listings are omitted', function () {
    ResaleListing::factory()->create([
        'listing_status' => 'ditangguhkan',
        'verification_status' => 'pending',
    ]);
    ResaleListing::factory()->create([
        'listing_status' => 'aktif',
        'verification_status' => 'pending',
    ]);
    ResaleListing::factory()->create([
        'listing_status' => 'ditangguhkan',
        'verification_status' => 'verified',
    ]);

    $response = $this->getJson('/api/v1/marketplace/listings');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

test('pagination is enforced with max 50', function () {
    $seller = User::factory()->create();

    ResaleListing::factory()->count(55)->create([
        'seller_id' => $seller->id,
        'listing_status' => 'aktif',
        'verification_status' => 'verified',
    ]);

    $response = $this->getJson('/api/v1/marketplace/listings?per_page=50');

    $response->assertStatus(200)
        ->assertJsonCount(50, 'data')
        ->assertJsonPath('meta.per_page', 50);

    $responseExceed = $this->getJson('/api/v1/marketplace/listings?per_page=100');
    $responseExceed->assertStatus(422);
});

test('search filters by event name, venue, city, or category', function () {
    $event1 = Event::factory()->create(['event_name' => 'Coldplay Concert', 'city' => 'Jakarta']);
    $event2 = Event::factory()->create(['event_name' => 'Jazz Festival', 'city' => 'Bandung']);

    $ticket1 = Ticket::factory()->create(['event_id' => $event1->id]);
    $ticket2 = Ticket::factory()->create(['event_id' => $event2->id]);

    ResaleListing::factory()->create(['ticket_id' => $ticket1->id, 'listing_status' => 'aktif', 'verification_status' => 'verified']);
    ResaleListing::factory()->create(['ticket_id' => $ticket2->id, 'listing_status' => 'aktif', 'verification_status' => 'verified']);

    $response = $this->getJson('/api/v1/marketplace/listings?search=Coldplay');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.ticket.event.name', 'Coldplay Concert');
});

test('guest can view marketplace listing details', function () {
    $listing = ResaleListing::factory()->create([
        'listing_status' => 'aktif',
        'verification_status' => 'verified',
        'original_price' => 500000,
        'floor_price' => 225000,
        'hard_cap_price' => 575000,
    ]);

    $response = $this->getJson("/api/v1/marketplace/listings/{$listing->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $listing->id)
        ->assertJsonPath('data.verification_status', 'verified')
        ->assertJsonPath('data.listing_status', 'aktif')
        ->assertJson(function (AssertableJson $json) {
            $json->has('data.original_price')
                ->has('data.floor_price')
                ->has('data.hard_cap_price')
                ->missing('data.seller.email')
                ->etc();
        });
});

test('show endpoint fails for non-active listings', function () {
    $listing = ResaleListing::factory()->create([
        'listing_status' => 'ditangguhkan',
        'verification_status' => 'pending',
    ]);

    $response = $this->getJson("/api/v1/marketplace/listings/{$listing->id}");

    $response->assertStatus(404);
});
