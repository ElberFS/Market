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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id(); // ID único para la imagen del producto
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // ID del producto al que pertenece la imagen (si el producto se elimina, sus imágenes también)
            $table->string('image_path'); // Ruta de almacenamiento de la imagen original
            $table->string('thumbnail_path')->nullable(); // Opcional: Ruta de almacenamiento de la miniatura de la imagen
            $table->boolean('is_main')->default(false); // Indica si esta es la imagen principal del producto
            $table->integer('sort_order')->default(0); // Orden para mostrar las imágenes del producto
            $table->timestamps(); // Campos `created_at` y `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
