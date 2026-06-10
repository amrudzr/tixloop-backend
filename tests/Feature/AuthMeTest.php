<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns the authenticated user profile', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'phone',
                'email_verified_at',
                'created_at',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Berhasil',
            'data' => [
                'id' => $user->id,
                'email' => 'jane@example.com',
            ],
        ]);
});

it('does not expose sensitive fields in user profile', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonMissing(['password'])
        ->assertJsonMissing(['remember_token']);
});

it('returns 401 when unauthenticated', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(401);
});
