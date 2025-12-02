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
        Schema::create('product_menu_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_id')->unique()->constrained('product_template')->onDelete('cascade');
            
            // Menu display info
            $table->string('menu_name', 200)->nullable()->comment('Override del nombre para el menú');
            $table->text('menu_description')->nullable()->comment('Descripción para clientes');
            
            // Kitchen & preparation
            $table->integer('preparation_time_minutes')->default(0);
            
            // Marketing & features
            $table->boolean('is_featured')->default(false)->comment('Destacado en el menú');
            $table->integer('display_order')->default(0);
            
            // Nutritional & dietary info
            $table->integer('calories')->nullable();
            $table->boolean('is_spicy')->default(false);
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_gluten_free')->default(false);
            $table->json('allergens')->nullable()->comment('["gluten", "lactose", "nuts"]');
            
            // Availability by service type
            $table->boolean('available_for_dine_in')->default(true);
            $table->boolean('available_for_takeout')->default(true);
            $table->boolean('available_for_delivery')->default(true);
            
            $table->timestamps();

            // Indexes
            $table->index('is_featured');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_menu_settings');
    }
};
