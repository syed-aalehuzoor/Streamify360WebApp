<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;


class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            $value = $value ?? '';
            Setting::where('key', $key)->update(['value' => $value]);
        }
    
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
    
}

