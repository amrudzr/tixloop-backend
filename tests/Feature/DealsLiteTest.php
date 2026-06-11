<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DealsLiteTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;

    private Ticket $ticket1;

    private Ticket $ticket2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create();
        $event = Event::factory()->create();

        $this->ticket1 = Ticket::factory()->create([
            'current_owner_id' => $this->seller->id,
            'event_id' => $event->id,
            'status' => 'aktif',
            'ticket_metadata' => ['original_price' => 1000000],
        ]);

        $this->ticket2 = Ticket::factory()->create([
            'current_owner_id' => $this->seller->id,
            'event_id' => $event->id,
            'status' => 'aktif',
            'ticket_metadata' => ['original_price' => 1000000],
        ]);
    }

    public function test_create_listing_tanpa_deals(): void
    {
        $response = $this->actingAs($this->seller)
            ->postJson('/api/v1/marketplace/listings', [
                'ticket_id' => $this->ticket1->id,
                'current_asking_price' => 1100000,
                'is_auto_drop' => false,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.is_auto_drop', false)
            ->assertJsonPath('data.floor_price', 450000);

        $this->assertDatabaseHas('resale_listings', [
            'ticket_id' => $this->ticket1->id,
            'is_auto_drop' => false,
            'floor_price' => 450000.0,
        ]);
    }

    public function test_create_listing_dengan_deals_berhasil(): void
    {
        $response = $this->actingAs($this->seller)
            ->postJson('/api/v1/marketplace/listings', [
                'ticket_id' => $this->ticket1->id,
                'current_asking_price' => 1100000,
                'is_auto_drop' => true,
                'floor_price' => 800000,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.is_auto_drop', true)
            ->assertJsonPath('data.floor_price', 800000);

        $this->assertDatabaseHas('resale_listings', [
            'ticket_id' => $this->ticket1->id,
            'is_auto_drop' => true,
            'floor_price' => 800000,
        ]);
    }

    public function test_create_listing_dengan_deals_tanpa_floor_price_gagal(): void
    {
        $response = $this->actingAs($this->seller)
            ->postJson('/api/v1/marketplace/listings', [
                'ticket_id' => $this->ticket1->id,
                'current_asking_price' => 1100000,
                'is_auto_drop' => true,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('floor_price');
    }

    public function test_create_listing_dengan_deals_floor_price_melebihi_original_gagal(): void
    {
        $response = $this->actingAs($this->seller)
            ->postJson('/api/v1/marketplace/listings', [
                'ticket_id' => $this->ticket1->id,
                'current_asking_price' => 1100000,
                'is_auto_drop' => true,
                'floor_price' => 1050000, // Original is 1000000
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('floor_price');
    }

    public function test_marketplace_deals_filter_hanya_menampilkan_deals(): void
    {
        $listingNonDeals = ResaleListing::create([
            'id' => Str::ulid(),
            'ticket_id' => $this->ticket1->id,
            'seller_id' => $this->seller->id,
            'original_price' => 1000000,
            'current_asking_price' => 1100000,
            'is_auto_drop' => false,
            'floor_price' => null,
            'hard_cap_price' => 1150000,
            'verification_status' => 'verified',
            'listing_status' => 'aktif',
        ]);

        $listingDeals = ResaleListing::create([
            'id' => Str::ulid(),
            'ticket_id' => $this->ticket2->id,
            'seller_id' => $this->seller->id,
            'original_price' => 1000000,
            'current_asking_price' => 1000000,
            'is_auto_drop' => true,
            'floor_price' => 800000,
            'hard_cap_price' => 1150000,
            'verification_status' => 'verified',
            'listing_status' => 'aktif',
        ]);

        // Default: semua aktif dan verified tampil
        $responseDefault = $this->getJson('/api/v1/marketplace/listings');
        $responseDefault->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Filter deals=true
        $responseDeals = $this->getJson('/api/v1/marketplace/listings?deals=true');
        $responseDeals->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $listingDeals->id)
            ->assertJsonPath('data.0.is_auto_drop', true);
    }
}
