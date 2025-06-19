<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryForm extends Component
{
    // Propiedades del componente que representan los campos del formulario
    public $categoryId;
    public $name;
    public $slug;
    public $description;
    public $parentId; // ID de la categoría padre
    public $isActive = true; // Estado de la categoría

    // Propiedad para almacenar las categorías disponibles para ser padres
    public $availableParentCategories;

    /**
     * Define las reglas de validación para las propiedades del formulario.
     */
    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Validación de unicidad para el nombre, ignorando la categoría actual si se está editando
                $this->categoryId
                    ? Rule::unique('categories', 'name')->ignore($this->categoryId)
                    : 'unique:categories,name',
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                // Validación de unicidad para el slug, ignorando la categoría actual
                Rule::unique('categories', 'slug')->ignore($this->categoryId),
            ],
            'description' => 'nullable|string',
            'parentId' => 'nullable|exists:categories,id', // Verifica que el parentId exista en la tabla categories
            'isActive' => 'boolean',
        ];
    }

    /**
     * Define los mensajes personalizados para las reglas de validación.
     */
    protected $messages = [
        'name.required' => 'El nombre de la categoría es obligatorio.',
        'name.unique' => 'Ya existe una categoría con este nombre.',
        'slug.required' => 'El slug es obligatorio.',
        'slug.unique' => 'Ya existe un slug idéntico. Intenta cambiar el nombre de la categoría.',
        'parentId.exists' => 'La categoría padre seleccionada no es válida.',
    ];

    /**
     * Se ejecuta una vez al inicializar el componente.
     * Carga los datos de la categoría si se está editando y las categorías padre disponibles.
     */
    public function mount($categoryId = null)
    {
        // Carga las categorías que pueden ser padres, excluyendo la categoría actual (si existe)
        // para evitar que una categoría sea su propia padre o un ancestro.
        $this->availableParentCategories = Category::when($categoryId, function ($query) use ($categoryId) {
            $query->where('id', '!=', $categoryId);
        })->get();

        // Si se proporciona un categoryId, significa que estamos en modo edición
        if ($categoryId) {
            $this->categoryId = $categoryId;
            $category = Category::findOrFail($categoryId); // Busca la categoría o lanza un 404

            // Asigna los valores de la categoría a las propiedades del componente
            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->description = $category->description;
            $this->parentId = $category->parent_id;
            $this->isActive = $category->is_active;
        }
    }

    /**
     * Se ejecuta cada vez que la propiedad 'name' es actualizada.
     * Genera automáticamente el slug a partir del nombre.
     */
    public function updatedName($value)
    {
        $this->slug = Str::slug($value); // Convierte el nombre en un slug amigable para URL
        $this->validateOnly('slug'); // Valida solo el slug para asegurar su unicidad en tiempo real
    }

    /**
     * Guarda una nueva categoría o actualiza una existente.
     */
    public function saveCategory()
    {
        $this->validate(); // Valida todas las propiedades del formulario

        // Prepara los datos para guardar/actualizar
        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parentId,
            'is_active' => $this->isActive,
        ];

        try {
            if ($this->categoryId) {
                // Actualiza una categoría existente
                Category::find($this->categoryId)->update($data);
                session()->flash('message', 'Categoría actualizada exitosamente.');
            } else {
                // Crea una nueva categoría
                Category::create($data);
                session()->flash('message', 'Categoría creada exitosamente.');
                // Resetea los campos del formulario después de crear una nueva categoría
                $this->reset(['name', 'slug', 'description', 'parentId', 'isActive']);
            }
            // Redirige al usuario a la lista de categorías después de guardar
            return redirect()->route('admin.categories.index');
        } catch (\Exception $e) {
            // Captura y muestra errores durante el proceso de guardado
            session()->flash('error', 'Error al guardar la categoría: ' . $e->getMessage());
        }
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        return view('livewire.category.category-form');
    }
}