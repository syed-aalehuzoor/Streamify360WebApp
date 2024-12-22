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
        Schema::create('viewers', function (Blueprint $table) {
            $table->id(); // This will create an auto-incrementing primary key.
            $table->string('ip');
            $table->string('country');
            $table->string('region');
            $table->string('city');
            $table->string('device_type');
            $table->string('operating_system');
            $table->text('error_reports')->nullable(); // Optional error reports.
            $table->integer('frequency_of_visits')->default(0); // Default 0 frequency
            $table->timestamp('first_visit')->useCurrent();
            $table->timestamp('last_visit')->useCurrent();
            $table->timestamps(); // Created_at and updated_at timestamp fields.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viewers');
    }
};
