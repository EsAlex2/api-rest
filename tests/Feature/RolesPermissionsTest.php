<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Status;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class RolesPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $inventoryManager;
    private User $auditor;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed Roles and Permissions
        $this->seed(\Database\Seeders\RolesSeeder::class);

        // 2. Create Users with different roles
        $this->admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'cedula' => '12345678',
            'gender' => 'Otro',
            'email' => 'admin@test.com',
            'username' => 'admin.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->inventoryManager = User::create([
            'first_name' => 'Manager',
            'last_name' => 'User',
            'cedula' => '87654321',
            'gender' => 'M',
            'email' => 'manager@test.com',
            'username' => 'manager.test',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $this->auditor = User::create([
            'first_name' => 'Auditor',
            'last_name' => 'User',
            'cedula' => '11223344',
            'gender' => 'F',
            'email' => 'auditor@test.com',
            'username' => 'auditor.test',
            'password' => Hash::make('password123'),
            'role' => 'auditor',
        ]);
    }

    /** @test */
    public function test_admin_can_manage_roles_and_permissions()
    {
        // List roles
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/roles');
        $response->assertStatus(200);
        $response->assertJsonCount(3); // admin, user, auditor

        // List permissions
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/permissions');
        $response->assertStatus(200);
        $response->assertJsonCount(9);

        // Create a new role
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/roles', [
            'name_role' => 'supervisor',
            'description' => 'Supervisor de Almacén',
            'permissions' => [
                Permission::where('name_permission', 'products.view')->first()->id,
                Permission::where('name_permission', 'movements.view')->first()->id,
            ]
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', ['name_role' => 'supervisor']);

        // Update role
        $supervisor = Role::where('name_role', 'supervisor')->first();
        $response = $this->actingAs($this->admin, 'sanctum')->putJson("/api/roles/{$supervisor->id}", [
            'description' => 'Supervisor Modificado',
            'permissions' => [
                Permission::where('name_permission', 'products.view')->first()->id,
                Permission::where('name_permission', 'movements.view')->first()->id,
                Permission::where('name_permission', 'movements.manage')->first()->id,
            ]
        ]);
        $response->assertStatus(200);
        $this->assertEquals('Supervisor Modificado', $supervisor->fresh()->description);

        // Delete role
        $response = $this->actingAs($this->admin, 'sanctum')->deleteJson("/api/roles/{$supervisor->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('roles', ['name_role' => 'supervisor']);
    }

    /** @test */
    public function test_non_admin_cannot_access_roles_endpoints()
    {
        $response = $this->actingAs($this->inventoryManager, 'sanctum')->getJson('/api/roles');
        $response->assertStatus(403);

        $response = $this->actingAs($this->auditor, 'sanctum')->getJson('/api/roles');
        $response->assertStatus(403);
    }

    /** @test */
    public function test_inventory_manager_can_manage_products_but_auditor_cannot()
    {
        // Setup category, supplier, status
        $category = Category::create(['name' => 'Cat', 'description' => 'Desc']);
        $supplier = Supplier::create(['name' => 'Sup', 'email' => 'sup@test.com']);
        $status = Status::create(['name_status' => 'Disponible', 'description' => 'Disp']);

        // 1. Inventory manager creates product -> Allowed
        $response = $this->actingAs($this->inventoryManager, 'sanctum')->postJson('/api/products', [
            'name' => 'Product Manager',
            'sku' => 'PROD-MGR-001',
            'price' => 100.00,
            'stock' => 10,
            'min_stock' => 2,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $response->assertStatus(201);

        // 2. Auditor tries to create product -> Forbidden
        $response = $this->actingAs($this->auditor, 'sanctum')->postJson('/api/products', [
            'name' => 'Product Auditor',
            'sku' => 'PROD-AUD-002',
            'price' => 100.00,
            'stock' => 10,
            'min_stock' => 2,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $response->assertStatus(403);

        // 3. Auditor can view product -> Allowed
        $product = Product::where('sku', 'PROD-MGR-001')->first();
        $response = $this->actingAs($this->auditor, 'sanctum')->getJson("/api/products/{$product->id}");
        $response->assertStatus(200);
    }
}
