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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('invoice_label')->nullable()->comment('Label shown on invoice');
            $table->string('tax_type')->comment('IGV, ISC, ICBPER, RETENCION, PERCEPCION, etc.');
            $table->string('affectation_type_code', 2)->nullable()->comment('SUNAT Catalog 07');
            $table->decimal('rate_percent', 5, 2)->default(0)->comment('18.00 for IGV, 0.30 for ICBPER');
            $table->boolean('is_price_inclusive')->default(false)->comment('Tax included in price');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Indexes for frequent queries
            $table->index('tax_type');
            $table->index('affectation_type_code');
            $table->index('is_active');
            $table->index('is_default');
            $table->index('rate_percent');
            $table->index(['rate_percent', 'is_price_inclusive']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
