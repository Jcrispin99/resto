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
        Schema::create('partner_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->onDelete('cascade');
            $table->enum('address_type', ['billing', 'shipping', 'delivery', 'other'])->default('billing');
            $table->string('address_name', 100)->comment('e.g., "Main Office", "North Warehouse"');
            $table->string('contact_name', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address', 255);
            $table->string('district', 100);
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('country', 100)->default('PE');
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('partner_id');
            $table->index('address_type');
            $table->index('is_default');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_addresses');
    }
};
