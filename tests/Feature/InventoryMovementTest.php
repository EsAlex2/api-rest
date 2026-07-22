<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Status;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryMovementTest extends TestCase
{
    use RefreshDatabase;

    private $category;
    private $supplier;
    private $statusAvailable;
    private $statusOut;
    private $statusLow;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolesSeeder::class);

        $this->user = \App\Models\User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'cedula' => '99999999',
            'gender' => 'Otro',
            'email' => 'test@test.com',
            'username' => 'test.user',
            'password' => 'password123',
            'role' => 'user'
        ]);

        // Crear datos base
        $this->category = Category::create([
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@test.com'
        ]);

        $this->statusAvailable = Status::create([
            'name_status' => 'Disponible',
            'description' => 'Disponible'
        ]);

        $this->statusOut = Status::create([
            'name_status' => 'Agotado',
            'description' => 'Agotado'
        ]);

        $this->statusLow = Status::create([
            'name_status' => 'Stock Bajo',
            'description' => 'Stock Bajo'
        ]);
    }

    /** @test */
    public function test_it_can_register_an_in_movement_and_increases_stock()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 10.00,
            'stock' => 5,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'status_id' => $this->statusLow->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 10,
            'reason' => 'Restocking'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 15,
            'status_id' => $this->statusAvailable->id // Stock 15 > min_stock 10
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 10,
            'reason' => 'Restocking'
        ]);
    }

    /** @test */
    public function test_it_can_register_an_out_movement_and_decreases_stock()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 10.00,
            'stock' => 15,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'status_id' => $this->statusAvailable->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 6,
            'reason' => 'Sale'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 9,
            'status_id' => $this->statusLow->id // Stock 9 <= min_stock 10
        ]);
    }

    /** @test */
    public function test_it_fails_out_movement_when_stock_is_insufficient()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 10.00,
            'stock' => 5,
            'min_stock' => 2,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'status_id' => $this->statusAvailable->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 6,
            'reason' => 'Sale'
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 5
        ]);
    }

    /** @test */
    public function test_it_can_register_an_adjustment_movement_and_overwrites_stock()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 10.00,
            'stock' => 15,
            'min_stock' => 5,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'status_id' => $this->statusAvailable->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 0,
            'reason' => 'Manual write-off'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 0,
            'status_id' => $this->statusOut->id // Stock 0 -> Agotado
        ]);
    }
}
