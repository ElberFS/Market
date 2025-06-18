<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Category\CategoryList;
use App\Livewire\Category\CategoryForm;
use App\Livewire\Product\ProductList; // Importar ProductList
use App\Livewire\Product\ProductForm; // Importar ProductForm

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/admin/users', UserManagement::class)->name('admin.users.index');
});

Route::middleware(['auth', 'role:administrador|vendedor'])->group(function () {
    Route::prefix('admin/categories')->name('admin.categories.')->group(function () {
        Route::get('/', CategoryList::class)->name('index');
        Route::get('/create', CategoryForm::class)->name('create');
        Route::get('/{categoryId}/edit', CategoryForm::class)->name('edit');
    });

    // --- Nuevas Rutas para Productos ---
    Route::prefix('admin/products')->name('admin.products.')->group(function () {
        Route::get('/', ProductList::class)->name('index');
        Route::get('/create', ProductForm::class)->name('create');
        Route::get('/{productId}/edit', ProductForm::class)->name('edit');
    });
    
});

require __DIR__.'/auth.php';