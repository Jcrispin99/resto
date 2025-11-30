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
        Schema::create('category_sales_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('menu_categories')->onDelete('cascade');
            $table->date('summary_date');
            $table->integer('total_items_sold')->default(0);
            $table->decimal('gross_sales', 10, 2)->default(0);
            $table->decimal('net_sales', 10, 2)->default(0);
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('category_id');
            $table->index('summary_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_sales_summary');
    }
};
