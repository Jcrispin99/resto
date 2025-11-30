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
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('cash_register_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payment_method_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 10, 2);
            $table->string('reference_number', 100)->nullable()->comment('Transaction number, card last 4 digits, etc.');
            $table->timestamp('payment_date')->useCurrent();
            $table->unsignedBigInteger('processed_by');
            $table->enum('status', ['pending', 'completed', 'cancelled', 'refunded'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('order_id');
            $table->index('cash_register_id');
            $table->index('payment_method_id');
            $table->index('status');
            $table->index('payment_date');

            // Foreign key
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
