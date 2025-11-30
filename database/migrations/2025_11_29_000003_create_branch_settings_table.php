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
        Schema::create('branch_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->unique()->constrained()->onDelete('cascade');
            $table->string('currency', 3)->default('PEN');
            $table->string('timezone', 50)->default('America/Lima');
            $table->decimal('tax_percentage', 5, 2)->default(18.00);
            $table->boolean('print_kitchen_ticket')->default(true);
            $table->boolean('print_customer_receipt')->default(true);
            $table->boolean('accept_reservations')->default(true);
            $table->boolean('accept_delivery')->default(true);
            $table->boolean('accept_takeout')->default(true);
            $table->json('config_json')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_settings');
    }
};
