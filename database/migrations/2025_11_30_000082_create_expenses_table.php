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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number', 20)->unique();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('restrict');
            $table->foreignId('partner_id')->nullable()->constrained()->onDelete('set null')->comment('Supplier');
            $table->date('expense_date');
            $table->string('description', 255);
            $table->decimal('amount', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('payment_method', 50);
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->string('invoice_number', 50)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('expense_number');
            $table->index('branch_id');
            $table->index('category_id');
            $table->index('partner_id');
            $table->index('status');
            $table->index('expense_date');

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
