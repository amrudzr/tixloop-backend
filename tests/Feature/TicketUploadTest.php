<?php

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ── Ticket Upload Tests ──────────────────────────────────────────────

test('authenticated user can upload a ticket with proof', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $file = UploadedFile::fake()->create('proof.pdf', 500, 'application/pdf');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets/upload', [
            'event_id' => $event->id,
            'ticket_code' => 'TIX-0001-ABCD',
            'seat_number' => 'A-1',
            'original_price' => 500000,
            'ticket_proof' => $file,
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Tiket berhasil diunggah.',
        ])
        ->assertJsonPath('data.event_id', $event->id)
        ->assertJsonPath('data.current_owner_id', $user->id)
        ->assertJsonPath('data.ticket_code', 'TIX-0001-ABCD')
        ->assertJsonPath('data.status', 'aktif');

    $ticket = Ticket::first();
    expect($ticket->ticket_proof_path)->not->toBeNull();
    expect($ticket->ticket_metadata['original_price'])->toEqual(500000);
    Storage::disk('local')->assertExists($ticket->ticket_proof_path);
});

test('ticket upload stores physical photo when provided', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $proof = UploadedFile::fake()->create('proof.pdf', 500, 'application/pdf');
    $photo = UploadedFile::fake()->image('physical.jpg', 400, 400);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets/upload', [
            'event_id' => $event->id,
            'ticket_code' => 'TIX-0002-EFGH',
            'original_price' => 750000,
            'ticket_proof' => $proof,
            'physical_photo' => $photo,
        ]);

    $response->assertStatus(201);

    $ticket = Ticket::first();
    $metadata = $ticket->ticket_metadata;
    expect($metadata)->toHaveKey('physical_photo_path');
    Storage::disk('local')->assertExists($metadata['physical_photo_path']);
});

test('guest cannot upload a ticket', function () {
    $event = Event::factory()->create();

    $response = $this->postJson('/api/v1/tickets/upload', [
        'event_id' => $event->id,
        'ticket_code' => 'TIX-0003',
        'original_price' => 500000,
    ]);

    $response->assertStatus(401);
});

test('ticket upload validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets/upload', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['event_id', 'ticket_code', 'original_price', 'ticket_proof']);
});

test('ticket upload rejects invalid file type', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $invalidFile = UploadedFile::fake()->create('proof.txt', 100, 'text/plain');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets/upload', [
            'event_id' => $event->id,
            'ticket_code' => 'TIX-0004',
            'original_price' => 500000,
            'ticket_proof' => $invalidFile,
        ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['ticket_proof']);
});

test('ticket upload rejects oversized file', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $largeFile = UploadedFile::fake()->create('proof.pdf', 3000, 'application/pdf');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets/upload', [
            'event_id' => $event->id,
            'ticket_code' => 'TIX-0005',
            'original_price' => 500000,
            'ticket_proof' => $largeFile,
        ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['ticket_proof']);
});

test('ticket upload stores proof on configurable disk', function () {
    Storage::fake('public');
    config(['filesystems.tickets_disk' => 'public']);

    $user = User::factory()->create();
    $event = Event::factory()->create();
    $file = UploadedFile::fake()->create('proof.pdf', 500, 'application/pdf');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets/upload', [
            'event_id' => $event->id,
            'ticket_code' => 'TIX-0006-XYZ',
            'seat_number' => 'B-2',
            'original_price' => 600000,
            'ticket_proof' => $file,
        ]);

    $response->assertStatus(201);

    $ticket = Ticket::where('ticket_code', 'TIX-0006-XYZ')->first();
    expect($ticket->ticket_proof_path)->not->toBeNull();
    Storage::disk('public')->assertExists($ticket->ticket_proof_path);
    Storage::disk('local')->assertMissing($ticket->ticket_proof_path);
});

test('ticket upload stores ticket_type in metadata when provided', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $file = UploadedFile::fake()->create('proof.pdf', 500, 'application/pdf');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets/upload', [
            'event_id' => $event->id,
            'ticket_code' => 'TIX-TYPE-001',
            'original_price' => 500000,
            'ticket_proof' => $file,
            'ticket_type' => 'VIP',
        ]);

    $response->assertStatus(201);

    $ticket = Ticket::where('ticket_code', 'TIX-TYPE-001')->first();
    expect($ticket->ticket_metadata['type'])->toBe('VIP');
    expect($ticket->ticket_metadata['original_price'])->toEqual(500000);
});

test('ticket upload works without ticket_type (backward compatible)', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $file = UploadedFile::fake()->create('proof.pdf', 500, 'application/pdf');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets/upload', [
            'event_id' => $event->id,
            'ticket_code' => 'TIX-NOMOD-001',
            'original_price' => 350000,
            'ticket_proof' => $file,
        ]);

    $response->assertStatus(201);

    $ticket = Ticket::where('ticket_code', 'TIX-NOMOD-001')->first();
    expect($ticket->ticket_metadata)->not->toHaveKey('type');
    expect($ticket->ticket_metadata['original_price'])->toEqual(350000);
});

test('ticket upload rejects ticket_type exceeding max length', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $file = UploadedFile::fake()->create('proof.pdf', 500, 'application/pdf');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tickets/upload', [
            'event_id' => $event->id,
            'ticket_code' => 'TIX-LONG-001',
            'original_price' => 500000,
            'ticket_proof' => $file,
            'ticket_type' => str_repeat('X', 51),
        ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['ticket_type']);
});
