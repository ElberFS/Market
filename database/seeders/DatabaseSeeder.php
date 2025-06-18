<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Importa el modelo User
use Illuminate\Support\Facades\Hash; // Importa Hash para la contraseña

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Asegúrate de que el seeder de roles se ejecute primero
        $this->call(RolesTableSeeder::class);

        // Verifica si el usuario administrador ya existe para evitar duplicados
        $adminEmail = 'admin@example.com';
        if (!User::where('email', $adminEmail)->exists()) {
            $adminUser = User::create([
                'name' => 'Administrador Principal',
                'email' => $adminEmail,
                'password' => Hash::make('password'), // ¡Cambia 'password' a una contraseña segura en producción!
                'email_verified_at' => now(), // Marcarlo como verificado para evitar problemas iniciales
            ]);

            // Asigna el rol de 'administrador' al usuario
            $adminUser->assignRole('administrador');
            $this->command->info("Usuario '{$adminUser->name}' (Admin) creado y rol asignado.");
        } else {
            $this->command->info("Usuario administrador '{$adminEmail}' ya existe. Asignando rol si no lo tiene...");
            // Opcional: Asegúrate de que el usuario existente tenga el rol
            $existingAdmin = User::where('email', $adminEmail)->first();
            if (!$existingAdmin->hasRole('administrador')) {
                $existingAdmin->assignRole('administrador');
                $this->command->info("Rol 'administrador' asignado al usuario existente '{$existingAdmin->name}'.");
            }
        }


        // Opcional: Crear un usuario cliente para pruebas
        $clientEmail = 'client@example.com';
        if (!User::where('email', $clientEmail)->exists()) {
            $clientUser = User::create([
                'name' => 'Cliente de Prueba',
                'email' => $clientEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $clientUser->assignRole('cliente');
            $this->command->info("Usuario '{$clientUser->name}' (Cliente) creado y rol asignado.");
        } else {
             $this->command->info("Usuario cliente '{$clientEmail}' ya existe. Asignando rol si no lo tiene...");
             $existingClient = User::where('email', $clientEmail)->first();
             if (!$existingClient->hasRole('cliente')) {
                 $existingClient->assignRole('cliente');
                 $this->command->info("Rol 'cliente' asignado al usuario existente '{$existingClient->name}'.");
             }
        }

        // Si necesitas usuarios de ejemplo masivos, puedes usar factorías aquí:
        // User::factory(10)->create()->each(function ($user) {
        //     $user->assignRole('cliente'); // Asigna el rol 'cliente' por defecto a estos usuarios
        // });
        // $this->command->info("10 usuarios de ejemplo creados con rol 'cliente'.");
    }
}
