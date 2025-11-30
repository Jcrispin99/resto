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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code', 10)->unique();
            $table->string('name', 150);

            // Fiscal data (for independent invoicing)
            $table->string('business_name', 200)->nullable()->comment('Razón social');
            $table->string('tax_id', 11)->nullable()->comment('RUC');

            // Location
            $table->string('address', 255);
            $table->string('ubigeo_code', 6)->nullable()->comment('Código ubigeo (departamento-provincia-distrito)');
            $table->string('country', 3)->default('PE');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Contact
            $table->string('phone', 20);
            $table->string('email', 150);
            $table->string('website', 255)->nullable();

            // Operations
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->integer('max_tables')->default(0);
            $table->integer('max_capacity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('company_id');
            $table->index('code');
            $table->index('tax_id');
            $table->index('ubigeo_code');
            $table->index('is_active');
            $table->index(['latitude', 'longitude']);

            // Foreign key will be added after users table exists
            // $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
