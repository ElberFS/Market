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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // ID único para la orden
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ID del usuario que realizó la orden (si el usuario se elimina, sus órdenes también)
            $table->string('order_number')->unique(); // Número de orden único generado por el sistema
            $table->decimal('total_amount', 10, 2); // Monto total de la orden
            $table->string('status')->default('pending'); // Estado actual de la orden (ej. 'pendiente', 'procesando', 'enviado', 'entregado', 'cancelado')
            $table->text('shipping_address'); // Dirección de envío al momento de la compra
            $table->text('billing_address')->nullable(); // Dirección de facturación (puede ser la misma que la de envío)
            $table->string('payment_method')->nullable(); // Método de pago utilizado (ej. 'tarjeta de crédito', 'paypal', 'transferencia')
            $table->string('payment_status')->default('pending'); // Estado del pago (ej. 'pagado', 'pendiente', 'fallido')
            $table->timestamp('shipped_at')->nullable(); // Fecha y hora en que la orden fue enviada
            $table->timestamp('delivered_at')->nullable(); // Fecha y hora en que la orden fue entregada
            $table->text('notes')->nullable(); // Notas adicionales sobre la orden
            $table->timestamps(); // Campos `created_at` y `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
