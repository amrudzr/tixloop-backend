<?php

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Event Listing Tests ──────────────────────────────────────────────

test('guest can list events', function () {
    Event::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/events');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Events retrieved successfully',
        ]);

    expect($response->json('data'))->toHaveCount(3);
});

test('events list is paginated', function () {
    Event::factory()->count(20)->create();

    $response = $this->getJson('/api/v1/events?per_page=5');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(5);
    expect($response->json('meta.per_page'))->toBe(5);
    expect($response->json('meta.total'))->toBe(20);
});

test('events list per_page capped at 50', function () {
    Event::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/events?per_page=999');

    $response->assertStatus(200);
    expect($response->json('meta.per_page'))->toBe(50);
});

test('can filter events by category', function () {
    Event::factory()->create(['event_category' => 'Music']);
    Event::factory()->create(['event_category' => 'Sports']);
    Event::factory()->create(['event_category' => 'Music']);

    $response = $this->getJson('/api/v1/events?category=Music');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(2);
});

test('can filter events by city', function () {
    Event::factory()->create(['city' => 'Jakarta']);
    Event::factory()->create(['city' => 'Bandung']);
    Event::factory()->create(['city' => 'Jakarta']);

    $response = $this->getJson('/api/v1/events?city=Jakarta');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(2);
});

test('can filter events by search on event name', function () {
    Event::factory()->create(['event_name' => 'Coldplay World Tour']);
    Event::factory()->create(['event_name' => 'Taylor Swift Eras']);

    $response = $this->getJson('/api/v1/events?search=Coldplay');

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['event_name'])->toBe('Coldplay World Tour');
});

test('search matches venue name and city', function () {
    Event::factory()->create(['venue_name' => 'Gelora Bung Karno', 'city' => 'Jakarta']);
    Event::factory()->create(['venue_name' => 'Trans Studio', 'city' => 'Bandung']);

    $response = $this->getJson('/api/v1/events?search=Gelora');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
});

test('guest can view single event', function () {
    $event = Event::factory()->create([
        'event_name' => 'Test Concert',
        'event_category' => 'Music',
    ]);

    $response = $this->getJson("/api/v1/events/{$event->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Event retrieved successfully',
            'data' => [
                'id' => $event->id,
                'event_name' => 'Test Concert',
                'event_category' => 'Music',
            ],
        ]);
});

test('show returns 404 for invalid event id', function () {
    $response = $this->getJson('/api/v1/events/nonexistent-id');

    $response->assertStatus(404);
});

test('events ordered by event_datetime descending', function () {
    $old = Event::factory()->create(['event_datetime' => now()->addDays(1)]);
    $new = Event::factory()->create(['event_datetime' => now()->addDays(30)]);

    $response = $this->getJson('/api/v1/events');

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data[0]['id'])->toBe($new->id);
    expect($data[1]['id'])->toBe($old->id);
});

test('event resource returns all expected fields', function () {
    $event = Event::factory()->create();

    $response = $this->getJson("/api/v1/events/{$event->id}");

    $response->assertStatus(200);
    $data = $response->json('data');

    expect($data)->toHaveKeys([
        'id',
        'event_name',
        'event_category',
        'event_datetime',
        'venue_name',
        'city',
        'event_poster_url',
    ]);
});

test('empty events list returns empty data array', function () {
    $response = $this->getJson('/api/v1/events');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [],
        ]);

    expect($response->json('meta.total'))->toBe(0);
});
