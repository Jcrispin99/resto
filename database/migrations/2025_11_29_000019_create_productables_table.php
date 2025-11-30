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
        Schema::create('productables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_product_id')->constrained('product_product')->onDelete('cascade');
            $table->unsignedBigInteger('productable_id');
            $table->string('productable_type', 50);
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['productable_id', 'productable_type']);
            $table->index('product_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productables');
    }
};
