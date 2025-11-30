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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('cash_register_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('table_id')->nullable()->constrained('tables')->onDelete('set null');
            $table->foreignId('partner_id')->nullable()->constrained()->onDelete('set null')->comment('Customer');
            $table->enum('order_type', ['dine_in', 'takeout', 'delivery', 'digital_menu'])->default('dine_in');
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->timestamp('order_date')->useCurrent();
            $table->timestamp('scheduled_time')->nullable();
            $table->timestamp('served_time')->nullable();
            $table->timestamp('completed_time')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('waiter_id')->nullable();
            $table->unsignedBigInteger('cashier_id')->nullable()->comment('Who processed payment');
            $table->integer('guests_count')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('tip_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('order_number');
            $table->index('branch_id');
            $table->index('cash_register_id');
            $table->index('table_id');
            $table->index('partner_id');
            $table->index('order_type');
            $table->index('status');
            $table->index('payment_status');
            $table->index('order_date');

            // Foreign keys
            $table->foreign('waiter_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cashier_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
