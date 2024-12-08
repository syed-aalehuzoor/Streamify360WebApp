<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\UserSetting;

class SystemSettingsController extends Controller
{
    public function ads_settings()
    {
        // Get the current user's settings
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->first();
        if (!$settings) {
            $settings = UserSetting::create(['user_id' => $user->id]);
        }
        return view('ads-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user->hasPlan('premium') || $user->hasPlan('enterprise')) {
            $request->validate([
                'pop_ads_code' => ['nullable', 'string', function ($attribute, $value, $fail) {
                    // Check for potentially dangerous tags or content in the input.
                    $allowedTags = '<script><meta><link>'; // Example: allow only <script>, <meta>, and <link> tags
            
                    // Strip out all but allowed tags.
                    $sanitizedCode = strip_tags($value, $allowedTags);
            
                    // Compare original input with sanitized version, fail if it's different (which means it had disallowed content)
                    if ($value !== $sanitizedCode) {
                        $fail('The ' . $attribute . ' contains unsafe content.');
                    }
                }],
                'vast_link' => 'nullable|url',
            ]);
            

            UserSetting::updateOrCreate(
                ['user_id' => $user->id],
                ['pop_ads_code' => $request->pop_ads_code],
                ['value' => $request->vast_link]
            );

            return redirect()->back()->with('success', 'Settings updated successfully.');
        }
    }
}
