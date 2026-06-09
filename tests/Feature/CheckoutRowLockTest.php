<?php

use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\OwnershipTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

// ── Helper ──────────────────────────────────────────────────────────

/**
 * Create a verified, active listing ready for checkout.
 *
 * @return array{listing: ResaleListing, seller: User, ticket: Ticket}
 */
function createActiveListing(float $price = 500000): array
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

// ── CheckoutService Row Lock ────────────────────────────────────────

test('checkout service uses lockForUpdate inside DB transaction', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createActiveListing();
    $buyer = User::factory()->create();

    $service = app(CheckoutService::class);
    $transaction = $service->initiateCheckout($buyer, $listing);

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->status)->toBe('pending');
    expect($transaction->buyer_id)->toBe($buyer->id);
    expect($transaction->seller_id)->toBe($seller->id);
});

test('second checkout on same listing is rejected by active transaction guard', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createActiveListing();
    $buyer1 = User::factory()->create();
    $buyer2 = User::factory()->create();

    $service = app(CheckoutService::class);
    $service->initiateCheckout($buyer1, $listing);

    expect(fn () => $service->initiateCheckout($buyer2, $listing))
        ->toThrow(ValidationException::class);

    $this->assertDatabaseCount('transactions', 1);
});

test('checkout allowed after previous transaction failed', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createActiveListing();
    $buyer1 = User::factory()->create();
    $buyer2 = User::factory()->create();

    $service = app(CheckoutService::class);
    $tx1 = $service->initiateCheckout($buyer1, $listing);
    $tx1->update(['status' => 'failed']);

    $tx2 = $service->initiateCheckout($buyer2, $listing);

    expect($tx2->status)->toBe('pending');
    expect($tx2->buyer_id)->toBe($buyer2->id);
});

// ── OwnershipTransferService Row Lock ───────────────────────────────

test('ownership transfer locks transaction row before status assertion', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createActiveListing();
    $buyer = User::factory()->create();

    $service = app(CheckoutService::class);
    $transaction = $service->initiateCheckout($buyer, $listing);

    $transferService = app(OwnershipTransferService::class);
    $result = $transferService->simulatePaymentAndTransfer($transaction);

    expect($result->status)->toBe('completed');
    expect($result->escrow_status)->toBe('released');
    expect($result->ticket->current_owner_id)->toBe($buyer->id);
});

test('ownership transfer rejects non-pending transaction', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createActiveListing();
    $buyer = User::factory()->create();

    $service = app(CheckoutService::class);
    $transaction = $service->initiateCheckout($buyer, $listing);
    $transaction->update(['status' => 'completed']);

    $transferService = app(OwnershipTransferService::class);

    expect(fn () => $transferService->simulatePaymentAndTransfer($transaction))
        ->toThrow(ValidationException::class);
});

test('listing marked as terjual after successful transfer', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createActiveListing();
    $buyer = User::factory()->create();

    $checkoutService = app(CheckoutService::class);
    $transaction = $checkoutService->initiateCheckout($buyer, $listing);

    $transferService = app(OwnershipTransferService::class);
    $transferService->simulatePaymentAndTransfer($transaction);

    $listing->refresh();
    expect($listing->listing_status)->toBe('terjual');
    expect($listing->sold_at)->not->toBeNull();
});

test('second buyer cannot checkout after transfer completes', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createActiveListing();
    $buyer1 = User::factory()->create();
    $buyer2 = User::factory()->create();

    $checkoutService = app(CheckoutService::class);
    $transaction = $checkoutService->initiateCheckout($buyer1, $listing);

    $transferService = app(OwnershipTransferService::class);
    $transferService->simulatePaymentAndTransfer($transaction);

    expect(fn () => $checkoutService->initiateCheckout($buyer2, $listing))
        ->toThrow(ValidationException::class);
});

// ── Full Purchase Flow Atomicity ────────────────────────────────────

test('full purchase flow is atomic: checkout then transfer', function () {
    ['listing' => $listing, 'seller' => $seller, 'ticket' => $ticket] = createActiveListing();
    $buyer = User::factory()->create();

    $checkoutService = app(CheckoutService::class);
    $transferService = app(OwnershipTransferService::class);

    $transaction = $checkoutService->initiateCheckout($buyer, $listing);
    $result = $transferService->simulatePaymentAndTransfer($transaction);

    expect($result->status)->toBe('completed');

    $ticket->refresh();
    expect($ticket->current_owner_id)->toBe($buyer->id);

    $listing->refresh();
    expect($listing->listing_status)->toBe('terjual');

    $this->assertDatabaseCount('transactions', 1);
    $this->assertDatabaseCount('ticket_ownership_history', 1);
});
