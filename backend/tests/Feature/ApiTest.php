<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_returns_a_sanctum_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'john@example.com')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['access_token']);
    }

    public function test_product_writes_require_an_elevated_access_level(): void
    {
        $member = User::factory()->create();
        $this->actingAs($member, 'sanctum')
            ->postJson('/api/products', ['name' => 'Laptop', 'price' => 100, 'stock' => 1])
            ->assertForbidden()
            ->assertJsonPath('message', 'Unauthorized');

        $admin = User::factory()->create();
        $admin->forceFill(['access_level' => 1])->save();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/products', ['name' => 'Laptop', 'price' => 100, 'stock' => 1])
            ->assertCreated()
            ->assertJsonPath('name', 'Laptop');

        $this->assertDatabaseHas('products', ['name' => 'Laptop']);
    }

    public function test_product_detail_requires_an_elevated_access_level(): void
    {
        $product = Product::factory()->create();
        $member = User::factory()->create();

        $this->actingAs($member, 'sanctum')
            ->getJson("/api/products/{$product->id}")
            ->assertForbidden();
    }
}
