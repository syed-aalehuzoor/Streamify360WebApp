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
        Schema::create('audience_insights', function (Blueprint $table) {
            $table->id();
            $table->string('videoid', 11);
            $table->foreign('videoid')->references('id')->on('videos')->onDelete('cascade');

            $table->string('type');
            $table->string('name');
            $table->double('percentage', 5, 2);
            $table->integer('views')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audience_insights');
    }
};
