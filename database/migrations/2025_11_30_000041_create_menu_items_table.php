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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('menu_categories')->onDelete('restrict');
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2)->default(0);
            $table->integer('preparation_time_minutes')->default(0);
            $table->integer('calories')->nullable();
            $table->boolean('is_spicy')->default(false);
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_gluten_free')->default(false);
            $table->json('allergens')->nullable();
            $table->string('image', 255)->nullable();
            $table->json('images')->nullable()->comment('Gallery');
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('sku');
            $table->index('category_id');
            $table->index('is_available');
            $table->index('is_featured');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
