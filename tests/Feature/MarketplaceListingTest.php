<?php

use App\Models\Event;
use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Marketplace Listing Creation Tests ───────────────────────────────

test('seller can create listing from owned active ticket', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $ticket = Ticket::factory()
        ->withOriginalPrice(500000)
        ->create([
            'event_id' => $event->id,
            'current_owner_id' => $user->id,
            'status' => 'aktif',
        ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', [
            'ticket_id' => $ticket->id,
            'current_asking_price' => 500000,
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Marketplace listing created successfully',
        ])
        ->assertJsonPath('data.ticket_id', $ticket->id)
        ->assertJsonPath('data.seller_id', $user->id)
        ->assertJsonPath('data.verification_status', 'pending')
        ->assertJsonPath('data.listing_status', 'ditangguhkan');

    $listing = ResaleListing::first();
    expect($listing->original_price)->toEqual('500000.00');
    expect($listing->floor_price)->toEqual('225000.00');
    expect($listing->hard_cap_price)->toEqual('575000.00');
});

test('guest cannot create a listing', function () {
    $ticket = Ticket::factory()->withOriginalPrice(500000)->create();

    $response = $this->postJson('/api/v1/marketplace/listings', [
        'ticket_id' => $ticket->id,
        'current_asking_price' => 500000,
    ]);

    $response->assertStatus(401);
});

test('listing creation validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['ticket_id', 'current_asking_price']);
});

test('user cannot create listing for ticket they do not own', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $ticket = Ticket::factory()
        ->withOriginalPrice(500000)
        ->create([
            'current_owner_id' => $owner->id,
            'status' => 'aktif',
        ]);

    $response = $this->actingAs($otherUser, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', [
            'ticket_id' => $ticket->id,
            'current_asking_price' => 500000,
        ]);

    $response->assertStatus(403);
});

test('cannot create listing for non-aktif ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()
        ->withOriginalPrice(500000)
        ->create([
            'current_owner_id' => $user->id,
            'status' => 'digunakan',
        ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', [
            'ticket_id' => $ticket->id,
            'current_asking_price' => 500000,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['ticket_id']);
});

test('cannot create duplicate listing when aktif listing exists', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()
        ->withOriginalPrice(500000)
        ->create([
            'current_owner_id' => $user->id,
            'status' => 'aktif',
        ]);

    ResaleListing::factory()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $user->id,
        'listing_status' => 'aktif',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', [
            'ticket_id' => $ticket->id,
            'current_asking_price' => 500000,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['ticket_id']);
});

test('cannot create duplicate listing when ditangguhkan listing exists', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()
        ->withOriginalPrice(500000)
        ->create([
            'current_owner_id' => $user->id,
            'status' => 'aktif',
        ]);

    ResaleListing::factory()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $user->id,
        'listing_status' => 'ditangguhkan',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', [
            'ticket_id' => $ticket->id,
            'current_asking_price' => 500000,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['ticket_id']);
});

test('can create listing after previous listing was dibatalkan', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()
        ->withOriginalPrice(500000)
        ->create([
            'current_owner_id' => $user->id,
            'status' => 'aktif',
        ]);

    ResaleListing::factory()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $user->id,
        'listing_status' => 'dibatalkan',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', [
            'ticket_id' => $ticket->id,
            'current_asking_price' => 500000,
        ]);

    $response->assertStatus(201);
});

test('asking price below floor price is rejected', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()
        ->withOriginalPrice(500000)
        ->create([
            'current_owner_id' => $user->id,
            'status' => 'aktif',
        ]);

    // floor = 500000 * 0.45 = 225000
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', [
            'ticket_id' => $ticket->id,
            'current_asking_price' => 200000,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_asking_price']);
});

test('asking price above hard cap is rejected', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()
        ->withOriginalPrice(500000)
        ->create([
            'current_owner_id' => $user->id,
            'status' => 'aktif',
        ]);

    // hard_cap = 500000 * 1.15 = 575000
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', [
            'ticket_id' => $ticket->id,
            'current_asking_price' => 600000,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_asking_price']);
});

test('ticket missing original price metadata is rejected', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'current_owner_id' => $user->id,
        'status' => 'aktif',
        'ticket_metadata' => null,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', [
            'ticket_id' => $ticket->id,
            'current_asking_price' => 500000,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['ticket_id']);
});
