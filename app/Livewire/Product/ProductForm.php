<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductImage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProductForm extends Component
{
    use WithFileUploads; // Habilita la subida de archivos para Livewire

    // Propiedades del formulario que corresponden a los campos de un producto
    public $productId;
    public $name;
    public $slug;
    public $description;
    public $short_description;
    public $price;
    public $old_price;
    public $SKU;
    public $stock;
    public $category_id;
    public $brand_id;
    public $is_active = true;
    public $is_featured = false;

    // Propiedades para la gestión de imágenes
    public $newImages = [];       // Archivos de imagen recién subidos
    public $existingImages = [];  // Datos de imágenes ya asociadas al producto
    public $imageDeleteIds = [];  // IDs de imágenes existentes marcadas para eliminar

    // Datos para los selectores (dropdowns) en el formulario
    public $categories;
    public $brands;

    /**
     * Define las reglas de validación para las propiedades del formulario.
     */
    protected function rules()
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                // Validación única del nombre, ignorando el producto actual si se está editando
                $this->productId ? Rule::unique('products', 'name')->ignore($this->productId) : 'unique:products,name',
            ],
            'slug' => [
                'required', 'string', 'max:255',
                // Validación única del slug, ignorando el producto actual
                Rule::unique('products', 'slug')->ignore($this->productId),
            ],
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0.01',
            'old_price' => 'nullable|numeric|min:0.01|gt:price',
            'SKU' => [
                'nullable', 'string', 'max:255',
                // Validación única del SKU, ignorando el producto actual
                $this->productId ? Rule::unique('products', 'SKU')->ignore($this->productId) : 'unique:products,SKU',
            ],
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'newImages.*' => 'nullable|image|max:2048', // Validación para cada archivo de imagen
        ];
    }

    /**
     * Define los mensajes personalizados para las reglas de validación.
     */
    protected $messages = [
        'name.required' => 'El nombre del producto es obligatorio.',
        'name.unique' => 'Ya existe un producto con este nombre.',
        'slug.required' => 'El slug del producto es obligatorio.',
        'slug.unique' => 'Ya existe un slug idéntico. Intenta cambiar el nombre del producto.',
        'description.required' => 'La descripción es obligatoria.',
        'price.required' => 'El precio es obligatorio.',
        'price.numeric' => 'El precio debe ser un número.',
        'price.min' => 'El precio debe ser al menos 0.01.',
        'old_price.gt' => 'El precio anterior debe ser mayor que el precio actual.',
        'SKU.unique' => 'Ya existe un SKU idéntico.',
        'stock.required' => 'El stock es obligatorio.',
        'stock.integer' => 'El stock debe ser un número entero.',
        'stock.min' => 'El stock no puede ser negativo.',
        'category_id.required' => 'La categoría es obligatoria.',
        'category_id.exists' => 'La categoría seleccionada no es válida.',
        'brand_id.exists' => 'La marca seleccionada no es válida.',
        'newImages.*.image' => 'Cada archivo debe ser una imagen.',
        'newImages.*.max' => 'Cada imagen no debe pesar más de 2MB.',
    ];

    /**
     * Se ejecuta una vez al inicializar el componente.
     * Carga datos iniciales (categorías, marcas) y el producto si se está editando.
     */
    public function mount($productId = null)
    {
        // Cargar datos para los selectores del formulario
        $this->categories = Category::orderBy('name')->get();
        $this->brands = Brand::orderBy('name')->get();

        // Si hay un ID de producto, cargar el producto existente para edición
        if ($productId) {
            $this->productId = $productId;
            $product = Product::with('images')->findOrFail($productId); // Cargar el producto con sus imágenes relacionadas

            // Asignar los atributos del producto a las propiedades del componente
            $this->name = $product->name;
            $this->slug = $product->slug;
            $this->description = $product->description;
            $this->short_description = $product->short_description;
            $this->price = $product->price;
            $this->old_price = $product->old_price;
            $this->SKU = $product->SKU;
            $this->stock = $product->stock;
            $this->category_id = $product->category_id;
            $this->brand_id = $product->brand_id;
            $this->is_active = (bool) $product->is_active;
            $this->is_featured = (bool) $product->is_featured;

            // Cargar las imágenes existentes del producto
            $this->existingImages = $product->images->toArray();
        }
    }

    /**
     * Se ejecuta automáticamente cuando la propiedad 'name' cambia.
     * Genera el slug a partir del nombre y valida solo el slug.
     */
    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
        $this->validateOnly('slug');
    }

    /**
     * Se ejecuta automáticamente cuando se seleccionan nuevas imágenes.
     * Valida cada nueva imagen al ser seleccionada.
     */
    public function updatedNewImages()
    {
        $this->validateOnly('newImages.*');
    }

    /**
     * Alterna el estado de una imagen existente para ser eliminada.
     */
    public function markImageForDeletion($imageId)
    {
        if (($key = array_search($imageId, $this->imageDeleteIds)) !== false) {
            unset($this->imageDeleteIds[$key]); // Desmarcar
        } else {
            $this->imageDeleteIds[] = $imageId; // Marcar
        }
        $this->imageDeleteIds = array_values($this->imageDeleteIds); // Reindexar array
    }

    /**
     * Elimina una imagen de la lista de 'existingImages' en el DOM y la marca para eliminación.
     */
    public function removeExistingImage($imageId)
    {
        $this->existingImages = array_filter($this->existingImages, fn($image) => $image['id'] != $imageId);
        $this->markImageForDeletion($imageId); // Asegurarse de que también se marque para DB
    }

    /**
     * Elimina una nueva imagen de la lista de 'newImages' antes de guardar.
     */
    public function removeNewImage($key)
    {
        unset($this->newImages[$key]);
        $this->newImages = array_values($this->newImages); // Reindexar array
    }

    /**
     * Guarda o actualiza un producto, incluyendo la gestión de imágenes.
     */
    public function saveProduct()
    {
        $this->validate(); // Ejecuta las reglas de validación

        // Prepara los datos del producto
        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'SKU' => $this->SKU,
            'stock' => $this->stock,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
        ];

        try {
            if ($this->productId) {
                // Actualiza un producto existente
                $product = Product::find($this->productId);
                $product->update($data);
                session()->flash('message', 'Producto actualizado exitosamente.');
            } else {
                // Crea un nuevo producto
                $product = Product::create($data);
                $this->productId = $product->id; // Guarda el ID del producto recién creado
                session()->flash('message', 'Producto creado exitosamente.');
            }

            // Elimina las imágenes marcadas para eliminación (tanto del almacenamiento como de la DB)
            if (!empty($this->imageDeleteIds)) {
                $imagesToDelete = ProductImage::whereIn('id', $this->imageDeleteIds)->get();
                foreach ($imagesToDelete as $img) {
                    Storage::disk('public')->delete($img->image_path);
                    if ($img->thumbnail_path) {
                        Storage::disk('public')->delete($img->thumbnail_path);
                    }
                    $img->delete();
                }
            }

            // Sube y asocia las nuevas imágenes al producto
            foreach ($this->newImages as $image) {
                $imagePath = $image->store('products', 'public'); // Guarda la imagen
                $thumbnailPath = null; // Lógica para thumbnail si se implementa

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'thumbnail_path' => $thumbnailPath,
                    'is_main' => false,
                    'sort_order' => 0,
                ]);
            }

            // Redirige a la lista de productos
            return redirect()->route('admin.products.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        return view('livewire.product.product-form');
    }
}