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
        Schema::create('user_course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['enrolled', 'in_progress', 'completed', 'dropped'])->default('enrolled');
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('course_id');
            $table->index('status');
            $table->unique(['user_id', 'course_id'], 'user_course_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_course_enrollments');
    }
};
