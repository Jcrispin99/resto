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
            $table->string('address', 255);
            $table->string('district', 100);
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('country', 100)->default('PE');
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone', 20);
            $table->string('email', 150);
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->integer('max_tables')->default(0);
            $table->integer('max_capacity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('company_id');
            $table->index('code');
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
