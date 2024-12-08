<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip');
            $table->integer('ssh_port');
            $table->string('username');
            $table->string('domain')->nullable();
            $table->string('status');
            $table->enum('type', ['encoder', 'storage']);
            $table->string('public_userid')->nullable();
            $table->enum('encoder_type', ['cpu', 'gpu'])->nullable();
            $table->integer('limit')->default(10);
            $table->integer('total_videos')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
