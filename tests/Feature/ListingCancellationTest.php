<?php

namespace Tests\Feature;

use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingCancellationTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;

    private User $buyer;

    private Ticket $ticket;

    private ResaleListing $listing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create();
        $this->buyer = User::factory()->create();

        $this->ticket = Ticket::factory()->create([
            'current_owner_id' => $this->seller->id,
            'status' => 'aktif',
            'ticket_metadata' => [
                'original_price' => 500000,
            ],
        ]);

        $this->listing = ResaleListing::create([
            'ticket_id' => $this->ticket->id,
            'seller_id' => $this->seller->id,
            'original_price' => 500000,
            'current_asking_price' => 500000,
            'floor_price' => 225000,
            'hard_cap_price' => 575000,
            'verification_status' => 'verified',
            'listing_status' => 'aktif',
        ]);
    }

    public function test_seller_can_cancel_active_listing(): void
    {
        $response = $this->actingAs($this->seller)
            ->deleteJson("/api/v1/marketplace/listings/{$this->listing->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Listing berhasil dibatalkan.');

        $this->assertDatabaseHas('resale_listings', [
            'id' => $this->listing->id,
            'listing_status' => 'dibatalkan',
        ]);

        // Ensure ticket can be listed again
        $relistResponse = $this->actingAs($this->seller)
            ->postJson('/api/v1/marketplace/listings', [
                'ticket_id' => $this->ticket->id,
                'current_asking_price' => 500000,
            ]);

        $relistResponse->assertStatus(201);
    }

    public function test_non_seller_cannot_cancel_listing(): void
    {
        $response = $this->actingAs($this->buyer)
            ->deleteJson("/api/v1/marketplace/listings/{$this->listing->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_cancel_sold_listing(): void
    {
        $this->listing->update(['listing_status' => 'terjual']);

        $response = $this->actingAs($this->seller)
            ->deleteJson("/api/v1/marketplace/listings/{$this->listing->id}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['listing']);
    }

    public function test_cannot_cancel_listing_with_active_transaction(): void
    {
        Transaction::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'resale_listing_id' => $this->listing->id,
            'ticket_id' => $this->ticket->id,
            'amount' => 500000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->seller)
            ->deleteJson("/api/v1/marketplace/listings/{$this->listing->id}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['listing']);
    }

    public function test_can_cancel_listing_with_failed_transaction(): void
    {
        Transaction::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'resale_listing_id' => $this->listing->id,
            'ticket_id' => $this->ticket->id,
            'amount' => 500000,
            'status' => 'failed',
        ]);

        $response = $this->actingAs($this->seller)
            ->deleteJson("/api/v1/marketplace/listings/{$this->listing->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('resale_listings', [
            'id' => $this->listing->id,
            'listing_status' => 'dibatalkan',
        ]);
    }
}
