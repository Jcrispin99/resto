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
            $table->enum('partner_type', ['individual', 'company'])->default('individual');
            $table->string('business_name', 200);
            $table->string('trade_name', 200)->nullable();
            $table->string('tax_id', 20)->unique()->nullable()->comment('RUC/DNI');
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email', 150);
            $table->string('phone', 20);
            $table->string('mobile', 20)->nullable();
            $table->string('website', 255)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->default('PE');
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_customer')->default(false);
            $table->boolean('is_supplier')->default(false);
            $table->boolean('is_transporter')->default(false);
            $table->string('customer_code', 20)->unique()->nullable();
            $table->string('supplier_code', 20)->unique()->nullable();
            $table->integer('payment_terms_days')->default(0);
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->integer('loyalty_points')->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->decimal('total_purchases', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('tax_id');
            $table->index('email');
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
