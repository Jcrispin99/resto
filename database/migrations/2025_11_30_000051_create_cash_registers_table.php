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
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terminal_id')->constrained('pos_terminals')->onDelete('cascade');
            $table->decimal('opening_balance', 10, 2);
            $table->decimal('closing_balance', 10, 2)->nullable();
            $table->decimal('expected_balance', 10, 2)->nullable();
            $table->decimal('difference', 10, 2)->nullable();
            $table->unsignedBigInteger('opened_by');
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('terminal_id');
            $table->index('status');
            $table->index('opened_at');
            $table->index('closed_at');

            // Foreign keys (users)
            $table->foreign('opened_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
