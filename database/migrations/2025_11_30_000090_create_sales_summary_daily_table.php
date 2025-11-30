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
        Schema::create('sales_summary_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('summary_date');
            $table->integer('total_orders')->default(0);
            $table->integer('total_customers')->default(0);
            $table->integer('dine_in_orders')->default(0);
            $table->integer('takeout_orders')->default(0);
            $table->integer('delivery_orders')->default(0);
            $table->decimal('gross_sales', 10, 2)->default(0);
            $table->decimal('discounts', 10, 2)->default(0);
            $table->decimal('taxes', 10, 2)->default(0);
            $table->decimal('net_sales', 10, 2)->default(0);
            $table->decimal('avg_ticket', 10, 2)->default(0);
            $table->integer('total_items_sold')->default(0);
            $table->timestamps();

            // Unique constraint
            $table->unique(['branch_id', 'summary_date'], 'sales_daily_unique');

            // Indexes
            $table->index('branch_id');
            $table->index('summary_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_summary_daily');
    }
};
