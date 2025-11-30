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
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menu_categories')->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('image', 255)->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('available_for_delivery')->default(true);
            $table->boolean('available_for_dine_in')->default(true);
            $table->boolean('available_for_takeout')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('parent_id');
            $table->index('order');
            $table->index('is_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_categories');
    }
};
