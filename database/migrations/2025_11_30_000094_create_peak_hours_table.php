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
        Schema::create('peak_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('summary_date');
            $table->integer('hour')->comment('0-23');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->integer('avg_preparation_time')->default(0)->comment('minutes');
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('summary_date');
            $table->index('hour');
            $table->index(['branch_id', 'summary_date', 'hour']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peak_hours');
    }
};
