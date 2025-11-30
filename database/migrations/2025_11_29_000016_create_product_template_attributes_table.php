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
        Schema::create('product_template_attributes', function (Blueprint $table) {
            $table->foreignId('product_template_id')->constrained('product_template')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('product_attributes')->onDelete('cascade');
            
            $table->primary(['product_template_id', 'attribute_id'], 'pt_attr_primary');
            
            // Indexes
            $table->index('product_template_id');
            $table->index('attribute_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_template_attributes');
    }
};
