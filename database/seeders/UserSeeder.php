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

        User::create([
            'first_name' => 'Ana',
            'last_name' => 'Martínez',
            'cedula' => '11223344',
            'gender' => 'F',
            'mobile_phone' => '04169998877',
            'email' => 'ana@inventario.com',
            'username' => 'ana.martinez',
            'password' => Hash::make('auditor123'),
            'role' => 'auditor',
        ]);
    }
}
