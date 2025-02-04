<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Storage;

class PlayerSettingsController extends Controller
{
    public function edit_ads()
    {
        // Get the current user's settings
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->first();
    
        if (!$settings) {
            $settings = UserSetting::create(['user_id' => $user->id]);
        }
        
        $popAdsCode = $settings->pop_ads_code;
        $vastLink = $settings->vast_link;

        return view('settings.edit-ads', compact('popAdsCode', 'vastLink'));
    }

    public function update_edit_ads(Request $request)
    {
        $request->validate([
            'popAdsCode' => 'nullable|string',
            'vastLink' => 'nullable|string',
        ]);
        $user = Auth::user();
        $settingsData = [
            'pop_ads_code' => $request->popAdsCode,
            'vast_link' => $request->vastLink,
        ];
        UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            $settingsData
        );

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

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

        $allowed_domains = $allowed_domains = explode("\n", $request->input('allowed_domains', ''));
        $allowed_domains = array_map('trim', $allowed_domains);
        $logoPath = null;

        $settingsData = [
            'allowed_domains' => $allowed_domains,

            'controlbar_background_color' => $request->controlbar_background_color,
            'controlbar_icons_color' => $request->controlbar_icons_color,
            'controlbar_icons_active_color' => $request->controlbar_icons_active_color,
            'controlbar_text_color' => $request->controlbar_text_color,
            'menu_background_color' => $request->menu_background_color,
            'menu_text_color' => $request->menu_text_color,
            'menu_text_active_color' => $request->menu_text_active_color,
            'timeslider_progress_color' => $request->timeslider_progress_color,
            'timeslider_rail_color' => $request->timeslider_rail_color,
            'tooltip_background_color' => $request->tooltip_background_color,
            'tooltip_text_color' => $request->tooltip_text_color,

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
            if ($request->has('websiteURL')){
                $settingsData['website_url'] = $request->websiteURL;            
            }
        }

        UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            $settingsData
        );

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
