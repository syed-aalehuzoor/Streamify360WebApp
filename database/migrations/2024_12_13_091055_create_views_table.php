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
        Schema::create('views', function (Blueprint $table) {
            $table->id();
            $table->string('videoid', 11);
            $table->foreign('videoid')->references('id')->on('videos')->onDelete('cascade');
            $table->foreignId('viewerid')->constrained('viewers')->onDelete('cascade');
            $table->string('country');
            $table->string('region');
            $table->string('city');
            $table->string('device_type');
            $table->timestamps(); // Includes created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};
