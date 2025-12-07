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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers')->onDelete('cascade');
            $table->enum('type', ['income', 'expense', 'opening', 'closing', 'deposit', 'withdrawal']);
            $table->string('concept', 200)->comment('Descripción del movimiento');
            $table->decimal('amount', 10, 2)->comment('Monto (positivo o negativo)');
            $table->string('payment_method', 50)->nullable()->comment('Efectivo, tarjeta, etc.');
            $table->string('reference', 100)->nullable()->comment('Número de operación/referencia');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('cash_register_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
