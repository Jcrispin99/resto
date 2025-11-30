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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('training_categories')->onDelete('restrict');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->integer('duration_hours')->default(0);
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->string('thumbnail', 255)->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Indexes
            $table->index('category_id');
            $table->index('level');
            $table->index('is_mandatory');
            $table->index('is_active');

            // Foreign key
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
