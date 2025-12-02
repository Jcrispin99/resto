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
        Schema::create('product_template_attribute_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_id')->constrained('product_template')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('product_attributes')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_template_id', 'attribute_id'], 'template_attribute_unique');
        });

        Schema::create('product_template_attribute_line_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_attribute_line_id');
            $table->foreign('product_template_attribute_line_id', 'ptal_line_id_foreign')->references('id')->on('product_template_attribute_lines')->onDelete('cascade');
            $table->foreignId('product_attribute_value_id');
            $table->foreign('product_attribute_value_id', 'ptal_val_id_foreign')->references('id')->on('product_attribute_values')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_template_attribute_line_id', 'product_attribute_value_id'], 'line_value_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_template_attribute_line_values');
        Schema::dropIfExists('product_template_attribute_lines');
    }
};
