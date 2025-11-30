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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->onDelete('cascade');
            $table->string('title', 200);
            $table->text('content')->nullable();
            $table->enum('content_type', ['video', 'document', 'presentation', 'quiz', 'text'])->default('text');
            $table->string('content_url', 255)->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('module_id');
            $table->index('content_type');
            $table->index('order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
