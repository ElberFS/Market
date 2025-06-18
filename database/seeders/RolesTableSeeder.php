<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; // Importa el modelo Role de Spatie

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desactiva la caché de permisos para que los nuevos roles estén disponibles inmediatamente
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Roles a crear
        $roles = [
            'administrador',
            'vendedor',
            'cliente',
        ];

        foreach ($roles as $roleName) {
            // Crea el rol si no existe. El guard_name por defecto es 'web'.
            Role::firstOrCreate(['name' => $roleName]);
            $this->command->info("Rol '{$roleName}' asegurado/creado.");
        }

        $this->command->info('Roles creados exitosamente o ya existentes.');
    }
}
