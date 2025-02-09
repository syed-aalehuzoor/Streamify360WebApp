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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('logo_url')->default(env('APP_URL') . '/storage/brands/streamify360.png');
            $table->string('website_name')->default('Streamify360');
            $table->string('website_url')->default('https://streamify360.com');
            $table->string('player_domain')->nullable()->default(null);
            $table->boolean('player_domain_varified')->default(true);
            $table->boolean('domain_varification_failed')->default(false);
            $table->string('pop_ads_code')->nullable();
            $table->string('vast_link')->nullable();
            $table->json('allowed_domains')->nullable();

            // Colors
            $table->string('player_background', 50)->default('#000000');
            $table->string('seek_bar_background', 50)->default('#999999');
            $table->string('seek_bar_loaded_progress', 50)->default('#cccccc');
            $table->string('seek_bar_current_progress', 50)->default('#ff3333');
            $table->string('control_buttons', 50)->default('#ffffff');
            $table->string('thumbnail_background', 50)->default('#000000');
            $table->string('volume_seek', 50)->default('#ff3333');
            $table->string('volume_seek_background', 50)->default('#f0f0f0');
            $table->string('menu_background', 50)->default('#404040');
            $table->string('menu_active', 50)->default('#000000');
            $table->string('menu_text', 50)->default('#ffffff');
            $table->string('menu_active_text', 50)->default('#ffffff');

            // Player settings
            $table->float('default_volume')->default(1);
            $table->json('playback_speeds');
            $table->boolean('show_playback_speed')->default(true);
            $table->float('default_playback_speed')->default(1);
            $table->boolean('default_muted')->default(false);
            $table->boolean('loop')->default(true);
            $table->boolean('controls')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
