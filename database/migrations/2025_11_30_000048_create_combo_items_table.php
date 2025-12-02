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
        Schema::create('combo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained('combos')->onDelete('cascade');
            $table->foreignId('product_template_id')->constrained('product_template')->onDelete('cascade')->comment('Producto que incluye el combo');
            $table->integer('quantity')->default(1);
            $table->boolean('allow_substitution')->default(false)->comment('Permitir cambiar por otro producto');
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('combo_id');
            $table->index('product_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_items');
    }
};
