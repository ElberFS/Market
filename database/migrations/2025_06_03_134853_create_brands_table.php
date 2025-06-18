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
        Schema::create('brands', function (Blueprint $table) {
            $table->id(); // ID único para la marca
            $table->string('name')->unique(); // Nombre de la marca (debe ser único)
            $table->string('slug')->unique(); // Slug para URLs amigables de la marca
            $table->text('description')->nullable(); // Descripción de la marca
            $table->string('logo_path')->nullable(); // Ruta a la imagen del logo de la marca
            $table->boolean('is_active')->default(true); // Indica si la marca está activa (visible)
            $table->timestamps(); // Campos `created_at` y `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
