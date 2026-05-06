<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles del sistema
        foreach (['administrador', 'comprador', 'vendedor'] as $rol) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
        }

        // Sucursales
        $islaDeMailpo = Sucursal::firstOrCreate(
            ['nombre' => 'Isla de Maipo'],
            ['direccion' => 'Isla de Maipo, Región Metropolitana', 'activa' => true]
        );
        Sucursal::firstOrCreate(
            ['nombre' => 'Las Condes'],
            ['direccion' => 'Las Condes, Región Metropolitana', 'activa' => true]
        );

        // Usuario administrador inicial
        $admin = User::firstOrCreate(
            ['email' => 'admin@floreria.cl'],
            [
                'nombre'               => 'Administrador',
                'apellido'             => 'Sistema',
                'password'             => bcrypt('Floreria2024!'),
                'must_change_password' => false,
                'activo'               => true,
            ]
        );
        $admin->syncRoles(['administrador']);
        $admin->sucursales()->syncWithoutDetaching([$islaDeMailpo->id]);
    }
}
