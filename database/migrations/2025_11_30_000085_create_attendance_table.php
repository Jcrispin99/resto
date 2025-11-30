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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('check_in');
            $table->time('check_out')->nullable();
            $table->decimal('worked_hours', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('attendance_date');
            $table->index('status');
            $table->unique(['user_id', 'branch_id', 'attendance_date'], 'attendance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
