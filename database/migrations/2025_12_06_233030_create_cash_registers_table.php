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
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terminal_id')->constrained('pos_terminals')->onDelete('cascade');
            $table->decimal('opening_balance', 10, 2)->comment('Balance inicial');
            $table->decimal('closing_balance', 10, 2)->nullable()->comment('Balance al cierre');
            $table->decimal('expected_balance', 10, 2)->nullable()->comment('Balance esperado');
            $table->decimal('difference', 10, 2)->nullable()->comment('Diferencia (sobrante/faltante)');
            $table->foreignId('opened_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('terminal_id');
            $table->index('status');
            $table->index('opened_at');
            $table->index('closed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
