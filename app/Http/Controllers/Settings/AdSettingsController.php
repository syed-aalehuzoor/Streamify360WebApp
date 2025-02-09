<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AdSettingsController extends Controller
{
    public function edit()
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

    public function update(Request $request)
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
}
