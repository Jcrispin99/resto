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
        Schema::create('user_quiz_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('user_quiz_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('quiz_questions')->onDelete('cascade');
            $table->foreignId('answer_id')->nullable()->constrained('quiz_answers')->onDelete('set null');
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('attempt_id');
            $table->index('question_id');
            $table->index('is_correct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quiz_responses');
    }
};
