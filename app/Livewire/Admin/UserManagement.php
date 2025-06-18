<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination; // Para paginar la lista de usuarios
use Spatie\Permission\Models\Role; // Para obtener los roles

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $userIdToEdit = null;
    public $selectedRoles = [];
    public $allRoles = [];
    public $showingUserModal = false; // Para controlar la visibilidad del modal

    protected $queryString = ['search' => ['except' => '']];

    // Reglas de validación para los roles
    // Asegúrate de que selectedRoles contenga nombres de roles válidos
    protected $rules = [
        'selectedRoles' => 'array',
        'selectedRoles.*' => 'exists:roles,name', // Cada elemento en selectedRoles debe existir en la tabla roles.name
    ];

    public function mount()
    {
        // Carga todos los roles disponibles una sola vez al cargar el componente
        $this->allRoles = Role::pluck('name')->toArray();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->with('roles') // Carga los roles de cada usuario para mostrarlos
            ->paginate($this->perPage);

        return view('livewire.admin.user-management', [
            'users' => $users,
        ]);
    }

    // Método para abrir el modal de edición de roles
    public function editRoles(User $user)
    {
        $this->userIdToEdit = $user->id;
        // Carga los roles actuales del usuario en el array de selección
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->showingUserModal = true;
    }

    // Método para guardar los roles del usuario
    public function saveRoles()
    {
        // Valida que selectedRoles sea un array y que los nombres de los roles existan
        $this->validate();

        $user = User::findOrFail($this->userIdToEdit);

        // Sincroniza los roles del usuario con los seleccionados
        // Esto elimina los que ya no están y añade los nuevos
        $user->syncRoles($this->selectedRoles);

        session()->flash('message', 'Roles de usuario actualizados con éxito.');
        $this->reset(['showingUserModal', 'userIdToEdit', 'selectedRoles']); // Cierra modal y limpia variables
    }

    // Método para resetear la búsqueda y paginación al cambiar el término de búsqueda
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Método para cerrar el modal sin guardar cambios
    public function closeUserModal()
    {
        $this->reset(['showingUserModal', 'userIdToEdit', 'selectedRoles']);
    }
}
