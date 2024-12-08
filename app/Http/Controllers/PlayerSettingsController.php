<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSetting;

class PlayerSettingsController extends Controller
{
    // Display the settings form
    public function edit()
    {
        // Get the current user's settings
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->first();
        if (!$settings) {
            $settings = UserSetting::create(['user_id' => $user->id]);
        }

        return view('player-settings.edit', compact('user', 'settings'));
    }

    // Update the player settings
    public function update(Request $request)
    {
        // Validate the form input
        $request->validate([
            'logo' => 'file|nullable',
            'website_url' => 'nullable|url',
            'allowed_domains' => 'nullable|string',
            'controlbar_background_color' => 'nullable',
            'controlbar_icons_color' => 'nullable',
            'menu_text_color' => 'nullable',
            'controlbar_icons_active_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'controlbar_text_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'menu_background_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'menu_text_active_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'timeslider_progress_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'timeslider_rail_color' => 'nullable',
            'tooltip_background_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'tooltip_text_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'player_width' => 'nullable|integer',
            'player_height' => 'nullable|integer',
            'volume_level' => 'required|integer|min:0|max:100',
            'autoplay' => 'required|boolean',
            'is_responsive' => 'required|boolean',
            'show_controls' => 'required|boolean',
            'show_playback_speed' => 'required|boolean',
            'playback_speed' => 'required|in:0.5x,1x,1.5x,2x',
            'keyboard_navigation_enabled' => 'required|boolean',
            'social_sharing_enabled' => 'required|boolean',
        ]);

        // Get the current user and settings
        $user = Auth::user();
        $settingsData = [];

        // Check if the user has a "premium" or "enterprise" plan before updating logo, website_url, and allowed_domains
        if ($user->hasPlan('premium') || $user->hasPlan('enterprise')) {
            // Check if a logo file is uploaded and store it
            if ($request->hasFile('logo')) {
                $logo_url = asset('storage/' . $request->file('logo')->storeAs(
                    'brands',
                    Auth::id() . '.' . $request->logo->getClientOriginalExtension(),
                    'public'
                ));
                $settingsData['logo_url'] = $logo_url;
            }

            // Allow updating website URL if the user has the required plan
            if ($request->website_url) {
                $settingsData['website_url'] = $request->website_url;
            }
        }

        $allowed_domains = array_map('trim', explode(',', $request->allowed_domains));
        // Add all other settings to be updated
        $settingsData = array_merge($settingsData, [
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
            'player_width' => $request->player_width,
            'player_height' => $request->player_height,
            'volume_level' => $request->volume_level,
            'autoplay' => $request->autoplay,
            'is_responsive' => $request->is_responsive,
            'show_controls' => $request->show_controls,
            'show_playback_speed' => $request->show_playback_speed,
            'playback_speed' => $request->playback_speed,
            'keyboard_navigation_enabled' => $request->keyboard_navigation_enabled,
            'social_sharing_enabled' => $request->social_sharing_enabled,
        ]);

        
        // Update or create user settings
        UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            $settingsData
        );

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
