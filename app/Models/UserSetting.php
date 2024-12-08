<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;
    
    // Define the fillable fields for mass assignment
    protected $fillable = [
        'allowed_domains',
        'user_id', 
        'logo_url', 
        'player_width', 
        'player_height',
        'is_responsive', 
        'show_controls', 
        'show_playback_speed', 
        'primary_color', 
        'control_bar_color',
        'play_button_color', 
        'autoplay', 
        'volume_level', 
        'start_time', 
        'playback_speed', 
        'social_sharing_enabled', 
        'keyboard_navigation_enabled',
        'controlbar_background_color',
        'controlbar_icons_color',
        'controlbar_icons_active_color',
        'controlbar_text_color',
        'menu_background_color',
        'menu_text_color',
        'menu_text_active_color',
        'timeslider_progress_color',
        'timeslider_rail_color',
        'tooltip_background_color',
        'tooltip_text_color',
        'pop_ads_code',          // Added new column
        'vast_link',             // Added new column
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
