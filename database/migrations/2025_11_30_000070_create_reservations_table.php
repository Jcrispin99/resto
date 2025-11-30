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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number', 20)->unique();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('partner_id')->constrained()->onDelete('restrict')->comment('Customer');
            $table->foreignId('table_id')->nullable()->constrained('tables')->onDelete('set null');
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->integer('guests_count');
            $table->enum('status', ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('special_requests')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('seated_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('reservation_number');
            $table->index('branch_id');
            $table->index('partner_id');
            $table->index('table_id');
            $table->index('status');
            $table->index(['reservation_date', 'reservation_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
