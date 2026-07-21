<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear administrador de prueba
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

        // Crear usuario regular de prueba
        $this->regularUser = User::create([
            'first_name' => 'Regular',
            'last_name' => 'User',
            'cedula' => '87654321',
            'gender' => 'M',
            'email' => 'user@test.com',
            'username' => 'user.test',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
    }

    /** @test */
    public function test_login_authenticates_user_and_returns_token()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
            'user'
        ]);
    }

    /** @test */
    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_admin_can_register_user_with_auto_generated_credentials()
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/users', [
            'first_name' => 'Carlos',
            'last_name' => 'Gómez',
            'cedula' => '11223344',
            'gender' => 'M',
            'email' => 'carlos@test.com',
            'mobile_phone' => '04125555555',
            'role' => 'user'
        ]);

        $response->assertStatus(201);
        
        // El nombre de usuario esperado es 'carlos.gomez'
        $expectedUsername = 'carlos.gomez';

        $response->assertJsonFragment([
            'username' => $expectedUsername,
            'password_temporal' => '11223344'
        ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Carlos',
            'last_name' => 'Gómez',
            'cedula' => '11223344',
            'username' => $expectedUsername,
            'role' => 'user'
        ]);
    }

    /** @test */
    public function test_non_admin_cannot_register_user()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')->postJson('/api/users', [
            'first_name' => 'Carlos',
            'last_name' => 'Gómez',
            'cedula' => '11223344',
            'gender' => 'M',
            'email' => 'carlos@test.com',
            'role' => 'user'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_unauthenticated_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);
    }
}
