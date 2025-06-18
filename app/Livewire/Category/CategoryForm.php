<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryForm extends Component
{
    public $categoryId;
    public $name;
    public $slug;
    public $description;
    public $parentId;
    public $isActive = true;

    public $availableParentCategories;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $this->categoryId
                    ? Rule::unique('categories', 'name')->ignore($this->categoryId)
                    : 'unique:categories,name',
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($this->categoryId),
            ],
            'description' => 'nullable|string',
            'parentId' => 'nullable|exists:categories,id',
            'isActive' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre de la categoría es obligatorio.',
        'name.unique' => 'Ya existe una categoría con este nombre.',
        'slug.required' => 'El slug es obligatorio.',
        'slug.unique' => 'Ya existe un slug idéntico. Intenta cambiar el nombre de la categoría.',
        'parentId.exists' => 'La categoría padre seleccionada no es válida.',
    ];

    public function mount($categoryId = null)
    {
        // Se excluye la categoría actual de las opciones de padres para evitar auto-anidamiento
        $this->availableParentCategories = Category::when($categoryId, function ($query) use ($categoryId) {
            $query->where('id', '!=', $categoryId);
        })->get();

        if ($categoryId) {
            $this->categoryId = $categoryId;
            $category = Category::findOrFail($categoryId);

            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->description = $category->description;
            $this->parentId = $category->parent_id;
            $this->isActive = $category->is_active;
        }
    }

    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
        $this->validateOnly('slug');
    }

    public function saveCategory()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parentId,
            'is_active' => $this->isActive,
        ];

        try {
            if ($this->categoryId) {
                Category::find($this->categoryId)->update($data);
                session()->flash('message', 'Categoría actualizada exitosamente.');
            } else {
                Category::create($data);
                session()->flash('message', 'Categoría creada exitosamente.');
                $this->reset(['name', 'slug', 'description', 'parentId', 'isActive']);
            }
            // ¡Línea corregida!
            return redirect()->route('admin.categories.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar la categoría: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.category.category-form');
    }
}