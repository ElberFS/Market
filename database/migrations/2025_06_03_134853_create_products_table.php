<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // ID único para el producto
            $table->string('name'); // Nombre del producto
            $table->string('slug')->unique(); // Slug para URLs amigables del producto
            $table->text('description'); // Descripción completa del producto
            $table->string('short_description')->nullable(); // Descripción corta para listados
            $table->decimal('price', 10, 2); // Precio actual del producto (10 dígitos en total, 2 decimales)
            $table->decimal('old_price', 10, 2)->nullable(); // Precio anterior para mostrar ofertas
            $table->string('SKU')->unique()->nullable(); // Stock Keeping Unit (código único de producto)
            $table->integer('stock')->default(0); // Cantidad de stock disponible

            // Claves foráneas
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // ID de la categoría del producto (si la categoría se elimina, los productos también)
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null'); // ID de la marca del producto (si la marca se elimina, se establece a NULL)

            $table->boolean('is_active')->default(true); // Indica si el producto está activo (visible)
            $table->boolean('is_featured')->default(false); // Indica si el producto es destacado (ej. en la página principal)
            $table->timestamps(); // Campos `created_at` y `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
