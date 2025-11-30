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
        Schema::create('kitchen_ticket_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('kitchen_tickets')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->enum('status', ['pending', 'preparing', 'ready'])->default('pending');
            $table->timestamps();

            // Indexes
            $table->index('ticket_id');
            $table->index('order_item_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_ticket_items');
    }
};
