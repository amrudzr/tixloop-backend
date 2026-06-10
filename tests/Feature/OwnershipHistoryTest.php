<?php

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketOwnershipHistory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

test('guest gets 401 when accessing history', function () {
    $ticket = Ticket::factory()->create();

    getJson("/api/v1/tickets/{$ticket->id}/history")
        ->assertStatus(401);
});

test('non owner gets 403 when accessing history', function () {
    $ticket = Ticket::factory()->create();
    $otherUser = User::factory()->create();

    actingAs($otherUser, 'sanctum')
        ->getJson("/api/v1/tickets/{$ticket->id}/history")
        ->assertStatus(403);
});

test('admin can view history', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $ticket = Ticket::factory()->create();

    actingAs($admin, 'sanctum')
        ->getJson("/api/v1/tickets/{$ticket->id}/history")
        ->assertStatus(200);
});

test('owner can view history in chronological order without pii', function () {
    $owner = User::factory()->create();
    $previousOwner1 = User::factory()->create();
    $previousOwner2 = User::factory()->create();

    $event = Event::factory()->create();
    $ticket = Ticket::factory()->create([
        'event_id' => $event->id,
        'current_owner_id' => $owner->id,
    ]);

    $transaction1 = Transaction::factory()->create();
    $transaction2 = Transaction::factory()->create();

    // Oldest transfer
    TicketOwnershipHistory::factory()->create([
        'ticket_id' => $ticket->id,
        'transaction_id' => $transaction1->id,
        'previous_owner_id' => $previousOwner1->id,
        'new_owner_id' => $previousOwner2->id,
        'transferred_at' => now()->subDays(2),
    ]);

    // Newest transfer
    TicketOwnershipHistory::factory()->create([
        'ticket_id' => $ticket->id,
        'transaction_id' => $transaction2->id,
        'previous_owner_id' => $previousOwner2->id,
        'new_owner_id' => $owner->id,
        'transferred_at' => now()->subDay(),
    ]);

    DB::enableQueryLog();

    $response = actingAs($owner, 'sanctum')
        ->getJson("/api/v1/tickets/{$ticket->id}/history")
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'transaction_id',
                    'transferred_at',
                    'previous_owner' => ['id', 'name'],
                    'new_owner' => ['id', 'name'],
                ],
            ],
        ]);

    $data = $response->json('data');

    // Check sorting (descending)
    expect($data)->toHaveCount(2)
        ->and($data[0]['transaction_id'])->toBe($transaction2->id) // Newest first
        ->and($data[1]['transaction_id'])->toBe($transaction1->id); // Oldest last

    // Check PII is hidden
    expect($data[0]['previous_owner'])->not->toHaveKeys(['email', 'phone', 'address'])
        ->and($data[0]['new_owner'])->not->toHaveKeys(['email', 'phone', 'address']);

    // Check eager loading (should be 1 for ticket, 1 for histories, 2 for users (prev/new) = ~4 queries, definitely less than 10)
    expect(count(DB::getQueryLog()))->toBeLessThan(6);
});
