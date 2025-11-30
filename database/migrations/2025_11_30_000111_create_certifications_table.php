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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('certificate_number', 50)->unique();
            $table->date('issued_date');
            $table->date('expiry_date')->nullable();
            $table->string('certificate_url', 255);
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('course_id');
            $table->index('certificate_number');
            $table->index('issued_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
