<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('registers a user and returns a bearer access token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('user.name', 'John Doe')
        ->assertJsonPath('user.email', 'john@example.test')
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
            'access_token',
            'token_type',
        ]);

    $user = User::query()->where('email', 'john@example.test')->firstOrFail();

    expect(Hash::check('password123', $user->password))->toBeTrue();
    expect($response->json('access_token'))->toBeString()->not->toBe('');

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
    ]);
});

it('returns validation errors when registering a duplicate email address', function () {
    $existingUser = User::factory()->create([
        'email' => 'john@example.test',
    ]);

    $response = $this->postJson('/api/register', [
        'name' => 'Another John',
        'email' => $existingUser->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('message', 'The email has already been taken.')
        ->assertJsonValidationErrors(['email']);
});

it('logs in with valid credentials and returns a bearer access token', function () {
    $user = User::factory()->create([
        'email' => 'john@example.test',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonStructure(['access_token', 'token_type']);

    expect($response->json('access_token'))->toBeString()->not->toBe('');

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
    ]);
});

it('rejects invalid login credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Invalid credentials']);

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('logs out by revoking the current bearer token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth-api-test')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/logout');

    $response
        ->assertOk()
        ->assertExactJson(['message' => 'Logged out successfully']);

    $this->assertDatabaseCount('personal_access_tokens', 0);

    $this->withToken($token)
        ->getJson('/api/users')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});

it('requires a valid bearer token to log out', function () {
    $this->postJson('/api/logout')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});
