<?php

namespace App\Livewire\Product;

use App\Models\Product; // Importar el modelo Product
use Livewire\Component;
use Livewire\WithPagination; // Para paginación

class ProductList extends Component
{
    use WithPagination;

    public $search = ''; // Propiedad para el término de búsqueda
    public $sortField = 'id'; // Campo por el cual ordenar
    public $sortDirection = 'asc'; // Dirección de la ordenación
    public $perPage = 10; // Cantidad de productos por página

    // Query string para mantener el estado de búsqueda y ordenación en la URL
    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    // Resetear la paginación cuando el término de búsqueda cambia
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Resetear la paginación cuando la cantidad por página cambia
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Método para cambiar el campo y la dirección de ordenación
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // Método para eliminar un producto
    public function deleteProduct($productId)
    {
        try {
            $product = Product::findOrFail($productId);

            // Opcional: Si quieres eliminar las imágenes físicas al eliminar el producto,
            // asegúrate de que esto se maneje a nivel de modelo con observers o que
            // las relaciones con onDelete('cascade') en la DB hagan el trabajo
            // (que ya lo hacen para product_images, pero no para archivos físicos).
            // Si las imágenes están en Storage, deberías eliminarlas manualmente aquí o en un observer:
            // foreach ($product->images as $image) {
            //     Storage::disk('public')->delete($image->image_path);
            //     if ($image->thumbnail_path) {
            //         Storage::disk('public')->delete($image->thumbnail_path);
            //     }
            // }

            $product->delete();
            session()->flash('message', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el producto: ' . $e->getMessage());
            // \Log::error('Error al eliminar producto: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Consulta los productos, cargando relaciones para mostrar nombre de categoría/marca
        $products = Product::query()
            ->with(['category', 'brand']) // Cargar relaciones Category y Brand
            ->when($this->search, function ($query) {
                // Filtrar por nombre, descripción corta, SKU
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('short_description', 'like', '%' . $this->search . '%')
                      ->orWhere('SKU', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection) // Aplicar ordenación
            ->paginate($this->perPage); // Aplicar paginación

        return view('livewire.product.product-list', [
            'products' => $products,
        ]);
    }
}