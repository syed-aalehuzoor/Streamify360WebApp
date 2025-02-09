<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Storage;

class PlayerSettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $settings = UserSetting::firstOrCreate(['user_id' => Auth::id()]);
    
        $colorCustomization = $settings->only([
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
        ]);
    
        $playerOptions = [
            'show_playback_speed' => $settings->autoplay,
            'default_muted' => $settings->show_controls,
            'loop' => $settings->show_playback_speed,
            'controls' => $settings->keyboard_navigation_enabled,
        ];
    
        return view('settings.edit-player', [
            'user' => $user,
            'logoURL' => $settings->logo_url,
            'playerDomain' => $settings->player_domain,
            'domainVarified' => $settings->player_domain_varified,
            'websiteName' => $settings->website_name,
            'websiteURL' => $settings->website_url,
            'allowedDomains' => json_decode($settings->allowed_domains, true),
            'colorCustomization' => $colorCustomization,
            'playerOptions' => $playerOptions,
            'volumeLevel' => $settings->default_volume * 100,
        ]);
    }    

    // Update the player settings
    public function update(Request $request)
    {
        $user = Auth::user();

        $allowed_domains = explode("\n", $request->input('allowed_domains', ''));
        $allowed_domains = array_map('trim', $allowed_domains);
        $logoPath = null;

        $settingsData = [
            'allowed_domains' => $allowed_domains,

            'player_background' => $request->player_background, 
            'seek_bar_background' => $request->seek_bar_background, 
            'seek_bar_loaded_progress' => $request->seek_bar_loaded_progress, 
            'seek_bar_current_progress' => $request->seek_bar_current_progress, 
            'control_buttons' => $request->control_buttons, 
            'thumbnail_background' => $request->thumbnail_background, 
            'volume_seek' => $request->volume_seek, 
            'volume_seek_background' => $request->volume_seek_background, 
            'menu_background' => $request->menu_background, 
            'menu_active' => $request->menu_active, 
            'menu_text' => $request->menu_text, 
            'menu_active_text' => $request->menu_active_text,

            'is_responsive' => $request->boolean('is_responsive'),
            'player_width' => $request->player_width,
            'player_height' => $request->player_height,
            
            'volume_level' => $request->volume_level,
            
            'autoplay' => $request->boolean('autoplay'),
            'show_controls' => $request->boolean('show_controls'),
            'show_playback_speed' => $request->boolean('show_playback_speed'),
            'keyboard_navigation_enabled' => $request->boolean('keyboard_navigation_enabled'),
            'social_sharing_enabled' => $request->boolean('social_sharing_enabled'),
            
            'playback_speed' => $request->playback_speed,
        ];

        if ($user->hasPlan('premium')){
            if ($request->hasFile('logo_file')) {
                $logoPath = $request->file('logo_file')->store('logos', 'public');
                $settingsData['logo_url'] = Storage::url($logoPath);
            }
            $settingsData['website_url'] = $request->websiteURL;
            $settingsData['website_name'] = $request->websiteName;
        }
        UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            $settingsData
        );

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
