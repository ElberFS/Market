<?php

namespace App\Livewire\Product;

use App\Models\Product; // Importa el modelo Product
use Livewire\Component;
use Livewire\WithPagination; // Habilita la paginación

class ProductList extends Component
{
    use WithPagination;

    // Propiedades para la búsqueda, ordenación y paginación
    public $search = '';
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Configura la URL para reflejar el estado de búsqueda y ordenación
    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    /**
     * Hook de Livewire: Se ejecuta antes de actualizar 'search'.
     * Reinicia la paginación para una nueva búsqueda.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Hook de Livewire: Se ejecuta antes de actualizar 'perPage'.
     * Reinicia la paginación al cambiar la cantidad de elementos por página.
     */
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    /**
     * Cambia el campo y la dirección de ordenación.
     * Si el campo es el mismo, invierte la dirección. Si es diferente, ordena ascendentemente.
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    /**
     * Elimina un producto de la base de datos.
     *
     * @param int $productId El ID del producto a eliminar.
     */
    public function deleteProduct($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            // Considerar eliminar imágenes físicas aquí o mediante un Observer en el modelo Product
            $product->delete();
            session()->flash('message', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Renderiza la vista del componente con la lista paginada de productos.
     */
    public function render()
    {
        // Consulta los productos, cargando sus categorías y marcas
        $products = Product::query()
            ->with(['category', 'brand'])
            // Aplica filtros de búsqueda si 'search' tiene un valor
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('short_description', 'like', '%' . $this->search . '%')
                      ->orWhere('SKU', 'like', '%' . $this->search . '%');
            })
            // Aplica la ordenación
            ->orderBy($this->sortField, $this->sortDirection)
            // Aplica la paginación
            ->paginate($this->perPage);

        return view('livewire.product.product-list', [
            'products' => $products,
        ]);
    }
}