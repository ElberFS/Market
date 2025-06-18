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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); // ID único para el ítem de la orden
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade'); // ID de la orden a la que pertenece el ítem (si la orden se elimina, sus ítems también)
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // ID del producto de este ítem (si el producto se elimina, los ítems de orden relacionados también)
            $table->integer('quantity'); // Cantidad del producto en este ítem de la orden
            $table->decimal('price', 10, 2); // Precio del producto al momento de la compra (importante para el historial y si el precio del producto cambia)
            $table->timestamps(); // Campos `created_at` y `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
