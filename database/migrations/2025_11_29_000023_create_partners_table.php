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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->enum('partner_type', ['individual', 'company'])->default('company');

            // Identificación
            $table->string('name', 200)->comment('Razón social o nombre completo');
            $table->string('trade_name', 200)->nullable()->comment('Nombre comercial');
            $table->string('tax_id', 20)->unique()->comment('RUC/DNI');

            // Contacto
            $table->string('email', 150);
            $table->string('phone', 20);

            // Dirección
            $table->string('address', 255)->nullable();
            $table->string('ubigeo_code', 6)->nullable()->comment('Código ubigeo INEI');

            // Tipo de partner
            $table->boolean('is_customer')->default(false);
            $table->boolean('is_supplier')->default(false);

            // Términos comerciales
            $table->integer('payment_terms_days')->default(0)->comment('0 = contado');

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('tax_id');
            $table->index('email');
            $table->index('ubigeo_code');
            $table->index('is_customer');
            $table->index('is_supplier');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
