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
            $table->foreignId('parent_id')->nullable()->constrained('companies')->onDelete('cascade')
                ->comment('NULL = Matriz, valor = Sucursal');

            // Core fields (always required)
            $table->string('name', 200)->comment('Nombre genérico');
            $table->string('business_name', 200)->comment('Razón social');
            $table->string('trade_name', 200)->comment('Nombre comercial');
            $table->string('tax_id', 20)->comment('RUC');
            $table->string('logo', 255)->nullable();
            $table->boolean('is_active')->default(true);

            // Branch identification (nullable - only for branches)
            $table->string('code', 10)->nullable()->unique()->comment('Código sucursal');

            // Contact (nullable - useful for both)
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website', 255)->nullable();

            // Location (nullable - only for branches)
            $table->string('address', 255)->nullable();
            $table->string('ubigeo_code', 6)->nullable()->comment('Código ubigeo');
            $table->string('country', 3)->default('PE');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Infrastructure (nullable - only for branches)
            $table->string('kitchen_printer_ip', 45)->nullable();

            // Operations (nullable - only for branches)
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->integer('max_tables')->default(0);
            $table->integer('max_capacity')->default(0);

            // Settings (defaults for branches)
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
            $table->softDeletes();

            // Indexes
            $table->index('parent_id');
            $table->index(['parent_id', 'is_active']);
            $table->index('tax_id');
            $table->index('code');
            $table->index('ubigeo_code');
            $table->index('is_active');
            $table->index(['latitude', 'longitude']);
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
