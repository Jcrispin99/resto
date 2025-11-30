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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('business_name', 200)->comment('Razón social');
            $table->string('trade_name', 200)->comment('Nombre comercial');
            $table->string('tax_id', 20)->unique()->comment('RUC');
            $table->string('logo', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('tax_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
