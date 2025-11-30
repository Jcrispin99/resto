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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->text('question');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer'])->default('multiple_choice');
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('quiz_id');
            $table->index('question_type');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
