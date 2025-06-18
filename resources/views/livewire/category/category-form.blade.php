<div class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg">
    <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">
        {{ $categoryId ? 'Editar Categoría' : 'Crear Nueva Categoría' }}
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

    <form wire:submit.prevent="saveCategory" class="space-y-6">
        {{-- Campo Nombre --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la Categoría:</label>
            <input type="text" id="name" wire:model.live="name"
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                   placeholder="Ej. Electrónica, Ropa, Libros">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Campo Slug (deshabilitado, se genera automáticamente) --}}
        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700">Slug (URL amigable):</label>
            <input type="text" id="slug" wire:model="slug" disabled
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-600 cursor-not-allowed sm:text-sm">
            <p class="mt-1 text-xs text-gray-500">Se generará automáticamente a partir del nombre.</p>
            @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Campo Descripción --}}
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Descripción (Opcional):</label>
            <textarea id="description" wire:model="description" rows="4"
                      class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      placeholder="Una breve descripción de la categoría..."></textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Campo Categoría Padre --}}
        <div>
            <label for="parentId" class="block text-sm font-medium text-gray-700">Categoría Padre (Opcional):</label>
            <select id="parentId" wire:model="parentId"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">-- Ninguna (Categoría Principal) --</option>
                @foreach($availableParentCategories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('parentId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Campo Activo --}}
        <div class="flex items-center">
            <input type="checkbox" id="isActive" wire:model="isActive"
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="isActive" class="ml-2 block text-sm font-medium text-gray-700">
                Activa (Visible en el sitio)
            </label>
            @error('isActive') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Botones de acción --}}
        <div class="flex justify-end space-x-3 mt-6">
            {{-- CORRECCIÓN AQUÍ: Usar 'admin.categories.index' --}}
            <a href="{{ route('admin.categories.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ $categoryId ? 'Actualizar Categoría' : 'Guardar Categoría' }}
            </button>
        </div>
    </form>
</div>