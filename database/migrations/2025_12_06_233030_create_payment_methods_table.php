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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Código único');
            $table->string('name', 50)->comment('Nombre del método');
            $table->enum('type', ['cash', 'card', 'transfer', 'wallet', 'other'])->default('cash');
            $table->boolean('requires_reference')->default(false)->comment('Requiere número de operación');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
