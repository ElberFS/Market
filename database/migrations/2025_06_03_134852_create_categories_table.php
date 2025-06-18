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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // ID único para la categoría
            $table->string('name')->unique(); // Nombre de la categoría (debe ser único)
            $table->string('slug')->unique(); // Slug para URLs amigables (ej. 'ropa-de-hombre')
            $table->text('description')->nullable(); // Descripción de la categoría
            $table->foreignId('parent_id') // ID de la categoría padre para categorías anidadas
                  ->nullable()
                  ->constrained('categories') // Clave foránea auto-referenciada a 'id' de 'categories'
                  ->onDelete('set null'); // Si la categoría padre se elimina, establece 'parent_id' a NULL
            $table->boolean('is_active')->default(true); // Indica si la categoría está activa (visible)
            $table->timestamps(); // Campos `created_at` y `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
