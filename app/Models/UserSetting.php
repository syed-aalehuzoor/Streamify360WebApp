<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;
    protected $attributes = [
        'playback_speeds' => '[0.5,1,1.5,2]',
    ];
    
    protected $fillable = [
        'user_id',
        'logo_url',
        'website_url',
        'player_domain',
        'player_domain_varified',
        'pop_ads_code',
        'vast_link',
        'allowed_domains',
        'player_background',
        'seek_bar_background',
        'seek_bar_loaded_progress',
        'seek_bar_current_progress',
        'control_buttons',
        'thumbnail_background',
        'volume_seek',
        'volume_seek_background',
        'menu_background',
        'menu_active',
        'menu_text',
        'menu_active_text',
        'default_volume',
        'playback_speeds',
        'show_playback_speed',
        'default_playback_speed',
        'default_muted',
        'loop',
        'controls',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
