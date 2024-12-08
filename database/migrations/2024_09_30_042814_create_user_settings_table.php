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
        // Create 'user_settings' table
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('logo_url')->default(env('APP_URL') . '/storage/brands/streamify360.png');
            $table->string('website_url')->default('https://streamify360.com');
            $table->enum('logo_position', ['top-right', 'top-left', 'bottom-right', 'bottom-left'])->default('top-right');  // Logo position
            $table->string('player_width')->nullable();  // Player width in pixels
            $table->string('player_height')->nullable();  // Player height in pixels
            $table->boolean('is_responsive')->default(true);  // Whether the player is responsive
            $table->boolean('show_controls')->default(true);  // Show/hide control bar
            $table->boolean('show_playback_speed')->default(true);  // Show/hide playback speed control
            $table->json('allowed_domains')->nullable();

            
            $table->string('controlbar_background_color', 50)->default('rgba(0, 0, 0, 0.7)');
            $table->string('controlbar_icons_color', 50)->default('rgba(255, 255, 255, 0.8)');
            $table->string('controlbar_icons_active_color', 50)->default('#FFFFFF');
            $table->string('controlbar_text_color', 50)->default('#FFFFFF');

            // Menu colors
            $table->string('menu_background_color', 50)->default('#333333');
            $table->string('menu_text_color', 50)->default('rgba(255, 255, 255, 0.8)');
            $table->string('menu_text_active_color', 50)->default('#FFFFFF');

            // Time slider colors
            $table->string('timeslider_progress_color', 50)->default('#F2F2F2');
            $table->string('timeslider_rail_color', 50)->default('rgba(255, 255, 255, 0.3)');

            // Tooltip colors
            $table->string('tooltip_background_color', 50)->default('#000000');
            $table->string('tooltip_text_color', 50)->default('#FFFFFF');
            
            $table->boolean('autoplay')->default(false);  // Autoplay setting
            $table->integer('volume_level')->default(80);  // Volume level (0-100)
            $table->integer('start_time')->default(0);  // Start time for playback in seconds
            $table->enum('playback_speed', ['0.5x', '1x', '1.5x', '2x'])->default('1x');  // Playback speed
            $table->boolean('social_sharing_enabled')->default(false);  // Enable/disable social sharing
            $table->boolean('keyboard_navigation_enabled')->default(true);  // Enable/disable keyboard navigation
            $table->timestamps();  // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');  // Drop the table on rollback
    }
};
