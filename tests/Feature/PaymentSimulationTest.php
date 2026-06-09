<?php

use App\Models\ResaleListing;
use App\Models\Ticket;
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
 * Create a pending transaction ready for payment simulation.
 *
 * @return array{transaction: Transaction, buyer: User, seller: User, ticket: Ticket, listing: ResaleListing}
 */
function createPendingTransaction(float $price = 500000): array
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
    $buyer = User::factory()->create();

    $transaction = Transaction::create([
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'resale_listing_id' => $listing->id,
        'ticket_id' => $ticket->id,
        'amount' => $price,
        'service_fee' => 0.00,
        'status' => 'pending',
    ]);

    return compact('transaction', 'buyer', 'seller', 'ticket', 'listing');
}

// ── Authentication ──────────────────────────────────────────────────

test('guest cannot simulate payment', function () {
    ['transaction' => $transaction] = createPendingTransaction();

    $response = $this->postJson("/api/v1/transactions/{$transaction->id}/simulate-payment");

    $response->assertStatus(401);
});

// ── Successful Payment Simulation (Phase 1: Escrow Held) ───────────

test('buyer can simulate payment and hold escrow', function () {
    ['transaction' => $transaction, 'buyer' => $buyer, 'seller' => $seller, 'ticket' => $ticket, 'listing' => $listing] = createPendingTransaction();

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/simulate-payment");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'paid')
        ->assertJsonPath('data.escrow_status', 'held')
        ->assertJsonPath('data.ticket.current_owner_id', $seller->id);

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'status' => 'paid',
        'escrow_status' => 'held',
    ]);

    // Ownership NOT transferred during payment phase
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'current_owner_id' => $seller->id,
        'status' => 'dalam_escrow',
    ]);

    // Listing still aktif during payment phase
    $this->assertDatabaseHas('resale_listings', [
        'id' => $listing->id,
        'listing_status' => 'aktif',
    ]);

    // No ownership history created during payment
    $this->assertDatabaseMissing('ticket_ownership_history', [
        'ticket_id' => $ticket->id,
    ]);
});

// ── Access Control ──────────────────────────────────────────────────

test('seller cannot simulate payment for transaction', function () {
    ['transaction' => $transaction, 'seller' => $seller] = createPendingTransaction();

    $response = $this->actingAs($seller, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/simulate-payment");

    $response->assertStatus(403);
});

test('unrelated user cannot simulate payment', function () {
    ['transaction' => $transaction] = createPendingTransaction();
    $stranger = User::factory()->create();

    $response = $this->actingAs($stranger, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/simulate-payment");

    $response->assertStatus(403);
});

// ── Invalid Transaction State ───────────────────────────────────────

test('cannot simulate payment for already paid transaction', function () {
    ['transaction' => $transaction, 'buyer' => $buyer] = createPendingTransaction();

    $transaction->update([
        'status' => 'paid',
        'escrow_status' => 'held',
        'paid_at' => now(),
    ]);

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/simulate-payment");

    $response->assertStatus(403);
});

test('cannot simulate payment for completed transaction', function () {
    ['transaction' => $transaction, 'buyer' => $buyer] = createPendingTransaction();

    $transaction->update([
        'status' => 'completed',
        'escrow_status' => 'released',
        'completed_at' => now(),
    ]);

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/simulate-payment");

    $response->assertStatus(403);
});

test('cannot simulate payment for failed transaction', function () {
    ['transaction' => $transaction, 'buyer' => $buyer] = createPendingTransaction();

    $transaction->update(['status' => 'failed']);

    $response = $this->actingAs($buyer, 'sanctum')
        ->postJson("/api/v1/transactions/{$transaction->id}/simulate-payment");

    $response->assertStatus(403);
});

// ── Transaction Detail View ─────────────────────────────────────────

test('buyer can view their transaction', function () {
    ['transaction' => $transaction, 'buyer' => $buyer] = createPendingTransaction();

    $response = $this->actingAs($buyer, 'sanctum')
        ->getJson("/api/v1/transactions/{$transaction->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $transaction->id)
        ->assertJsonPath('data.status', 'pending');
});

test('seller can view transaction for their listing', function () {
    ['transaction' => $transaction, 'seller' => $seller] = createPendingTransaction();

    $response = $this->actingAs($seller, 'sanctum')
        ->getJson("/api/v1/transactions/{$transaction->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $transaction->id);
});

test('admin can view any transaction', function () {
    ['transaction' => $transaction] = createPendingTransaction();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/v1/transactions/{$transaction->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $transaction->id);
});

test('unrelated user cannot view transaction', function () {
    ['transaction' => $transaction] = createPendingTransaction();
    $stranger = User::factory()->create();

    $response = $this->actingAs($stranger, 'sanctum')
        ->getJson("/api/v1/transactions/{$transaction->id}");

    $response->assertStatus(403);
});

test('guest cannot view transaction', function () {
    ['transaction' => $transaction] = createPendingTransaction();

    $response = $this->getJson("/api/v1/transactions/{$transaction->id}");

    $response->assertStatus(401);
});
