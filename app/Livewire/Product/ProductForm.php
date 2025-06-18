<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\Category; // Importar el modelo Category
use App\Models\Brand;    // Importar el modelo Brand
use App\Models\ProductImage; // Importar el modelo ProductImage
use Livewire\Component;
use Livewire\WithFileUploads; // Importar el trait para subir archivos
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Para manejar el almacenamiento de archivos

class ProductForm extends Component
{
    use WithFileUploads; // Habilitar la carga de archivos

    // Propiedades del producto
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

    // Propiedades para la carga y gestión de imágenes
    public $newImages = []; // Array para las nuevas imágenes subidas
    public $existingImages = []; // Array para las imágenes existentes del producto
    public $imageDeleteIds = []; // Array para IDs de imágenes a eliminar

    // Datos para selectores
    public $categories;
    public $brands;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Validar unicidad del nombre, ignorando el producto actual si es edición
                $this->productId
                    ? Rule::unique('products', 'name')->ignore($this->productId)
                    : 'unique:products,name',
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                // Validar unicidad del slug, ignorando el producto actual si es edición
                Rule::unique('products', 'slug')->ignore($this->productId),
            ],
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0.01',
            'old_price' => 'nullable|numeric|min:0.01|gt:price', // old_price debe ser mayor que price si existe
            'SKU' => [
                'nullable',
                'string',
                'max:255',
                // Validar unicidad del SKU, ignorando el producto actual si es edición
                $this->productId
                    ? Rule::unique('products', 'SKU')->ignore($this->productId)
                    : 'unique:products,SKU',
            ],
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'newImages.*' => 'nullable|image|max:2048', // Validación para cada nueva imagen (máx 2MB)
        ];
    }

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

    public function mount($productId = null)
    {
        // Cargar categorías y marcas disponibles para los selectores
        $this->categories = Category::orderBy('name')->get();
        $this->brands = Brand::orderBy('name')->get(); // Asegúrate de tener un modelo Brand y una tabla 'brands'

        if ($productId) {
            $this->productId = $productId;
            $product = Product::with('images')->findOrFail($productId); // Cargar el producto con sus imágenes

            // Asignar propiedades del producto
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
            $this->is_active = $product->is_active;
            $this->is_featured = $product->is_featured;

            // Cargar imágenes existentes
            $this->existingImages = $product->images->toArray();
        }
    }

    // Se ejecuta automáticamente cuando 'name' cambia
    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
        $this->validateOnly('slug'); // Opcional: Revalidar solo el slug para feedback inmediato
    }

    // Se ejecuta automáticamente cuando 'newImages' cambia
    // Puede usarse para validación en tiempo real o previsualización
    public function updatedNewImages()
    {
        $this->validateOnly('newImages.*'); // Valida cada nueva imagen al seleccionarla
    }

    // Método para marcar una imagen existente para eliminación
    public function markImageForDeletion($imageId)
    {
        if (($key = array_search($imageId, $this->imageDeleteIds)) !== false) {
            // Si ya está marcada, desmarcarla
            unset($this->imageDeleteIds[$key]);
        } else {
            // Si no está marcada, marcarla
            $this->imageDeleteIds[] = $imageId;
        }
        $this->imageDeleteIds = array_values($this->imageDeleteIds); // Reindexar el array
    }

    // Método para eliminar una imagen existente del DOM (sin eliminarla de DB todavía)
    public function removeExistingImage($imageId)
    {
        // Esto es útil si quieres que el usuario vea que la imagen "desaparece"
        // antes de guardar. La eliminación real en DB ocurre en saveProduct.
        $this->existingImages = array_filter($this->existingImages, function($image) use ($imageId) {
            return $image['id'] != $imageId;
        });
        // Asegurarse de que si se eliminó del DOM, también se marque para borrar de la DB
        $this->markImageForDeletion($imageId);
    }

    // Método para eliminar una nueva imagen subida (antes de guardar)
    public function removeNewImage($key)
    {
        unset($this->newImages[$key]);
        $this->newImages = array_values($this->newImages); // Reindexar el array
    }

    public function saveProduct()
    {
        $this->validate(); // Valida todos los campos, incluyendo las nuevas imágenes

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
                // Modo Edición
                $product = Product::find($this->productId);
                $product->update($data);
                session()->flash('message', 'Producto actualizado exitosamente.');
            } else {
                // Modo Creación
                $product = Product::create($data);
                $this->productId = $product->id; // Obtener el ID del nuevo producto
                session()->flash('message', 'Producto creado exitosamente.');
            }

            // 1. Eliminar imágenes marcadas
            if (!empty($this->imageDeleteIds)) {
                $imagesToDelete = ProductImage::whereIn('id', $this->imageDeleteIds)->get();
                foreach ($imagesToDelete as $img) {
                    Storage::disk('public')->delete($img->image_path); // Eliminar archivo físico
                    if ($img->thumbnail_path) {
                        Storage::disk('public')->delete($img->thumbnail_path); // Eliminar miniatura física
                    }
                    $img->delete(); // Eliminar registro de la DB
                }
            }

            // 2. Subir y guardar nuevas imágenes
            foreach ($this->newImages as $image) {
                $imagePath = $image->store('products', 'public'); // Guarda en storage/app/public/products

                // Opcional: Generar y guardar thumbnail (requiere Intervention Image u otra librería)
                // Para simplificar, aquí solo guardamos la ruta de la imagen original.
                // Si quieres generar thumbnails, necesitarías una librería como Intervention Image
                // y código adicional aquí para procesar la imagen antes de guardar.
                $thumbnailPath = null; // Opcional: implementar la generación de thumbnail aquí

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'thumbnail_path' => $thumbnailPath,
                    'is_main' => false, // Podrías tener lógica para determinar la principal
                    'sort_order' => 0,  // Podrías tener lógica para el orden
                ]);
            }

            // Redirigir al listado de productos después de guardar
            return redirect()->route('admin.products.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar el producto: ' . $e->getMessage());
            // Opcional: loguear el error para depuración
            // \Log::error('Error al guardar producto: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.product.product-form');
    }
}