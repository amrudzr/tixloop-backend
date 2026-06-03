<?php

use App\Models\Event;
use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\User;
use App\Services\BurnPrevention\BurnPreventionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helper: create listing with specific event time
|--------------------------------------------------------------------------
*/

/**
 * @param  array<string, mixed>  $eventOverrides
 * @param  array<string, mixed>  $listingOverrides
 */
function createListingForEvent(array $eventOverrides = [], array $listingOverrides = []): ResaleListing
{
    $seller = User::factory()->create();
    $event = Event::factory()->create($eventOverrides);
    $ticket = Ticket::factory()->create([
        'event_id' => $event->id,
        'current_owner_id' => $seller->id,
        'status' => 'aktif',
    ]);

    return ResaleListing::factory()->create(array_merge([
        'ticket_id' => $ticket->id,
        'seller_id' => $seller->id,
        'original_price' => 100000,
        'current_asking_price' => 80000,
        'floor_price' => 45000,
        'hard_cap_price' => 115000,
        'verification_status' => 'verified',
        'listing_status' => 'aktif',
    ], $listingOverrides));
}

/*
|--------------------------------------------------------------------------
| Risk Classification Tests
|--------------------------------------------------------------------------
*/

test('classifies listing as low risk when TTE > 72 hours', function () {
    $listing = createListingForEvent(
        ['event_datetime' => now()->addHours(120)],
    );

    $service = app(BurnPreventionService::class);
    $assessment = $service->assessListing($listing);

    expect($assessment['risk_level'])->toBe('low')
        ->and($assessment['time_to_event_hours'])->toBeGreaterThan(72);
});

test('classifies listing as medium risk when 24 < TTE <= 72 hours', function () {
    $listing = createListingForEvent(
        ['event_datetime' => now()->addHours(48)],
    );

    $service = app(BurnPreventionService::class);
    $assessment = $service->assessListing($listing);

    expect($assessment['risk_level'])->toBe('medium')
        ->and($assessment['time_to_event_hours'])->toBeGreaterThan(24)
        ->and($assessment['time_to_event_hours'])->toBeLessThanOrEqual(72);
});

test('classifies listing as high risk when TTE <= 24 hours', function () {
    $listing = createListingForEvent(
        ['event_datetime' => now()->addHours(12)],
    );

    $service = app(BurnPreventionService::class);
    $assessment = $service->assessListing($listing);

    expect($assessment['risk_level'])->toBe('high')
        ->and($assessment['time_to_event_hours'])->toBeLessThanOrEqual(24);
});

test('classifies pending listing as high risk when TTE <= 48 hours', function () {
    $listing = createListingForEvent(
        ['event_datetime' => now()->addHours(36)],
        ['verification_status' => 'pending', 'listing_status' => 'ditangguhkan'],
    );

    $service = app(BurnPreventionService::class);
    $assessment = $service->assessListing($listing);

    expect($assessment['risk_level'])->toBe('high');
});

test('classifies expired event listing correctly', function () {
    $listing = createListingForEvent(
        ['event_datetime' => now()->subHours(2)],
    );

    $service = app(BurnPreventionService::class);
    $assessment = $service->assessListing($listing);

    expect($assessment['risk_level'])->toBe('expired')
        ->and($assessment['time_to_event_hours'])->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Dashboard Metrics Tests
|--------------------------------------------------------------------------
*/

test('dashboard returns correct risk counts', function () {
    // 2 high-risk (event in 12 hours)
    createListingForEvent(['event_datetime' => now()->addHours(12)]);
    createListingForEvent(['event_datetime' => now()->addHours(10)]);

    // 1 medium-risk (event in 48 hours)
    createListingForEvent(['event_datetime' => now()->addHours(48)]);

    // 1 low-risk (event in 120 hours)
    createListingForEvent(['event_datetime' => now()->addHours(120)]);

    $response = $this->getJson('/api/v1/dashboard/burn-prevention');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.summary.high_risk_count', 2)
        ->assertJsonPath('data.summary.medium_risk_count', 1)
        ->assertJsonPath('data.summary.low_risk_count', 1);
});

test('dashboard calculates total value at risk from high and medium risk listings', function () {
    // High-risk: 80000
    createListingForEvent(
        ['event_datetime' => now()->addHours(12)],
        ['current_asking_price' => 80000],
    );

    // Medium-risk: 60000
    createListingForEvent(
        ['event_datetime' => now()->addHours(48)],
        ['current_asking_price' => 60000],
    );

    // Low-risk: 50000 (should NOT be included in total_value_at_risk)
    createListingForEvent(
        ['event_datetime' => now()->addHours(120)],
        ['current_asking_price' => 50000],
    );

    $response = $this->getJson('/api/v1/dashboard/burn-prevention');

    $response->assertStatus(200)
        ->assertJsonPath('data.summary.total_value_at_risk', 140000);
});

test('dashboard counts historical wasted tickets', function () {
    $seller = User::factory()->create();

    // Past event, ticket aktif, never used = wasted
    $pastEvent = Event::factory()->create(['event_datetime' => now()->subDays(3)]);
    Ticket::factory()->create([
        'event_id' => $pastEvent->id,
        'current_owner_id' => $seller->id,
        'status' => 'aktif',
        'used_at' => null,
    ]);

    // Past event, ticket used = NOT wasted
    Ticket::factory()->create([
        'event_id' => $pastEvent->id,
        'current_owner_id' => $seller->id,
        'status' => 'aktif',
        'used_at' => now()->subDays(3),
    ]);

    // Future event, ticket aktif = NOT wasted
    $futureEvent = Event::factory()->create(['event_datetime' => now()->addDays(5)]);
    Ticket::factory()->create([
        'event_id' => $futureEvent->id,
        'current_owner_id' => $seller->id,
        'status' => 'aktif',
        'used_at' => null,
    ]);

    $response = $this->getJson('/api/v1/dashboard/burn-prevention');

    $response->assertStatus(200)
        ->assertJsonPath('data.summary.historical_wasted_tickets', 1);
});

test('dashboard returns empty metrics when no data exists', function () {
    $response = $this->getJson('/api/v1/dashboard/burn-prevention');

    $response->assertStatus(200)
        ->assertJsonPath('data.summary.total_value_at_risk', 0)
        ->assertJsonPath('data.summary.high_risk_count', 0)
        ->assertJsonPath('data.summary.medium_risk_count', 0)
        ->assertJsonPath('data.summary.low_risk_count', 0)
        ->assertJsonPath('data.summary.historical_wasted_tickets', 0);
});

test('dashboard filters by event_id', function () {
    $targetEvent = Event::factory()->create(['event_datetime' => now()->addHours(12)]);
    $otherEvent = Event::factory()->create(['event_datetime' => now()->addHours(12)]);

    $seller = User::factory()->create();

    $targetTicket = Ticket::factory()->create([
        'event_id' => $targetEvent->id,
        'current_owner_id' => $seller->id,
    ]);
    $otherTicket = Ticket::factory()->create([
        'event_id' => $otherEvent->id,
        'current_owner_id' => $seller->id,
    ]);

    ResaleListing::factory()->create([
        'ticket_id' => $targetTicket->id,
        'seller_id' => $seller->id,
        'verification_status' => 'verified',
        'listing_status' => 'aktif',
        'current_asking_price' => 70000,
    ]);
    ResaleListing::factory()->create([
        'ticket_id' => $otherTicket->id,
        'seller_id' => $seller->id,
        'verification_status' => 'verified',
        'listing_status' => 'aktif',
        'current_asking_price' => 90000,
    ]);

    $response = $this->getJson("/api/v1/dashboard/burn-prevention?event_id={$targetEvent->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.summary.high_risk_count', 1)
        ->assertJsonPath('data.metadata.filters.event_id', $targetEvent->id);
});

/*
|--------------------------------------------------------------------------
| Recommendation Tests
|--------------------------------------------------------------------------
*/

test('recommends price drop when listing is high risk and not at floor', function () {
    $listing = createListingForEvent(
        ['event_datetime' => now()->addHours(10)],
        ['current_asking_price' => 80000, 'floor_price' => 45000],
    );

    $service = app(BurnPreventionService::class);
    $assessment = $service->assessListing($listing);

    expect($assessment['risk_level'])->toBe('high')
        ->and($assessment['is_at_floor'])->toBeFalse()
        ->and($assessment['recommendation'])->toContain('45000');
});

test('recommends monitoring when listing is already at floor price', function () {
    $listing = createListingForEvent(
        ['event_datetime' => now()->addHours(10)],
        ['current_asking_price' => 45000, 'floor_price' => 45000],
    );

    $service = app(BurnPreventionService::class);
    $assessment = $service->assessListing($listing);

    expect($assessment['risk_level'])->toBe('high')
        ->and($assessment['is_at_floor'])->toBeTrue()
        ->and($assessment['recommendation'])->toContain('Maximum rescue visibility');
});

test('recommends monitoring for low risk listings', function () {
    $listing = createListingForEvent(
        ['event_datetime' => now()->addHours(120)],
    );

    $service = app(BurnPreventionService::class);
    $assessment = $service->assessListing($listing);

    expect($assessment['risk_level'])->toBe('low')
        ->and($assessment['recommendation'])->toContain('Monitor market activity');
});

/*
|--------------------------------------------------------------------------
| Validation Tests
|--------------------------------------------------------------------------
*/

test('validation fails for invalid event_id format', function () {
    $response = $this->getJson('/api/v1/dashboard/burn-prevention?event_id=not-a-ulid');

    $response->assertStatus(422);
});

/*
|--------------------------------------------------------------------------
| Response Structure Tests
|--------------------------------------------------------------------------
*/

test('response structure contains expected keys', function () {
    createListingForEvent(['event_datetime' => now()->addHours(12)]);

    $response = $this->getJson('/api/v1/dashboard/burn-prevention');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'summary' => [
                    'total_value_at_risk',
                    'high_risk_count',
                    'medium_risk_count',
                    'low_risk_count',
                    'historical_wasted_tickets',
                ],
                'user_listings_at_risk',
                'metadata' => [
                    'filters' => ['event_id'],
                    'calculated_at',
                ],
            ],
        ]);
});

test('pending high-risk listings counted in dashboard metrics', function () {
    // Pending listing for event in 36 hours = high risk
    createListingForEvent(
        ['event_datetime' => now()->addHours(36)],
        ['verification_status' => 'pending', 'listing_status' => 'ditangguhkan', 'current_asking_price' => 55000],
    );

    $response = $this->getJson('/api/v1/dashboard/burn-prevention');

    $response->assertStatus(200)
        ->assertJsonPath('data.summary.high_risk_count', 1)
        ->assertJsonPath('data.summary.total_value_at_risk', 55000);
});

test('guest request returns public metrics with empty user_listings_at_risk', function () {
    $response = $this->getJson('/api/v1/dashboard/burn-prevention');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user_listings_at_risk', []);
});

test('authenticated seller receives their own listings at risk in user_listings_at_risk', function () {
    $seller = User::factory()->create();

    // Create an at-risk listing for this seller (event in 12 hours)
    $event = Event::factory()->create(['event_datetime' => now()->addHours(12)]);
    $ticket = Ticket::factory()->create([
        'event_id' => $event->id,
        'current_owner_id' => $seller->id,
        'status' => 'aktif',
    ]);
    $listing = ResaleListing::factory()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $seller->id,
        'current_asking_price' => 80000,
        'floor_price' => 45000,
        'verification_status' => 'verified',
        'listing_status' => 'aktif',
    ]);

    // Create an at-risk listing for another seller
    $otherSeller = User::factory()->create();
    $otherEvent = Event::factory()->create(['event_datetime' => now()->addHours(12)]);
    $otherTicket = Ticket::factory()->create([
        'event_id' => $otherEvent->id,
        'current_owner_id' => $otherSeller->id,
        'status' => 'aktif',
    ]);
    ResaleListing::factory()->create([
        'ticket_id' => $otherTicket->id,
        'seller_id' => $otherSeller->id,
        'current_asking_price' => 90000,
        'floor_price' => 45000,
        'verification_status' => 'verified',
        'listing_status' => 'aktif',
    ]);

    $response = $this->actingAs($seller, 'sanctum')->getJson('/api/v1/dashboard/burn-prevention');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $userListings = $response->json('data.user_listings_at_risk');
    expect($userListings)->toHaveCount(1);
    expect($userListings[0]['listing_id'])->toBe($listing->id);
});
