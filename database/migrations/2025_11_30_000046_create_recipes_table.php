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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_id')->constrained('product_template')->onDelete('cascade')->comment('El plato/producto que se elabora');
            $table->foreignId('ingredient_id')->constrained('product_product')->onDelete('restrict')->comment('Ingrediente que consume');
            $table->decimal('quantity', 10, 3)->comment('Cantidad necesaria del ingrediente');
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            $table->decimal('waste_percentage', 5, 2)->default(0)->comment('% de merma esperada');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('product_template_id');
            $table->index('ingredient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
