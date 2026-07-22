<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear Permisos
        $permissions = [
            'users.view' => 'Ver usuarios del sistema',
            'users.manage' => 'Crear, editar y eliminar usuarios',
            'products.view' => 'Ver catálogo de productos y stock',
            'products.manage' => 'Crear, editar y eliminar productos',
            'movements.view' => 'Ver historial de movimientos de inventario',
            'movements.manage' => 'Registrar movimientos de entrada, salida y ajuste',
            'categories.manage' => 'Gestionar categorías',
            'suppliers.manage' => 'Gestionar proveedores',
            'statuses.manage' => 'Gestionar estados',
        ];

        $permissionModels = [];
        foreach ($permissions as $name => $description) {
            $permissionModels[$name] = Permission::firstOrCreate(
                ['name_permission' => $name],
                ['description' => $description]
            );
        }

        // 2. Crear Roles
        $adminRole = Role::firstOrCreate(
            ['name_role' => 'admin'],
            ['description' => 'Administrador con acceso total al sistema.']
        );

        $userRole = Role::firstOrCreate(
            ['name_role' => 'user'],
            ['description' => 'Gestor de inventario con permisos para productos, movimientos y tablas maestras.']
        );

        $auditorRole = Role::firstOrCreate(
            ['name_role' => 'auditor'],
            ['description' => 'Auditor con acceso de solo lectura al inventario y movimientos.']
        );

        // 3. Relacionar Roles y Permisos
        // El administrador tiene TODOS los permisos
        $adminRole->permissions()->sync(
            collect($permissionModels)->pluck('id')->toArray()
        );

        // El usuario gestor de inventario tiene permisos operativos pero NO de usuarios
        $userRole->permissions()->sync([
            $permissionModels['products.view']->id,
            $permissionModels['products.manage']->id,
            $permissionModels['movements.view']->id,
            $permissionModels['movements.manage']->id,
            $permissionModels['categories.manage']->id,
            $permissionModels['suppliers.manage']->id,
            $permissionModels['statuses.manage']->id,
        ]);

        // El auditor solo tiene acceso de lectura a productos y movimientos
        $auditorRole->permissions()->sync([
            $permissionModels['products.view']->id,
            $permissionModels['movements.view']->id,
        ]);
    }
}
