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
        Schema::create('user_lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->integer('progress_percentage')->default(0);
            $table->integer('time_spent_minutes')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('lesson_id');
            $table->index('status');
            $table->unique(['user_id', 'lesson_id'], 'user_lesson_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_lesson_progress');
    }
};
