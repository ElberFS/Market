<div class="max-w-6xl mx-auto p-6 bg-white shadow-lg rounded-lg my-8">
    <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">
        {{ $productId ? 'Editar Producto' : 'Crear Nuevo Producto' }}
    </h2>

    {{-- Mensajes de sesión (éxito/error) --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">Éxito</p>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="saveProduct" class="space-y-6">
        {{-- Sección de Información Básica --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Producto:</label>
                <input type="text" id="name" wire:model.live="name"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       placeholder="Ej. Laptop Gaming, Camiseta Algodón">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug (URL amigable):</label>
                <input type="text" id="slug" wire:model="slug" disabled
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-600 cursor-not-allowed sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">Se generará automáticamente a partir del nombre.</p>
                @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Descripción Completa:</label>
                <textarea id="description" wire:model="description" rows="5"
                          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                          placeholder="Describe el producto en detalle..."></textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="short_description" class="block text-sm font-medium text-gray-700">Descripción Corta (Opcional):</label>
                <textarea id="short_description" wire:model="short_description" rows="2"
                          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                          placeholder="Una breve descripción para listados de productos..."></textarea>
                @error('short_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        ---

        {{-- Sección de Precios y Stock --}}
        <h3 class="text-xl font-semibold text-gray-800 pt-4 border-t border-gray-200">Precios y Stock</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Precio Actual:</label>
                <input type="number" step="0.01" id="price" wire:model="price"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       placeholder="99.99">
                @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="old_price" class="block text-sm font-medium text-gray-700">Precio Anterior (Opcional):</label>
                <input type="number" step="0.01" id="old_price" wire:model="old_price"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       placeholder="129.99">
                <p class="mt-1 text-xs text-gray-500">Para mostrar como oferta (debe ser mayor al precio actual).</p>
                @error('old_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="stock" class="block text-sm font-medium text-gray-700">Stock Disponible:</label>
                <input type="number" id="stock" wire:model="stock"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       placeholder="100">
                @error('stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-1">
                <label for="SKU" class="block text-sm font-medium text-gray-700">SKU (Opcional):</label>
                <input type="text" id="SKU" wire:model="SKU"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       placeholder="PROD-ABC-123">
                <p class="mt-1 text-xs text-gray-500">Código único de inventario.</p>
                @error('SKU') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        ---

        {{-- Sección de Categoría y Marca --}}
        <h3 class="text-xl font-semibold text-gray-800 pt-4 border-t border-gray-200">Clasificación</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Categoría:</label>
                <select id="category_id" wire:model="category_id"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">-- Seleccione una Categoría --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="brand_id" class="block text-sm font-medium text-gray-700">Marca (Opcional):</label>
                <select id="brand_id" wire:model="brand_id"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">-- Ninguna --</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
                @error('brand_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        ---

        {{-- Sección de Imágenes --}}
        <h3 class="text-xl font-semibold text-gray-800 pt-4 border-t border-gray-200">Imágenes del Producto</h3>

        {{-- Mostrar imágenes existentes (solo en modo edición) --}}
        @if ($productId && count($existingImages) > 0)
            <div class="mb-4">
                <p class="block text-sm font-medium text-gray-700 mb-2">Imágenes Actuales:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($existingImages as $image)
                        <div class="relative group border rounded-lg overflow-hidden shadow-sm">
                            <img src="{{ Storage::url($image['image_path']) }}" alt="Producto Imagen" class="w-full h-32 object-cover">
                            <button type="button"
                                    wire:click="markImageForDeletion({{ $image['id'] }})"
                                    class="absolute top-1 right-1 p-1 rounded-full bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    title="{{ in_array($image['id'], $imageDeleteIds) ? 'Desmarcar para conservar' : 'Marcar para eliminar' }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            @if (in_array($image['id'], $imageDeleteIds))
                                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white font-bold text-sm">
                                    ELIMINAR
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-500">Haz clic en el icono de papelera para marcar/desmarcar imágenes para eliminar.</p>
            </div>
        @endif

        {{-- Input para nuevas imágenes --}}
        <div>
            <label for="newImages" class="block text-sm font-medium text-gray-700">Subir Nuevas Imágenes (Máx. 2MB cada una):</label>
            <input type="file" id="newImages" wire:model="newImages" multiple
                   class="mt-1 block w-full text-sm text-gray-500
                   file:mr-4 file:py-2 file:px-4
                   file:rounded-md file:border-0
                   file:text-sm file:font-semibold
                   file:bg-indigo-50 file:text-indigo-700
                   hover:file:bg-indigo-100">
            @error('newImages.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('newImages') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Previsualización de nuevas imágenes --}}
        @if (!empty($newImages))
            <div class="mt-4">
                <p class="block text-sm font-medium text-gray-700 mb-2">Previsualización de Nuevas Imágenes:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($newImages as $key => $image)
                        <div class="relative group border rounded-lg overflow-hidden shadow-sm">
                            @if ($image)
                                <img src="{{ $image->temporaryUrl() }}" alt="Previsualización" class="w-full h-32 object-cover">
                                <button type="button"
                                        wire:click="removeNewImage({{ $key }})"
                                        class="absolute top-1 right-1 p-1 rounded-full bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        title="Eliminar esta imagen de la previsualización">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        ---

        {{-- Sección de Estados del Producto --}}
        <h3 class="text-xl font-semibold text-gray-800 pt-4 border-t border-gray-200">Estado</h3>
        <div class="flex items-center space-x-6">
            <div class="flex items-center">
                <input type="checkbox" id="is_active" wire:model="is_active"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700">
                    Producto Activo (Visible en el sitio)
                </label>
                @error('is_active') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="is_featured" wire:model="is_featured"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="is_featured" class="ml-2 block text-sm font-medium text-gray-700">
                    Producto Destacado (Mostrar en portada/sección especial)
                </label>
                @error('is_featured') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        ---

        {{-- Botones de acción --}}
        <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.products.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ $productId ? 'Actualizar Producto' : 'Guardar Producto' }}
            </button>
        </div>
    </form>
</div>