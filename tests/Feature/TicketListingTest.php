<?php

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can list all tickets with pagination', function () {
    $event = Event::factory()->create();
    $seller = User::factory()->create();
    Ticket::factory()->count(20)->create([
        'event_id' => $event->id,
        'seller_id' => $seller->id,
    ]);

    $response = $this->getJson('/api/v1/tickets');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Tickets retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'event_id',
                    'seller_id',
                    'ticket_type',
                    'seat_number',
                    'price',
                    'is_verified',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);

    // Assert pagination (max 15 items per page)
    expect($response->json('data'))->toHaveCount(15);
});

test('can filter tickets by search query on event name', function () {
    $event1 = Event::factory()->create(['event_name' => 'Coldplay Concert']);
    $event2 = Event::factory()->create(['event_name' => 'Taylor Swift Concert']);

    $ticket1 = Ticket::factory()->create(['event_id' => $event1->id]);
    $ticket2 = Ticket::factory()->create(['event_id' => $event2->id]);

    $response = $this->getJson('/api/v1/tickets?search=Coldplay');

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['id'])->toBe($ticket1->id);
});

test('can filter tickets by min and max price', function () {
    $ticketLow = Ticket::factory()->create(['price' => 100000]);
    $ticketMid = Ticket::factory()->create(['price' => 500000]);
    $ticketHigh = Ticket::factory()->create(['price' => 1000000]);

    $response = $this->getJson('/api/v1/tickets?min_price=200000&max_price=800000');

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['id'])->toBe($ticketMid->id);
});

test('can filter tickets by verification status', function () {
    $verifiedTicket = Ticket::factory()->create(['is_verified' => true]);
    $unverifiedTicket = Ticket::factory()->create(['is_verified' => false]);

    // Test verified only
    $responseVerified = $this->getJson('/api/v1/tickets?verified=true');
    $responseVerified->assertStatus(200);
    $dataVerified = $responseVerified->json('data');
    expect($dataVerified)->toHaveCount(1);
    expect($dataVerified[0]['id'])->toBe($verifiedTicket->id);

    // Test unverified only
    $responseUnverified = $this->getJson('/api/v1/tickets?verified=false');
    $responseUnverified->assertStatus(200);
    $dataUnverified = $responseUnverified->json('data');
    expect($dataUnverified)->toHaveCount(1);
    expect($dataUnverified[0]['id'])->toBe($unverifiedTicket->id);
});

test('guest can view a single ticket', function () {
    $ticket = Ticket::factory()->create();

    $response = $this->getJson("/api/v1/tickets/{$ticket->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Ticket retrieved successfully',
            'data' => [
                'id' => $ticket->id,
                'ticket_type' => $ticket->ticket_type,
                'price' => $ticket->price,
            ],
        ]);
});

test('authenticated user can list a ticket', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $ticketData = [
        'event_id' => $event->id,
        'ticket_type' => 'VIP Box',
        'seat_number' => 'A-1',
        'price' => 750000,
        'is_verified' => false,
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets', $ticketData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Ticket listed successfully',
            'data' => [
                'event_id' => $event->id,
                'seller_id' => $user->id,
                'ticket_type' => 'VIP Box',
                'seat_number' => 'A-1',
                'price' => 750000,
                'is_verified' => false,
            ],
        ]);

    $this->assertDatabaseHas('tickets', [
        'event_id' => $event->id,
        'seller_id' => $user->id,
        'ticket_type' => 'VIP Box',
        'price' => 750000,
    ]);
});

test('guest cannot list a ticket', function () {
    $event = Event::factory()->create();

    $ticketData = [
        'event_id' => $event->id,
        'ticket_type' => 'VIP Box',
        'seat_number' => 'A-1',
        'price' => 750000,
    ];

    $response = $this->postJson('/api/v1/tickets', $ticketData);

    $response->assertStatus(401);
});

test('ticket creation validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['event_id', 'ticket_type', 'price']);
});
