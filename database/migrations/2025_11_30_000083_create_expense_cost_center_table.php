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
        Schema::create('expense_cost_center', function (Blueprint $table) {
            $table->foreignId('expense_id')->constrained()->onDelete('cascade');
            $table->foreignId('cost_center_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('percentage', 5, 2);
            
            $table->primary(['expense_id', 'cost_center_id'], 'expense_cost_center_primary');
            
            // Indexes
            $table->index('expense_id');
            $table->index('cost_center_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_cost_center');
    }
};
