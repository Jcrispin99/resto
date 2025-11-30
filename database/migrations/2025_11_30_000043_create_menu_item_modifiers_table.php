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
        Schema::create('menu_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['single', 'multiple'])->default('multiple');
            $table->integer('min_selection')->default(0);
            $table->integer('max_selection')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_modifiers');
    }
};
