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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained()->onDelete('restrict');
            $table->foreignId('variant_id')->nullable()->constrained('menu_item_variants')->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->onDelete('set null');
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pending', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');
            $table->text('special_instructions')->nullable();
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->timestamp('sent_to_kitchen_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('order_id');
            $table->index('menu_item_id');
            $table->index('status');

            // Foreign key
            $table->foreign('prepared_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
