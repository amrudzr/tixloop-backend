<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketFileAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $otherUser;
    private User $admin;
    private Event $event;
    private Ticket $ticket;
    private string $proofPath;
    private string $physicalPhotoPath;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'seller']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'buyer']);

        Storage::fake(config('filesystems.tickets_disk', 'local'));

        $this->owner = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->event = Event::factory()->create();

        $proofFile = UploadedFile::fake()->image('proof.jpg');
        $this->proofPath = $proofFile->store('ticket_proofs', config('filesystems.tickets_disk', 'local'));

        $physicalPhotoFile = UploadedFile::fake()->image('physical.jpg');
        $this->physicalPhotoPath = $physicalPhotoFile->store('ticket_proofs', config('filesystems.tickets_disk', 'local'));

        $this->ticket = Ticket::factory()->create([
            'event_id' => $this->event->id,
            'current_owner_id' => $this->owner->id,
            'original_buyer_id' => $this->owner->id,
            'ticket_proof_path' => $this->proofPath,
            'ticket_metadata' => [
                'physical_photo_path' => $this->physicalPhotoPath,
            ],
        ]);
    }

    public function test_owner_can_download_proof(): void
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/tickets/{$this->ticket->id}/proof");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_admin_can_download_proof(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/tickets/{$this->ticket->id}/proof");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_other_user_gets_403_for_proof(): void
    {
        $response = $this->actingAs($this->otherUser, 'sanctum')
            ->getJson("/api/v1/tickets/{$this->ticket->id}/proof");

        $response->assertStatus(403);
    }

    public function test_guest_gets_401_for_proof(): void
    {
        $response = $this->getJson("/api/v1/tickets/{$this->ticket->id}/proof");

        $response->assertStatus(401);
    }

    public function test_returns_404_if_proof_file_not_found_on_disk(): void
    {
        // Delete the file from the fake storage
        Storage::disk(config('filesystems.tickets_disk', 'local'))->delete($this->ticket->ticket_proof_path);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/tickets/{$this->ticket->id}/proof");

        $response->assertStatus(404)
            ->assertJson(['message' => 'File tidak ditemukan.']);
    }

    public function test_owner_can_download_physical_photo(): void
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/tickets/{$this->ticket->id}/physical-photo");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_admin_can_download_physical_photo(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/tickets/{$this->ticket->id}/physical-photo");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_other_user_gets_403_for_physical_photo(): void
    {
        $response = $this->actingAs($this->otherUser, 'sanctum')
            ->getJson("/api/v1/tickets/{$this->ticket->id}/physical-photo");

        $response->assertStatus(403);
    }

    public function test_returns_404_if_physical_photo_not_found(): void
    {
        $ticketWithoutPhoto = Ticket::factory()->create([
            'event_id' => $this->event->id,
            'current_owner_id' => $this->owner->id,
            'original_buyer_id' => $this->owner->id,
            'ticket_proof_path' => $this->proofPath,
            'ticket_metadata' => [], // No physical photo
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/tickets/{$ticketWithoutPhoto->id}/physical-photo");

        $response->assertStatus(404)
            ->assertJson(['message' => 'File tidak ditemukan.']);
    }
}
