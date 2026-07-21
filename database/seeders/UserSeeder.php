<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Administrador',
            'last_name' => 'Sistema',
            'cedula' => '12345678',
            'gender' => 'Otro',
            'mobile_phone' => '04120000000',
            'email' => 'admin@inventario.com',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // También podemos crear un usuario común de prueba
        User::create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'cedula' => '87654321',
            'gender' => 'M',
            'mobile_phone' => '04241112233',
            'email' => 'juan@inventario.com',
            'username' => 'juan.perez',
            'password' => Hash::make('user123'),
            'role' => 'user',
        ]);
    }
}
