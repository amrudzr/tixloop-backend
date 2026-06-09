<?php

use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\TicketOwnershipHistory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin']);
});

// ── Helper ──────────────────────────────────────────────────────────

/**
 * Create a paid transaction with escrow held, ready for release.
 *
 * @return array{transaction: Transaction, buyer: User, seller: User, ticket: Ticket, listing: ResaleListing}
 */
function createHeldTransaction(float $price = 500000): array
{
    $seller = User::factory()->create();
    $ticket = Ticket::factory()->withOriginalPrice($price)->create([
        'current_owner_id' => $seller->id,
        'status' => 'dalam_escrow',
    ]);
    $listing = ResaleListing::factory()->verified()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $seller->id,
        'current_asking_price' => $price,
    ]);
    $buyer = User::factory()->create();

    $transaction = Transaction::create([
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'resale_listing_id' => $listing->id,
        'ticket_id' => $ticket->id,
        'amount' => $price,
        'service_fee' => 0.00,
        'status' => 'paid',
        'escrow_status' => 'held',
        'paid_at' => now(),
    ]);

    return compact('transaction', 'buyer', 'seller', 'ticket', 'listing');
}

// ── Authentication ──────────────────────────────────────────────────

test('guest cannot release escrow', function () {
    ['transaction' => $transaction] = createHeldTransaction();

    $response = $this->postJson("/api/v1/transactions/{$transaction->id}/release-escrow");

    $response->assertStatus(401);
});

// ── Successful Escrow Release ───────────────────────────────────────

test('buyer can release escrow and receive ownership', function () {
    ['transaction' => $transaction, 'buyer' => $buyer, 'seller' => $seller, 'ticket' => $ticket, 'listing' => $listing] = createHeldTransaction();

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/release-escrow");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.escrow_status', 'released')
        ->assertJsonPath('data.released_by', $buyer->id)
        ->assertJsonPath('data.ticket.current_owner_id', $buyer->id);

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'status' => 'completed',
        'escrow_status' => 'released',
        'released_by' => $buyer->id,
    ]);

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'current_owner_id' => $buyer->id,
        'status' => 'terjual',
    ]);

    $this->assertDatabaseHas('resale_listings', [
        'id' => $listing->id,
        'listing_status' => 'terjual',
    ]);

    $this->assertDatabaseHas('ticket_ownership_history', [
        'ticket_id' => $ticket->id,
        'transaction_id' => $transaction->id,
        'previous_owner_id' => $seller->id,
        'new_owner_id' => $buyer->id,
    ]);
});

test('admin can release escrow for any transaction', function () {
    ['transaction' => $transaction, 'buyer' => $buyer, 'ticket' => $ticket] = createHeldTransaction();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/release-escrow");

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.escrow_status', 'released')
        ->assertJsonPath('data.released_by', $admin->id)
        ->assertJsonPath('data.ticket.current_owner_id', $buyer->id);

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'released_by' => $admin->id,
    ]);
});

// ── Access Control ──────────────────────────────────────────────────

test('seller cannot release escrow', function () {
    ['transaction' => $transaction, 'seller' => $seller] = createHeldTransaction();

    $response = $this->actingAs($seller, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/release-escrow");

    $response->assertStatus(403);
});

test('unrelated user cannot release escrow', function () {
    ['transaction' => $transaction] = createHeldTransaction();
    $stranger = User::factory()->create();

    $response = $this->actingAs($stranger, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/release-escrow");

    $response->assertStatus(403);
});

// ── Idempotency & State Guards ──────────────────────────────────────

test('cannot release escrow twice', function () {
    ['transaction' => $transaction, 'buyer' => $buyer] = createHeldTransaction();

    // First release succeeds
    $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/release-escrow")
        ->assertStatus(200);

    // Second release rejected
    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/release-escrow");

    $response->assertStatus(403);
});

test('cannot release escrow on pending transaction', function () {
    ['transaction' => $transaction, 'buyer' => $buyer] = createHeldTransaction();

    $transaction->update([
        'status' => 'pending',
        'escrow_status' => null,
    ]);

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/release-escrow");

    $response->assertStatus(403);
});

test('cannot release escrow on failed transaction', function () {
    ['transaction' => $transaction, 'buyer' => $buyer] = createHeldTransaction();

    $transaction->update([
        'status' => 'failed',
        'escrow_status' => null,
    ]);

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/release-escrow");

    $response->assertStatus(403);
});

// ── Ownership History Audit ─────────────────────────────────────────

test('ownership history records correct transfer data on escrow release', function () {
    ['transaction' => $transaction, 'buyer' => $buyer, 'seller' => $seller, 'ticket' => $ticket] = createHeldTransaction();

    $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/release-escrow")
        ->assertStatus(200);

    $history = TicketOwnershipHistory::where('ticket_id', $ticket->id)->first();

    expect($history)->not->toBeNull();
    expect($history->previous_owner_id)->toBe($seller->id);
    expect($history->new_owner_id)->toBe($buyer->id);
    expect($history->transaction_id)->toBe($transaction->id);
    expect($history->transferred_at)->not->toBeNull();
});
