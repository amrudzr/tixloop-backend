<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a new user and returns a token', function () {
    $payload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '08123456789',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/v1/auth/register', $payload);

    $response->assertStatus(201)
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
            'message' => 'Registration successful',
            'data' => [
                'token_type' => 'Bearer',
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'phone' => '08123456789',
                ],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '08123456789',
    ]);
});

it('returns 422 when required fields are missing', function () {
    $response = $this->postJson('/api/v1/auth/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'phone', 'password']);
});

it('returns 422 when email is already taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'taken@example.com',
        'phone' => '08123456789',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when phone is already taken', function () {
    User::factory()->create(['phone' => '08123456789']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '08123456789',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

it('returns 422 when password confirmation does not match', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '08123456789',
        'password' => 'password123',
        'password_confirmation' => 'wrongconfirm',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('returns 422 when password is too short', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '08123456789',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
