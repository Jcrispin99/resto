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
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('partner_id')->constrained('partners')->onDelete('restrict')->comment('Customer');
            
            // Dates
            $table->date('order_date');
            $table->date('quote_valid_until')->nullable()->comment('Vigencia de cotización');
            $table->date('delivery_date')->nullable();
            $table->date('paid_date')->nullable();
            
            // Status flow: quote → quote_sent → approved → processing → delivered → paid
            $table->enum('status', [
                'quote',           // Cotización (borrador)
                'quote_sent',      // Cotización enviada al cliente
                'approved',        // Cliente aprobó
                'processing',      // En preparación/producción
                'delivered',       // Entregado al cliente
                'paid',            // Cliente pagó
                'cancelled'
            ])->default('quote');
            
            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);
            
            // Delivery info
            $table->string('delivery_address', 255)->nullable();
            $table->string('delivery_contact', 100)->nullable();
            $table->string('delivery_phone', 20)->nullable();
            
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('order_number');
            $table->index('branch_id');
            $table->index('warehouse_id');
            $table->index('partner_id');
            $table->index('status');
            $table->index('order_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_orders');
    }
};
