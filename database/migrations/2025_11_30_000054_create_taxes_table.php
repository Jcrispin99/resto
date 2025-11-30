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
            $table->string('code', 20)->unique()->comment('IGV, ICBPER, ISC, etc.');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('tax_type', ['percentage', 'fixed', 'per_unit'])->default('percentage');
            $table->decimal('rate', 10, 4)->comment('18.00 for IGV, 0.30 for ICBPER');
            $table->boolean('is_included_in_price')->default(false);
            $table->enum('applies_to', ['all', 'products', 'services'])->default('all');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('code');
            $table->index('tax_type');
            $table->index('is_default');
            $table->index('is_active');
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
