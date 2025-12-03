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
        Schema::create('branch_menu_availability', function (Blueprint $table) {
            $table->foreignId('branch_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('product_template_id')->constrained('product_template')->onDelete('cascade');
            $table->boolean('is_available')->default(true);
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->primary(['branch_id', 'product_template_id'], 'branch_menu_primary');

            // Indexes
            $table->index('branch_id');
            $table->index('product_template_id');
            $table->index('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_menu_availability');
    }
};
