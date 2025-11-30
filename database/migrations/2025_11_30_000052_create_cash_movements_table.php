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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['income', 'expense', 'opening', 'closing', 'deposit', 'withdrawal']);
            $table->string('concept', 200);
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50)->nullable();
            $table->string('reference', 100)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('cash_register_id');
            $table->index('type');
            $table->index('created_at');

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
