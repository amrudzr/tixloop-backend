<?php

use App\Models\Event;
use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns transaction history for logged-in user', function () {
    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $event = Event::factory()->create();
    $ticket = Ticket::factory()->create(['event_id' => $event->id]);
    $listing = ResaleListing::factory()->create(['ticket_id' => $ticket->id, 'seller_id' => $seller->id]);

    $transaction = Transaction::factory()->create([
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'ticket_id' => $ticket->id,
        'resale_listing_id' => $listing->id,
    ]);

    $this->actingAs($buyer)
        ->getJson('/api/v1/transactions')
        ->assertStatus(200)
        ->assertJsonPath('data.data.0.id', $transaction->id)
        ->assertJsonPath('data.data.0.buyer.id', $buyer->id)
        ->assertJsonPath('data.data.0.seller.id', $seller->id)
        ->assertJsonPath('data.data.0.ticket.event.id', $event->id);
});

it('does not return transactions of other users', function () {
    $otherBuyer = User::factory()->create();
    $seller = User::factory()->create();
    
    Transaction::factory()->create([
        'buyer_id' => $otherBuyer->id,
        'seller_id' => $seller->id,
    ]);

    $me = User::factory()->create();

    $this->actingAs($me)
        ->getJson('/api/v1/transactions')
        ->assertStatus(200)
        ->assertJsonCount(0, 'data.data');
});

it('requires authentication to view transaction history', function () {
    $this->getJson('/api/v1/transactions')
        ->assertStatus(401);
});
