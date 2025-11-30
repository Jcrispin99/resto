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
        Schema::create('imageables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('imageable_id');
            $table->string('imageable_type', 50);
            $table->string('image_url', 255);
            $table->string('thumbnail_url', 255)->nullable();
            $table->string('title', 200)->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['imageable_id', 'imageable_type']);
            $table->index('is_primary');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imageables');
    }
};
