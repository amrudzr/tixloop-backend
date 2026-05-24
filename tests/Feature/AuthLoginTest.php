<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('authenticates a user and returns a token', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'email_verified_at',
                    'created_at',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => 'john@example.com',
                ],
            ],
        ]);
});

it('returns 422 when credentials are invalid', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when user does not exist', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when email is missing', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when password is missing', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('returns 422 when all fields are missing', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

it('returns 422 when email format is invalid', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'not-an-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('does not expose whether email exists on failed login', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $existingEmailResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'wrongpassword',
    ]);

    $nonExistingEmailResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'wrongpassword',
    ]);

    // Both should return the same generic error to prevent user enumeration
    expect($existingEmailResponse->json('errors.email'))
        ->toEqual($nonExistingEmailResponse->json('errors.email'));
});

it('returns 429 when login attempts exceed rate limit', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    // Exhaust 5 allowed attempts
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);
    }

    // 6th attempt should be throttled
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(429)
        ->assertJson([
            'success' => false,
            'message' => 'Too many login attempts. Please try again later.',
        ]);
});

it('allows login again after rate limit cooldown', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    // Exhaust 5 allowed attempts
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);
    }

    // Verify throttled
    $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ])->assertStatus(429);

    // Travel forward 61 seconds to clear the per-minute window
    $this->travel(61)->seconds();

    // Should succeed now
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Login successful',
        ]);
});
