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
        Schema::create('kitchen_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20)->unique();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('station_id')->constrained('kitchen_stations')->onDelete('restrict');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'preparing', 'ready', 'delivered'])->default('pending');
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('ticket_number');
            $table->index('order_id');
            $table->index('station_id');
            $table->index('status');
            $table->index('priority');

            // Foreign key
            $table->foreign('prepared_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_tickets');
    }
};
