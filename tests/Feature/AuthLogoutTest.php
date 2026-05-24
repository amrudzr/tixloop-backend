<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('revokes the current token and returns success', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
});

it('only revokes the current token, not other tokens', function () {
    $user = User::factory()->create();

    // Create two tokens
    $user->createToken('device_a');
    $tokenB = $user->createToken('device_b');

    // Authenticate with token B
    Sanctum::actingAs($user, [], 'sanctum');

    // Simulate the token B being the current one
    $this->postJson('/api/v1/auth/logout');

    // User should still have at least one token remaining (device_a)
    expect($user->tokens()->where('name', 'device_a')->count())->toBe(1);
});

it('returns 401 when unauthenticated', function () {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(401);
});

it('returns 401 when using an invalid bearer token', function () {
    $response = $this->postJson('/api/v1/auth/logout', [], [
        'Authorization' => 'Bearer invalid-token-here',
    ]);

    $response->assertStatus(401);
});
