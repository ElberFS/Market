<?php

namespace App\Livewire\Admin;

use App\Models\User; // Importa el modelo User para interactuar con la tabla de usuarios
use Livewire\Component;
use Livewire\WithPagination; // Trait para habilitar la paginación de los resultados
use Spatie\Permission\Models\Role; // Importa el modelo Role de Spatie para gestionar roles

class UserManagement extends Component
{
    use WithPagination; // Habilita la funcionalidad de paginación para la tabla de usuarios

    // Propiedades públicas del componente, accesibles desde la vista y para el estado de Livewire
    public $search = ''; // Almacena el término de búsqueda ingresado por el usuario
    public $perPage = 10; // Define cuántos usuarios se mostrarán por página
    public $userIdToEdit = null; // Guarda el ID del usuario que se está editando en el modal
    public $selectedRoles = []; // Array para almacenar los roles seleccionados para un usuario
    public $allRoles = []; // Almacena todos los roles disponibles en el sistema (ej. 'administrador', 'vendedor', 'cliente')
    public $showingUserModal = false; // Bandera booleana para controlar la visibilidad del modal de edición de usuario

    // Configuración para que el término de búsqueda ($search) sea parte de la URL (query string)
    // 'except' => '' significa que el parámetro no aparecerá en la URL si está vacío
    protected $queryString = ['search' => ['except' => '']];

    // Reglas de validación para las propiedades del componente
    protected $rules = [
        'selectedRoles' => 'array', // Asegura que 'selectedRoles' sea un array
        'selectedRoles.*' => 'exists:roles,name', // Cada elemento en 'selectedRoles' debe corresponder a un 'name' existente en la tabla 'roles'
    ];

    /**
     * Método que se ejecuta una vez cuando el componente es inicializado.
     * Ideal para cargar datos iniciales que no cambian.
     */
    public function mount()
    {
        // Carga todos los nombres de roles disponibles en el sistema
        // Esto se hace una vez para que el selector de roles en el modal esté siempre disponible
        $this->allRoles = Role::pluck('name')->toArray();
    }

    /**
     * Método que se ejecuta en cada renderizado del componente.
     * Aquí se construyen los datos que se mostrarán en la vista, como la lista de usuarios.
     */
    public function render()
    {
        // Construye la consulta para obtener usuarios
        $users = User::query()
            // Condición para aplicar la búsqueda si el término $search no está vacío
            ->when($this->search, function ($query) {
                // Busca usuarios cuyo nombre o email contengan el término de búsqueda
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->with('roles') // Carga eager la relación 'roles' para evitar el problema N+1 y mostrar los roles de cada usuario
            ->paginate($this->perPage); // Pagina los resultados según el número de elementos por página

        // Retorna la vista de Livewire con los usuarios paginados
        return view('livewire.admin.user-management', [
            'users' => $users,
        ]);
    }

    /**
     * Método para abrir el modal de edición de roles de un usuario específico.
     *
     * @param User $user El modelo de usuario que se va a editar (Livewire resuelve el ID automáticamente).
     */
    public function editRoles(User $user)
    {
        $this->userIdToEdit = $user->id; // Guarda el ID del usuario para futuras operaciones (ej. guardar)
        // Carga los nombres de los roles actuales del usuario en la propiedad selectedRoles.
        // Esto preselecciona los checkboxes de los roles que el usuario ya tiene.
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->showingUserModal = true; // Hace visible el modal en la interfaz
    }

    /**
     * Método para guardar los roles actualizados de un usuario.
     */
    public function saveRoles()
    {
        $this->validate(); // Ejecuta las reglas de validación definidas en $rules

        // Encuentra al usuario por su ID. Si no lo encuentra, lanzará una excepción 404.
        $user = User::findOrFail($this->userIdToEdit);

        // Sincroniza los roles del usuario con los roles seleccionados en el modal.
        // Esto asegura que el usuario tenga exactamente los roles presentes en $this->selectedRoles,
        // eliminando los antiguos que no estén en la selección y añadiendo los nuevos.
        $user->syncRoles($this->selectedRoles);

        // Envía un mensaje de éxito a la sesión que puede ser mostrado en la vista
        session()->flash('message', 'Roles de usuario actualizados con éxito.');

        // Resetea las propiedades relacionadas con el modal para cerrarlo y limpiar el estado
        $this->reset(['showingUserModal', 'userIdToEdit', 'selectedRoles']);
    }

    /**
     * Se ejecuta automáticamente cada vez que la propiedad $search cambia.
     * Utilizado para reiniciar la paginación cuando se realiza una nueva búsqueda.
     */
    public function updatedSearch()
    {
        $this->resetPage(); // Vuelve a la primera página de resultados
    }

    /**
     * Método para cerrar el modal de edición de usuario sin guardar cambios.
     * Resetea el estado del modal y las propiedades relacionadas.
     */
    public function closeUserModal()
    {
        $this->reset(['showingUserModal', 'userIdToEdit', 'selectedRoles']); // Cierra el modal y limpia las selecciones
    }
}