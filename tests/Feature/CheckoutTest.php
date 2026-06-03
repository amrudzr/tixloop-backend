<?php

use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Helper ──────────────────────────────────────────────────────────

/**
 * Create a verified, active listing ready for checkout.
 *
 * @return array{listing: ResaleListing, seller: User, ticket: Ticket}
 */
function createPurchasableListing(float $price = 500000): array
{
    $seller = User::factory()->create();
    $ticket = Ticket::factory()->withOriginalPrice($price)->create([
        'current_owner_id' => $seller->id,
        'status' => 'aktif',
    ]);
    $listing = ResaleListing::factory()->verified()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $seller->id,
        'current_asking_price' => $price,
    ]);

    return compact('listing', 'seller', 'ticket');
}

// ── Authentication ──────────────────────────────────────────────────

test('guest cannot initiate checkout', function () {
    ['listing' => $listing] = createPurchasableListing();

    $response = $this->postJson("/api/v1/marketplace/listings/{$listing->id}/checkout");

    $response->assertStatus(401);
});

// ── Successful Checkout ─────────────────────────────────────────────

test('buyer can successfully initiate checkout on verified listing', function () {
    ['listing' => $listing, 'seller' => $seller] = createPurchasableListing();
    $buyer = User::factory()->create();

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/marketplace/listings/{$listing->id}/checkout");

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.escrow_status', null)
        ->assertJsonPath('data.buyer.id', $buyer->id)
        ->assertJsonPath('data.seller.id', $seller->id);

    $this->assertDatabaseHas('transactions', [
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'resale_listing_id' => $listing->id,
        'status' => 'pending',
    ]);
});

// ── Seller Cannot Buy Own Listing ───────────────────────────────────

test('seller cannot purchase their own listing', function () {
    ['listing' => $listing, 'seller' => $seller] = createPurchasableListing();

    $response = $this->actingAs($seller, 'sanctum')
        ->postJson("/api/v1/marketplace/listings/{$listing->id}/checkout");

    $response->assertStatus(403);
});

// ── Invalid Listing States ──────────────────────────────────────────

test('cannot checkout unverified listing', function () {
    $seller = User::factory()->create();
    $ticket = Ticket::factory()->withOriginalPrice(500000)->create([
        'current_owner_id' => $seller->id,
        'status' => 'aktif',
    ]);
    $listing = ResaleListing::factory()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $seller->id,
        'verification_status' => 'pending',
        'listing_status' => 'aktif',
    ]);
    $buyer = User::factory()->create();

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/marketplace/listings/{$listing->id}/checkout");

    $response->assertStatus(422);
});

test('cannot checkout sold listing', function () {
    $seller = User::factory()->create();
    $ticket = Ticket::factory()->withOriginalPrice(500000)->create([
        'current_owner_id' => $seller->id,
        'status' => 'aktif',
    ]);
    $listing = ResaleListing::factory()->verified()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $seller->id,
        'listing_status' => 'terjual',
        'sold_at' => now(),
    ]);
    $buyer = User::factory()->create();

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/marketplace/listings/{$listing->id}/checkout");

    $response->assertStatus(422);
});

// ── Checkout Lock Rule ──────────────────────────────────────────────

test('cannot checkout listing with active pending transaction', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createPurchasableListing();
    $firstBuyer = User::factory()->create();
    $secondBuyer = User::factory()->create();

    Transaction::factory()->create([
        'buyer_id' => $firstBuyer->id,
        'seller_id' => $seller->id,
        'resale_listing_id' => $listing->id,
        'ticket_id' => $ticket->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($secondBuyer, 'sanctum')
        ->postJson("/api/v1/marketplace/listings/{$listing->id}/checkout");

    $response->assertStatus(422);
});

test('can checkout listing after previous transaction failed', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createPurchasableListing();
    $firstBuyer = User::factory()->create();
    $secondBuyer = User::factory()->create();

    Transaction::factory()->failed()->create([
        'buyer_id' => $firstBuyer->id,
        'seller_id' => $seller->id,
        'resale_listing_id' => $listing->id,
        'ticket_id' => $ticket->id,
    ]);

    $response = $this->actingAs($secondBuyer, 'sanctum')
        ->postJson("/api/v1/marketplace/listings/{$listing->id}/checkout");

    $response->assertStatus(201);
});

test('completed transaction does not block checkout on listing status grounds', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createPurchasableListing();
    $firstBuyer = User::factory()->create();
    $secondBuyer = User::factory()->create();

    Transaction::factory()->completed()->create([
        'buyer_id' => $firstBuyer->id,
        'seller_id' => $seller->id,
        'resale_listing_id' => $listing->id,
        'ticket_id' => $ticket->id,
    ]);

    // Simulate listing already marked as sold after completed transaction.
    $listing->update(['listing_status' => 'terjual', 'sold_at' => now()]);

    $response = $this->actingAs($secondBuyer, 'sanctum')
        ->postJson("/api/v1/marketplace/listings/{$listing->id}/checkout");

    // Fails because listing is terjual, not because of lock rule.
    $response->assertStatus(422);
});

// ── Database Constraint Enforcement ─────────────────────────────────

test('database constraint prevents duplicate active transactions for same listing', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createPurchasableListing();
    $buyer1 = User::factory()->create();
    $buyer2 = User::factory()->create();

    Transaction::create([
        'buyer_id' => $buyer1->id,
        'seller_id' => $seller->id,
        'resale_listing_id' => $listing->id,
        'ticket_id' => $ticket->id,
        'amount' => 500000,
        'status' => 'pending',
    ]);

    expect(fn () => Transaction::create([
        'buyer_id' => $buyer2->id,
        'seller_id' => $seller->id,
        'resale_listing_id' => $listing->id,
        'ticket_id' => $ticket->id,
        'amount' => 500000,
        'status' => 'pending',
    ]))->toThrow(QueryException::class);
});

// ── Listing Not Found ───────────────────────────────────────────────

test('checkout returns 404 for non-existent listing', function () {
    $buyer = User::factory()->create();

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson('/api/v1/marketplace/listings/01NONEXISTENT000000000000/checkout');

    $response->assertStatus(404);
});
