<?php

namespace App\Livewire\Category;

use App\Models\Category; // Importa el modelo Category para interactuar con la tabla de categorías
use Livewire\Component;
use Livewire\WithPagination; // Trait para habilitar la paginación

class CategoryList extends Component
{
    use WithPagination; // Habilita la paginación para la lista de categorías

    // Propiedades públicas para el estado del componente
    public $search = ''; // Término de búsqueda para filtrar categorías
    public $sortField = 'id'; // Campo por el cual se ordenarán las categorías
    public $sortDirection = 'asc'; // Dirección de la ordenación (ascendente/descendente)

    // Configuración para que las propiedades de búsqueda y ordenación se reflejen en la URL
    protected $queryString = [
        'search' => ['except' => ''], // 'search' no aparece en la URL si está vacío
        'sortField' => ['except' => 'id'], // 'sortField' no aparece si es 'id'
        'sortDirection' => ['except' => 'asc'], // 'sortDirection' no aparece si es 'asc'
    ];

    /**
     * Hook de Livewire que se ejecuta antes de que la propiedad 'search' sea actualizada.
     * Resetea la paginación al cambiar el término de búsqueda.
     */
    public function updatingSearch()
    {
        $this->resetPage(); // Vuelve a la primera página de resultados
    }

    /**
     * Cambia el campo y la dirección de ordenación.
     * Si se hace clic en el mismo campo, alterna la dirección.
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc'; // Por defecto, ordena ascendentemente al cambiar de campo
        }
        $this->sortField = $field; // Establece el nuevo campo de ordenación
    }

    /**
     * Elimina una categoría de la base de datos.
     *
     * @param int $categoryId El ID de la categoría a eliminar.
     */
    public function deleteCategory($categoryId)
    {
        try {
            $category = Category::findOrFail($categoryId); // Busca la categoría o lanza un 404
            $category->delete(); // Elimina la categoría
            session()->flash('message', 'Categoría eliminada exitosamente.'); // Mensaje de éxito
        } catch (\Exception $e) {
            // Maneja cualquier error durante la eliminación
            session()->flash('error', 'Error al eliminar la categoría: ' . $e->getMessage());
        }
    }

    /**
     * Renderiza la vista del componente con la lista de categorías.
     */
    public function render()
    {
        // Construye la consulta para obtener categorías
        $categories = Category::query()
            ->with('parent') // Carga eager la relación 'parent' para mostrar el nombre de la categoría padre
            // Aplica el filtro de búsqueda si el término $search no está vacío
            ->when($this->search, function ($query) {
                // Busca categorías cuyo nombre o descripción contengan el término de búsqueda
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            // Aplica la ordenación según $sortField y $sortDirection
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10); // Pagina los resultados, mostrando 10 categorías por página

        // Retorna la vista con las categorías paginadas
        return view('livewire.category.category-list', [
            'categories' => $categories,
        ]);
    }
}