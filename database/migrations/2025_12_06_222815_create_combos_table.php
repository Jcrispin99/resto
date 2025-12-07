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
        Schema::create('combos', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->comment('Precio del combo');
            $table->decimal('regular_price', 10, 2)->comment('Precio regular sin descuento');
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('% de descuento aplicado');
            $table->string('image', 255)->nullable();
            $table->date('start_date')->comment('Fecha de inicio de la promoción');
            $table->date('end_date')->nullable()->comment('Fecha de fin (null = sin vencimiento)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combos');
    }
};
