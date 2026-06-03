<?php

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can list all tickets with pagination', function () {
    $event = Event::factory()->create();
    $owner = User::factory()->create();
    Ticket::factory()->count(20)->create([
        'event_id' => $event->id,
        'current_owner_id' => $owner->id,
        'status' => 'aktif',
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
                    'current_owner_id',
                    'original_buyer_id',
                    'ticket_code',
                    'seat_number',
                    'ticket_proof_type',
                    'ticket_proof_path',
                    'proof_uploaded_at',
                    'status',
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

test('guest can view a single ticket', function () {
    $ticket = Ticket::factory()->create([
        'status' => 'aktif',
    ]);

    $response = $this->getJson("/api/v1/tickets/{$ticket->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Ticket retrieved successfully',
            'data' => [
                'id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code,
                'status' => 'aktif',
            ],
        ]);
});
