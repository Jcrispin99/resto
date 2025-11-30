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
        Schema::create('waiter_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('summary_date');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->decimal('avg_ticket', 10, 2)->default(0);
            $table->integer('avg_preparation_time')->default(0)->comment('minutes');
            $table->decimal('customer_satisfaction', 3, 2)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('summary_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waiter_performance');
    }
};
