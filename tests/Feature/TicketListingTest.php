<?php

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('admin');
    Role::findOrCreate('seller');
});

test('guest cannot list tickets', function () {
    $response = $this->getJson('/api/v1/tickets');

    $response->assertStatus(401);
});

test('guest cannot view a single ticket', function () {
    $ticket = Ticket::factory()->create([
        'status' => 'aktif',
    ]);

    $response = $this->getJson("/api/v1/tickets/{$ticket->id}");

    $response->assertStatus(401);
});

test('authenticated owner can list their own tickets with pagination', function () {
    $event = Event::factory()->create();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create 10 tickets for owner
    Ticket::factory()->count(10)->create([
        'event_id' => $event->id,
        'current_owner_id' => $owner->id,
        'status' => 'aktif',
    ]);

    // Create 5 tickets for other user
    Ticket::factory()->count(5)->create([
        'event_id' => $event->id,
        'current_owner_id' => $otherUser->id,
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/v1/tickets');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Daftar tiket berhasil diambil',
        ]);

    // Assert only owner's tickets are returned (10 tickets)
    expect($response->json('data'))->toHaveCount(10);
});

test('admin can list all tickets', function () {
    $event = Event::factory()->create();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Ticket::factory()->count(10)->create([
        'event_id' => $event->id,
        'current_owner_id' => $owner->id,
        'status' => 'aktif',
    ]);

    Ticket::factory()->count(5)->create([
        'event_id' => $event->id,
        'current_owner_id' => $otherUser->id,
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/tickets');

    $response->assertStatus(200);
    // Admin sees all 15 tickets
    expect($response->json('data'))->toHaveCount(15);
});

test('owner can view their single ticket with ticket_code', function () {
    $owner = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'current_owner_id' => $owner->id,
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($owner, 'sanctum')->getJson("/api/v1/tickets/{$ticket->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Data tiket berhasil diambil',
            'data' => [
                'id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code,
                'status' => 'aktif',
            ],
        ]);
});

test('admin can view any ticket with ticket_code', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $ticket = Ticket::factory()->create([
        'current_owner_id' => $owner->id,
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($admin, 'sanctum')->getJson("/api/v1/tickets/{$ticket->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Data tiket berhasil diambil',
            'data' => [
                'id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code,
                'status' => 'aktif',
            ],
        ]);
});

test('non-owner cannot view a ticket', function () {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'current_owner_id' => $owner->id,
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($nonOwner, 'sanctum')->getJson("/api/v1/tickets/{$ticket->id}");

    $response->assertStatus(403);
});

test('can filter owner tickets by search query on event name', function () {
    $owner = User::factory()->create();
    $event1 = Event::factory()->create(['event_name' => 'Coldplay Concert']);
    $event2 = Event::factory()->create(['event_name' => 'Taylor Swift Concert']);

    $ticket1 = Ticket::factory()->create(['event_id' => $event1->id, 'current_owner_id' => $owner->id]);
    $ticket2 = Ticket::factory()->create(['event_id' => $event2->id, 'current_owner_id' => $owner->id]);

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/v1/tickets?search=Coldplay');

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['id'])->toBe($ticket1->id);
});
