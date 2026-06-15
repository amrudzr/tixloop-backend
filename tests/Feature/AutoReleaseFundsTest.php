<?php

use App\Models\Event;
use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\TicketOwnershipHistory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin']);
    User::factory()->create()->assignRole('admin');
});

// ── Helper ──────────────────────────────────────────────────────────

/**
 * Create a paid transaction with escrow held, ready for release.
 *
 * @return array{transaction: Transaction, buyer: User, seller: User, ticket: Ticket, listing: ResaleListing, event: Event}
 */
function createTransactionWithEventDate($eventDate, string $status = 'paid', ?string $escrowStatus = 'held'): array
{
    $seller = User::factory()->create();

    $event = Event::factory()->create([
        'event_datetime' => $eventDate,
    ]);

    $ticket = Ticket::factory()->create([
        'event_id' => $event->id,
        'current_owner_id' => $seller->id,
        'status' => 'dalam_escrow',
    ]);

    $listing = ResaleListing::factory()->verified()->create([
        'ticket_id' => $ticket->id,
        'seller_id' => $seller->id,
        'current_asking_price' => 500000,
    ]);
    $buyer = User::factory()->create();

    $transaction = Transaction::create([
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'resale_listing_id' => $listing->id,
        'ticket_id' => $ticket->id,
        'amount' => 500000,
        'service_fee' => 0.00,
        'status' => $status,
        'escrow_status' => $escrowStatus,
        'paid_at' => now(),
    ]);

    return compact('transaction', 'buyer', 'seller', 'ticket', 'listing', 'event');
}

// ── Tests ───────────────────────────────────────────────────────────

test('transaksi H+3 berhasil auto release', function () {
    $admin = User::role('admin')->first();

    ['transaction' => $transaction, 'buyer' => $buyer, 'ticket' => $ticket, 'listing' => $listing] = createTransactionWithEventDate(now()->subDays(4));

    Artisan::call('tixloop:auto-release-funds');

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'status' => 'completed',
        'escrow_status' => 'released',
        'released_by' => $admin->id,
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
        'new_owner_id' => $buyer->id,
    ]);
});

test('transaksi belum H+3 tidak diproses', function () {
    ['transaction' => $transaction, 'ticket' => $ticket] = createTransactionWithEventDate(now()->subDays(2));

    Artisan::call('tixloop:auto-release-funds');

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'status' => 'paid',
        'escrow_status' => 'held',
    ]);

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'dalam_escrow',
    ]);
});

test('status selain paid/held diabaikan', function () {
    ['transaction' => $transaction1] = createTransactionWithEventDate(now()->subDays(4), 'completed', 'released');
    ['transaction' => $transaction2] = createTransactionWithEventDate(now()->subDays(4), 'failed', null);

    Artisan::call('tixloop:auto-release-funds');

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction1->id,
        'status' => 'completed',
        'escrow_status' => 'released',
    ]);

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction2->id,
        'status' => 'failed',
        'escrow_status' => null,
    ]);
});

test('ownership history tetap tercatat dan listing berubah menjadi terjual', function () {
    ['transaction' => $transaction, 'buyer' => $buyer, 'seller' => $seller, 'ticket' => $ticket, 'listing' => $listing] = createTransactionWithEventDate(now()->subDays(4));

    Artisan::call('tixloop:auto-release-funds');

    $history = TicketOwnershipHistory::where('ticket_id', $ticket->id)->first();

    expect($history)->not->toBeNull();
    expect($history->previous_owner_id)->toBe($seller->id);
    expect($history->new_owner_id)->toBe($buyer->id);
    expect($history->transaction_id)->toBe($transaction->id);
    expect($history->transferred_at)->not->toBeNull();

    $this->assertDatabaseHas('resale_listings', [
        'id' => $listing->id,
        'listing_status' => 'terjual',
    ]);
});

test('released_by terisi admin sistem', function () {
    $admin = User::role('admin')->first();
    ['transaction' => $transaction] = createTransactionWithEventDate(now()->subDays(4));

    Artisan::call('tixloop:auto-release-funds');

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'released_by' => $admin->id,
    ]);
});
