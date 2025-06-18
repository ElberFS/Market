<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue; // Puedes quitar esta línea si no vas a usar queues
use Illuminate\Queue\InteractsWithQueue;    // Puedes quitar esta línea si no vas a usar queues
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log; // Importa el facade Log

class AssignDefaultRoleToUser
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        // Obtén el rol 'cliente'
        $clientRole = Role::where('name', 'cliente')->first();

        // Si el rol 'cliente' existe y el usuario no tiene ningún rol asignado aún
        // NOTA: El trait HasRoles debe estar en el modelo App\Models\User.
        if ($clientRole && !$event->user->hasAnyRole()) {
            $event->user->assignRole($clientRole);
            Log::info("El rol 'cliente' asignado automáticamente al nuevo usuario: " . $event->user->email);
        } else if (!$clientRole) {
            Log::warning("El rol 'cliente' no se encontró al intentar asignar a un nuevo usuario. Asegúrate de haber ejecutado los seeders de roles.");
        }
    }
}
