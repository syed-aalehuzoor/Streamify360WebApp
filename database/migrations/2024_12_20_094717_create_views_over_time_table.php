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
        Schema::create('views_over_time', function (Blueprint $table) {
            $table->id();
            $table->string('videoid', 11);
            $table->foreign('videoid')->references('id')->on('videos')->onDelete('cascade');
            $table->date('date');
            $table->unsignedBigInteger('views');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('views_over_time');
    }
};
