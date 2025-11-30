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
        Schema::create('product_sales_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->date('summary_date');
            $table->integer('quantity_sold')->default(0);
            $table->decimal('gross_sales', 10, 2)->default(0);
            $table->decimal('net_sales', 10, 2)->default(0);
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->decimal('profit_margin', 5, 2)->default(0);
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('menu_item_id');
            $table->index('summary_date');
            $table->index(['branch_id', 'summary_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sales_summary');
    }
};
