<?php

use App\Models\Event;
use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'seller']);
});

// ── Authentication & Authorization ──────────────────────────────────

test('guest cannot access admin listings endpoint', function () {
    $response = $this->getJson('/api/v1/admin/listings');

    $response->assertStatus(401);
});

test('non-admin user cannot access admin listings endpoint', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $response = $this->actingAs($seller, 'sanctum')
        ->getJson('/api/v1/admin/listings');

    $response->assertStatus(403);
});

// ── Browse Pending Listings ─────────────────────────────────────────

test('admin can browse pending listings', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    ResaleListing::factory()->count(3)->create([
        'verification_status' => 'pending',
        'listing_status' => 'ditangguhkan',
    ]);

    // Verified listing should NOT appear
    ResaleListing::factory()->verified()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/listings');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'original_price',
                    'current_asking_price',
                    'verification_status',
                    'listing_status',
                    'seller' => ['id', 'name', 'email'],
                    'ticket' => ['id', 'ticket_code'],
                ],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

test('admin can search pending listings by ticket code', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $ticket1 = Ticket::factory()->create(['ticket_code' => 'TCK-ALPHA-001']);
    $ticket2 = Ticket::factory()->create(['ticket_code' => 'TCK-BRAVO-002']);

    ResaleListing::factory()->create([
        'ticket_id' => $ticket1->id,
        'verification_status' => 'pending',
        'listing_status' => 'ditangguhkan',
    ]);
    ResaleListing::factory()->create([
        'ticket_id' => $ticket2->id,
        'verification_status' => 'pending',
        'listing_status' => 'ditangguhkan',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/listings?search=ALPHA');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.ticket.ticket_code', 'TCK-ALPHA-001');
});

test('admin listings pagination defaults to 15 and respects per_page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    ResaleListing::factory()->count(20)->create([
        'verification_status' => 'pending',
        'listing_status' => 'ditangguhkan',
    ]);

    // Default
    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/listings');

    $response->assertStatus(200)
        ->assertJsonCount(15, 'data')
        ->assertJsonPath('meta.per_page', 15);

    // Custom per_page
    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/listings?per_page=5');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5);
});

test('admin listings rejects invalid per_page values', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/listings?per_page=100');

    $response->assertStatus(422);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/listings?per_page=0');

    $response->assertStatus(422);
});

// ── Show Single Listing ─────────────────────────────────────────────

test('admin can view single listing details', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $listing = ResaleListing::factory()->create([
        'verification_status' => 'pending',
        'listing_status' => 'ditangguhkan',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/v1/admin/listings/{$listing->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $listing->id)
        ->assertJsonPath('data.verification_status', 'pending')
        ->assertJsonStructure([
            'data' => [
                'seller' => ['id', 'name', 'email'],
                'ticket' => ['id', 'ticket_code', 'seat_number', 'ticket_proof_path', 'ticket_proof_type', 'event'],
            ],
        ]);
});

// ── Verify Listing ──────────────────────────────────────────────────

test('admin can verify a pending listing', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $listing = ResaleListing::factory()->create([
        'verification_status' => 'pending',
        'listing_status' => 'ditangguhkan',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/admin/listings/{$listing->id}/verify");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.verification_status', 'verified')
        ->assertJsonPath('data.listing_status', 'aktif')
        ->assertJsonPath('message', 'Tiket berhasil diverifikasi');

    $listing->refresh();
    expect($listing->verification_status)->toBe('verified');
    expect($listing->listing_status)->toBe('aktif');
    expect($listing->verified_at)->not->toBeNull();
    expect($listing->verified_by)->toBe($admin->id);
    expect($listing->rejection_reason)->toBeNull();
});

test('admin cannot verify an already verified listing', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $listing = ResaleListing::factory()->verified()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/admin/listings/{$listing->id}/verify");

    $response->assertStatus(403);
});

test('admin cannot verify an already rejected listing', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $listing = ResaleListing::factory()->rejected()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/admin/listings/{$listing->id}/verify");

    $response->assertStatus(403);
});

test('guest cannot verify a listing', function () {
    $listing = ResaleListing::factory()->create([
        'verification_status' => 'pending',
    ]);

    $response = $this->postJson("/api/v1/admin/listings/{$listing->id}/verify");

    $response->assertStatus(401);
});

test('non-admin cannot verify a listing', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $listing = ResaleListing::factory()->create([
        'verification_status' => 'pending',
    ]);

    $response = $this->actingAs($seller, 'sanctum')
        ->postJson("/api/v1/admin/listings/{$listing->id}/verify");

    $response->assertStatus(403);
});

// ── Reject Listing ──────────────────────────────────────────────────

test('admin can reject a pending listing with reason', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $listing = ResaleListing::factory()->create([
        'verification_status' => 'pending',
        'listing_status' => 'ditangguhkan',
    ]);

    $reason = 'The uploaded ticket proof image is blurry and unreadable.';

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/admin/listings/{$listing->id}/reject", [
            'rejection_reason' => $reason,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.verification_status', 'rejected')
        ->assertJsonPath('data.listing_status', 'ditolak')
        ->assertJsonPath('data.rejection_reason', $reason)
        ->assertJsonPath('message', 'Tiket berhasil ditolak');

    $listing->refresh();
    expect($listing->verification_status)->toBe('rejected');
    expect($listing->listing_status)->toBe('ditolak');
    expect($listing->verified_by)->toBe($admin->id);
    expect($listing->rejection_reason)->toBe($reason);
});

test('reject fails without rejection_reason', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $listing = ResaleListing::factory()->create([
        'verification_status' => 'pending',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/admin/listings/{$listing->id}/reject", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['rejection_reason']);
});

test('reject fails when rejection_reason is too short', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $listing = ResaleListing::factory()->create([
        'verification_status' => 'pending',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/admin/listings/{$listing->id}/reject", [
            'rejection_reason' => 'Bad',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['rejection_reason']);
});

test('admin cannot reject an already verified listing', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $listing = ResaleListing::factory()->verified()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/admin/listings/{$listing->id}/reject", [
            'rejection_reason' => 'This should not be allowed because it is already verified.',
        ]);

    $response->assertStatus(403);
});

test('admin cannot reject an already rejected listing', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $listing = ResaleListing::factory()->rejected()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/admin/listings/{$listing->id}/reject", [
            'rejection_reason' => 'This should not be allowed because it is already rejected.',
        ]);

    $response->assertStatus(403);
});

test('guest cannot reject a listing', function () {
    $listing = ResaleListing::factory()->create([
        'verification_status' => 'pending',
    ]);

    $response = $this->postJson("/api/v1/admin/listings/{$listing->id}/reject", [
        'rejection_reason' => 'Guest should not be able to reject listings at all.',
    ]);

    $response->assertStatus(401);
});

test('non-admin cannot reject a listing', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $listing = ResaleListing::factory()->create([
        'verification_status' => 'pending',
    ]);

    $response = $this->actingAs($seller, 'sanctum')
        ->postJson("/api/v1/admin/listings/{$listing->id}/reject", [
            'rejection_reason' => 'Seller should not be able to reject listings at all.',
        ]);

    $response->assertStatus(403);
});

// ── Re-listing After Rejection ──────────────────────────────────────

test('seller can create new listing after previous listing was rejected', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $ticket = Ticket::factory()
        ->withOriginalPrice(500000)
        ->create([
            'event_id' => $event->id,
            'current_owner_id' => $user->id,
            'status' => 'aktif',
        ]);

    // Simulate a rejected listing (listing_status = ditolak)
    ResaleListing::factory()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $user->id,
        'verification_status' => 'rejected',
        'listing_status' => 'ditolak',
        'rejection_reason' => 'Blurry proof image.',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/marketplace/listings', [
            'ticket_id' => $ticket->id,
            'current_asking_price' => 500000,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.verification_status', 'pending')
        ->assertJsonPath('data.listing_status', 'ditangguhkan');

    // Two listings should exist for same ticket
    expect(ResaleListing::where('ticket_id', $ticket->id)->count())->toBe(2);
});
