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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->foreignId('branch_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('partner_id')->constrained('partners')->onDelete('restrict')->comment('Supplier');

            // Dates
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('received_date')->nullable();
            $table->date('paid_date')->nullable();

            // Status flow: quote_request → quote_received → ordered → approved → received → paid
            $table->enum('status', [
                'quote_request',   // Solicitud de cotización
                'quote_received',  // Cotización recibida
                'ordered',         // Orden enviada al proveedor
                'approved',        // Aprobada internamente
                'received',        // Mercancía recibida
                'paid',            // Pagado al proveedor
                'cancelled',
            ])->default('quote_request');

            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('purchase_orders');
    }
};
