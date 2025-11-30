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
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('base_salary', 10, 2);
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->decimal('commissions', 10, 2)->default(0);
            $table->decimal('overtime', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('status');
            $table->index(['period_start', 'period_end']);
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};
